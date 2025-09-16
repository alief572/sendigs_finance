<?php
$ENABLE_ADD     = has_permission('Quotation.Add');
$ENABLE_MANAGE  = has_permission('Quotation.Manage');
$ENABLE_VIEW    = has_permission('Quotation.View');
$ENABLE_DELETE  = has_permission('Quotation.Delete');
// print_r($results);
// exit;
?>
<style type="text/css">
    thead input {
        width: 100%;
    }

    .breakarea {
        page-break-before: always;
    }



    .custom-list .marker {
        display: inline-block;
        width: 20px;
        /* Adjust as necessary */
        text-align: center;
    }

    @media print {
        .unprint {
            display: none;
        }
    }
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

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
                <?= strtoupper($results['pt_name']) ?>
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


    <table style="width: 100%;">
        <tr>
            <th>
                Jakarta, <?= date('d M Y') ?>
            </th>
            <th style="text-align: right;">
                Rev <?= ($results['data_penawaran']->no_revisi == '') ? 0 : $results['data_penawaran']->no_revisi ?>
            </th>
        </tr>
    </table>
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
            <td style="vertical-align: top;"><?= $results['data_penawaran']->subject ?></td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Notes</td>
            <td style="text-align:center; width:50px !important; vertical-align: top;">:</td>
            <td style="vertical-align: top;"><?= $results['data_penawaran']->notes ?></td>
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
                    <th class="text-center">VARIANT</th>
                    <th class="text-center">COLOR</th>
                    <th class="text-center">SURFACE</th>
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
                    if (isset($results['show_disc']) && $results['show_disc'] == '1') {
                        echo '
                            <tr>
                                <td class="text-center">' . $no . '</td>
                                <td class="">' . $penawaran_detail->nama_produk . '';

                        if ($penawaran_detail->ukuran_potongan !== '' && $penawaran_detail->ukuran_potongan !== null) {
                            echo '<br><br> <b>Cut Size : ' . $penawaran_detail->ukuran_potongan . '</b>';
                        }

                        $br_harga = (strlen(($penawaran_detail->harga_satuan - ($penawaran_detail->harga_satuan * $penawaran_detail->diskon_persen / 100))) >= 8) ? '<br>' : '';

                        $br_total = (strlen((($penawaran_detail->harga_satuan - ($penawaran_detail->harga_satuan * $penawaran_detail->diskon_persen / 100)) * $penawaran_detail->qty)) >= 8) ? '<br>' : '';

                        echo '</td>
                                <td class="text-center">' . $penawaran_detail->code . '</td>
                                <td class="text-center">' . $penawaran_detail->variant_product . '</td>
                                <td class="text-center">' . $penawaran_detail->color . '</td>
                                <td class="text-center">' . $penawaran_detail->surface . '</td>
                                <td class="text-center">' . number_format($penawaran_detail->qty) . '</td>
                                <td class="text-center">' . ucfirst($penawaran_detail->unit_packing) . '</td>
                                <td class="text-right">' . $results['data_penawaran']->currency . ' ' . $br_harga . number_format((($penawaran_detail->harga_satuan + $penawaran_detail->cutting_fee + $penawaran_detail->delivery_fee))) . '</td>
                                <td class="text-right">' . $results['data_penawaran']->currency . ' ' . $br_total . number_format(((($penawaran_detail->harga_satuan + $penawaran_detail->cutting_fee + $penawaran_detail->delivery_fee) * $penawaran_detail->qty))) . '</td>
                            </tr>
                        ';

                        $harga_seb_diskon += (($penawaran_detail->harga_satuan + $penawaran_detail->cutting_fee + $penawaran_detail->delivery_fee) * $penawaran_detail->qty);
                        $harga_ses_diskon += ((($penawaran_detail->harga_satuan + $penawaran_detail->cutting_fee + $penawaran_detail->delivery_fee) - ($penawaran_detail->harga_satuan * $penawaran_detail->diskon_persen / 100)) * $penawaran_detail->qty);
                        $ttl_diskon += (($penawaran_detail->harga_satuan * $penawaran_detail->diskon_persen / 100) * $penawaran_detail->qty);
                    } else {
                        echo '
                            <tr>
                                <td class="text-center">' . $no . '</td>
                                <td class="">' . $penawaran_detail->nama_produk . '';

                        if ($penawaran_detail->ukuran_potongan !== '' && $penawaran_detail->ukuran_potongan !== null) {
                            echo '<br><br> <b>Cut Size : ' . $penawaran_detail->ukuran_potongan . '</b>';
                        }

                        $br_harga = (strlen($penawaran_detail->harga_satuan) >= 8) ? '<br>' : '';

                        $br_total = (strlen((($penawaran_detail->harga_satuan) * $penawaran_detail->qty)) >= 8) ? '<br>' : '';

                        echo '</td>
                                <td class="text-center">' . $penawaran_detail->code . '</td>
                                <td class="text-center">' . $penawaran_detail->variant_product . '</td>
                                <td class="text-center">' . $penawaran_detail->color . '</td>
                                <td class="text-center">' . $penawaran_detail->surface . '</td>
                                <td class="text-center">' . number_format($penawaran_detail->qty) . '</td>
                                <td class="text-center">' . ucfirst($penawaran_detail->unit_packing) . '</td>
                                <td class="text-right">' . $results['data_penawaran']->currency . ' ' . $br_harga . number_format(($penawaran_detail->harga_satuan)) . '</td>
                                <td class="text-right">' . $results['data_penawaran']->currency . ' ' . $br_total . number_format((($penawaran_detail->harga_satuan) * $penawaran_detail->qty)) . '</td>
                            </tr>
                        ';

                        $harga_seb_diskon += (($penawaran_detail->harga_satuan + $penawaran_detail->cutting_fee + $penawaran_detail->delivery_fee) * $penawaran_detail->qty);
                        $harga_ses_diskon += (($penawaran_detail->harga_satuan + $penawaran_detail->cutting_fee + $penawaran_detail->delivery_fee) * $penawaran_detail->qty);
                    }
                    $no++;
                }

                foreach ($results['list_other_cost'] as $other_cost) {
                    $br = (strlen($other_cost->nilai) >= 8) ? '<br>' : '';

                    echo '
                        <tr>
                            <td class="text-center">' . $no . '</td>
                            <td class="">' . $other_cost->keterangan . '</td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-right">' . $other_cost->curr . ' ' . $br . number_format($other_cost->total_nilai) . '</td>
                            <td class="text-right">' . $other_cost->curr . ' ' . $br . number_format($other_cost->total_nilai) . '</td>
                        </tr>
                    ';

                    $harga_seb_diskon += $other_cost->total_nilai;
                    $harga_ses_diskon += $other_cost->total_nilai;
                }

                foreach($results['list_other_item'] as $other_item) {

                    $get_other_detail = $this->db->query("
                        SELECT
                            a.code as product_code,
                            b.code as nm_unit
                        FROM
                            new_inventory_4 a
                            LEFT JOIN ms_satuan b ON b.id = a.id_unit
                        WHERE
                            a.code_lv4 = '".$other_item->id_other."'

                        UNION ALL

                        SELECT
                            a.id_stock as product_code,
                            b.code as nm_unit
                        FROM
                            accessories a
                            LEFT JOIN ms_satuan b ON b.id = a.id_unit
                        WHERE
                            a.id = '".$other_item->id_other."'
                    ")->row();

                    echo '<tr>';
                    echo '<td class="text-center">'.$no.'</td>';
                    echo '<td class="text-left">'.$other_item->nm_other.'</td>';
                    echo '<td class="text-center">'.$get_other_detail->product_code.'</td>';
                    echo '<td class="text-center">-</td>';
                    echo '<td class="text-center">-</td>';
                    echo '<td class="text-center">-</td>';
                    echo '<td class="text-center">'.number_format($other_item->qty).'</td>';
                    echo '<td class="text-center">'.ucfirst($get_other_detail->nm_unit).'</td>';
                    echo '<td class="text-left">'.$results['data_penawaran']->currency.' '.number_format($other_item->harga).'</td>';
                    echo '<td class="text-left">'.$results['data_penawaran']->currency.' '.number_format($other_item->total).'</td>';
                    echo '</tr>';

                    $harga_seb_diskon += $other_item->total;
                    $harga_ses_diskon += $other_item->total;
                    $no++;
                }

                if ($harga_seb_diskon > 0 && $harga_ses_diskon > 0) {
                    $ttl_persen_diskon = (($harga_seb_diskon - $harga_ses_diskon) / $harga_seb_diskon);
                }
                ?>
            </tbody>
            <tbody>
                <tr>
                    <th class="text-right" colspan="9">TOTAL NETT</th>
                    <th class="text-right"><?= $results['data_penawaran']->currency ?> <?= number_format($harga_seb_diskon) ?></th>
                </tr>
                <?php 
                    if($results['show_disc'] !== '' && $results['show_disc'] !== null) {
                        echo '
                            <tr>
                                <th class="text-right" colspan="9">DISCOUNT</th>
                                <th class="text-right">'.$results['data_penawaran']->currency.' '. number_format($ttl_diskon) .'</th>
                            </tr>
                        ';
                    }
                ?>
                <tr>
                    <th class="text-right" colspan="9">VAT <?= number_format($results['data_penawaran']->ppn) ?>%</th>
                    <th class="text-right"><?= $results['data_penawaran']->currency ?> <?= number_format(($harga_ses_diskon * $results['data_penawaran']->ppn / 100)) ?></th>
                </tr>
                <tr>
                    <th class="text-right" colspan="9">GRAND TOTAL NETT</th>
                    <th class="text-right"><?= $results['data_penawaran']->currency ?> <?= number_format($harga_ses_diskon + ($harga_ses_diskon * $results['data_penawaran']->ppn / 100)) ?></th>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="breakarea"></div>

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
                <?= strtoupper($results['pt_name']) ?>
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

    <!-- <div class="col-md-2"> -->
    <table style="width: 60%;">
        <tr>
            <td>*</td>
            <td>Time Of Delivery</td>
            <td class="text-center" style="min-width:50px;">:</td>
            <td><?= $results['data_penawaran']->time_delivery ?></td>
        </tr>
        <tr>
            <td>*</td>
            <td>Offer Period</td>
            <td class="text-center" style="min-width:50px;">:</td>
            <td><?= $results['data_penawaran']->offer_period ?></td>
        </tr>
        <tr>
            <td>*</td>
            <td>Delivery Term</td>
            <td class="text-center" style="min-width:50px;">:</td>
            <td><?= $results['data_penawaran']->delivery_term ?></td>
        </tr>
        <tr>
            <td>*</td>
            <td>Stage Payment of Fees</td>
            <td class="text-center" style="min-width:50px;">:</td>
            <td><?= $results['data_penawaran']->nama_top . ' ' . $results['data_penawaran']->top_custom ?></td>
        </tr>
        <tr>
            <td>*</td>
            <td>Warranty</td>
            <td class="text-center" style="min-width:50px;">:</td>
            <td><?= $results['data_penawaran']->warranty ?></td>
        </tr>
    </table>
    <!-- </div> -->

    <?php
    if ($results['data_penawaran']->quote_by == 'ORINDO') {
        echo '
                <div style="padding-top: 10px;">
                    <b style="text-transform: underline;">Terms & Conditions :</b>
                    <ol class="custom-list" >
                        <li>Above costs are including the Loading Cost at PT. Orindo Eratec Warehouse.</li>
                        <li>Every event of late payment will incur 0.1% / day and maximum penalty of 10% of total Purchase Order.</li>
                        <li>Any delay in delivery and /or determination of production upon request from customer side, will incur additional cost as follow: temporary storage charges will be billed. If the product is in our factory and /or stop of delivery within three weeks and more at a price Rp. 15.000/ m2 / day</li>
                        <li>Any disputes arising from the implementation of the activities referred to this Proposal Offer, all parties involved will resolve by deliberation to reach consensus. If the process fo deliberation and consensus does not occur, then all parties involved agree to resolve it and choosing legal common law in Central Jakarta District Court. The laws governing this agreement is the law of the Republic Indonesia</li>
                        <li>PT. Orindo Eratec will be not responsible for problems resulting from external causes such as accident, abuse, misuse, mishandling, negligence, fire / water damage, theft, vandalism, riot, explosion, natural disaster, or other external causes unrelated to product performance.</li>
                    </ol>
                </div>

                <p>As above are the basic condition from us, if there are unclear matter, please do not hesitate to contact our representative offices or agents. Thank you for your attention and trust.</p>
            ';
    } else {
        echo '
            <div style="padding-top: 10px;">
                <b style="text-transform: underline;">Terms & Conditions :</b>
                <ol class="custom-list" >
                    <li>Above costs are including the Loading Cost at PT. Origa Mulia FRP.</li>
                    <li>Every event of late payment will incur 0.1% / day and maximum penalty of 10% of total Purchase Order.</li>
                    <li>Any delay in delivery and /or determination of production upon request from customer side, will incur additional cost as follow: temporary storage charges will be billed. If the product is in our factory and /or stop of delivery within three weeks and more at a price Rp. 15.000/ m2 / day</li>
                    <li>Any disputes arising from the implementation of the activities referred to this Proposal Offer, all parties involved will resolve by deliberation to reach consensus. If the process fo deliberation and consensus does not occur, then all parties involved agree to resolve it and choosing legal common law in Central Jakarta District Court. The laws governing this agreement is the law of the Republic Indonesia</li>
                    <li>PT.  Origa Mulia FRP will be not responsible for problems resulting from external causes such as accident, abuse, misuse, mishandling, negligence, fire / water damage, theft, vandalism, riot, explosion, natural disaster, or other external causes unrelated to product performance.</li>
                </ol>
            </div>

            <p>As above are the basic condition from us, if there are unclear matter, please do not hesitate to contact our representative offices or agents. Thank you for your attention and trust.</p>
            ';
    }
    ?>

    <div style="padding-top: 60px;">
        <b style="text-decoration: underline;">Yours Faithfully</b> <br><br><br><br>
        <b style="text-decoration: underline;"><?= $results['data_penawaran']->nama_sales ?></b>
    </div>
    <!-- </div> -->
    <!-- /.box-body -->
</div>

<!-- <div class="text-right unprint">
    <button type="button" class="btn btn-sm btn-info" onclick="window.print()">Print</button>
</div> -->

<!-- awal untuk modal dialog -->
<!-- Modal -->

<!-- /.modal -->

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<!-- page script -->
<script>
    // function printDiv(divId) {
    //     var printContents = document.getElementById(divId).innerHTML;
    //     var originalContents = document.body.innerHTML;

    //     document.body.innerHTML = printContents;

    //     window.print();

    //     document.body.innerHTML = originalContents;
    // }

    window.print();
</script>