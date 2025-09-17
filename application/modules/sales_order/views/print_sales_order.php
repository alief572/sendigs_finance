<?php
$ENABLE_ADD     = has_permission('Sales_Order.Add');
$ENABLE_MANAGE  = has_permission('Sales_Order.Manage');
$ENABLE_VIEW    = has_permission('Sales_Order.View');
$ENABLE_DELETE  = has_permission('Sales_Order.Delete');

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
    <table class=" w-100" border="0">
        <tr>
            <td rowspan="2" style="width: 100px;">
                <?= $results['logo'] ?>
            </td>
            <td style="vertical-align: middle;">

            </td>
            <td class="text-center" style="width:200px; vertical-align: middle;">
                <h2 style="text-decoration: underline;">Sales Order</h2>
            </td>
            <!-- <td class="text-right" style="vertical-align: top;">
                Jl. Pembangunan 2 No. 34 <br>
                Kec. Batuceper, Kota Tanggerang, Banten 15121 <br>
                <span style="font-weight:bold;">Hotline Service :</span> (+62) 21 557 66 153 <span style="font-weight:bold;">WhatsApp :</span> (+62) 858 9138 3212
            </td> -->
        </tr>
        <!-- <tr>
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
        </tr> -->
    </table>

    <b>Jl. Pembangunan II Kel. Batusari, <br>
        Kec. Batuceper, Kota Tangerang Postal <br>
        Code 15122 Indonesia <br></b>
    <table style="width: 350px;">
        <tr>
            <th>Phone No</th>
            <th class="text-left">:</th>
            <th>021-55776153</th>
        </tr>
        <tr>
            <th>Email</th>
            <th class="text-left">:</th>
            <th>sales.origa@gmail.com</th>
        </tr>
    </table>

    <table style="width: 100%">
        <tr>
            <td>To</td>
            <td class="text-center">:</td>
            <td><?= 'Loco On Truck ' . ($results['data_penawaran']->quote_by == 'ORIGA') ? 'PT Origa Mulia FRP' : 'PT Orindo Eratec' ?></td>
            <td>SO Date</td>
            <td class="text-center">:</td>
            <td><?= date('d F Y', strtotime($results['data_penawaran']->tgl_so)) ?></td>
        </tr>
        <tr>
            <td>Ordered By</td>
            <td class="text-center">:</td>
            <td><?= $results['data_penawaran']->nm_customer ?></td>
            <td>SO No</td>
            <td class="text-center">:</td>
            <td><?= $results['data_penawaran']->no_so ?></td>
        </tr>
        <tr>
            <td>Address</td>
            <td class="text-center">:</td>
            <td><?= $results['data_penawaran']->delivery_address ?></td>
            <td>PO Date</td>
            <td class="text-center">:</td>
            <td><?= ($results['data_penawaran']->po_date !== '' && $results['data_penawaran']->po_date !== null) ? date('d F Y', strtotime($results['data_penawaran']->po_date)) : null ?></td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td>PO No.</td>
            <td class="text-center">:</td>
            <td><?= $results['data_penawaran']->po_no ?></td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Attn</td>
            <td class="text-center">:</td>
            <td><?= $results['data_penawaran']->nm_pic ?></td>
            <td>Shipment</td>
            <td class="text-center">:</td>
            <td><?= $results['data_penawaran']->pengiriman . ' ' . (($results['data_penawaran']->quote_by == 'ORIGA') ? 'PT Origa Mulia FRP' : 'PT Orindo Eratec')  ?></td>
        </tr>
        <tr>
            <td>Telp</td>
            <td class="text-center">:</td>
            <td><?= $results['data_penawaran']->pic_hp ?></td>
            <td>Estimated Delivery</td>
            <td class="text-center">:</td>
            <td><?= date('d F Y', strtotime($results['data_penawaran']->delivery_date))  ?></td>
        </tr>
        <tr>
            <td>Fax</td>
            <td class="text-center">:</td>
            <td><?= $results['data_penawaran']->fax ?></td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td>Payment</td>
            <td class="text-center">:</td>
            <td><?= $results['data_penawaran']->nama_top ?></td>
            <td colspan="3"></td>
        </tr>
    </table>

    

    
    <table style="width: 100%" border="1">
        <thead>
            <tr>
                <th class="text-center" style="padding:10px; border: 1px solid black;">No.</th>
                <th class="text-center" style="padding:10px; border: 1px solid black;">Product Code</th>
                <th class="text-center" style="padding:10px; border: 1px solid black;">Product Master</th>
                <th class="text-center" style="padding:10px; border: 1px solid black;">Variant</th>
                <th class="text-center" style="padding:10px; border: 1px solid black;">Color</th>
                <th class="text-center" style="padding:10px; border: 1px solid black;">Surface</th>
                <th class="text-center" style="padding:10px; border: 1px solid black;">Quantity</th>
                <th class="text-center" style="padding:10px; border: 1px solid black;">UOM</th>
                <th class="text-center" style="padding:10px; border: 1px solid black;">Material</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            foreach ($results['data_penawaran_detail'] as $item) :
                echo '<tr>';
                echo '<td class="text-center" style="padding: 10px;border: 1px solid black;">'.$no.'</td>';
                echo '<td class="text-center" style="padding: 10px;border: 1px solid black;">'.$item->code.'</td>';
                echo '<td class="text-center" style="padding: 10px;border: 1px solid black;">'.$item->nama.'</td>';
                echo '<td class="text-center" style="padding: 10px;border: 1px solid black;">'.$item->variant.'</td>';
                echo '<td class="text-center" style="padding: 10px;border: 1px solid black;">'.$item->color.'</td>';
                echo '<td class="text-center" style="padding: 10px;border: 1px solid black;">'.$item->surface.'</td>';
                echo '<td class="text-center" style="padding: 10px;border: 1px solid black;">'.number_format($item->qty_so).'</td>';
                echo '<td class="text-center" style="padding: 10px;border: 1px solid black;">'.ucfirst($item->unit_packing).'</td>';
                echo '<td class="text-center" style="padding: 10px;border: 1px solid black;">FRP</td>';
                echo '</tr>';

                $no++;
            endforeach;
            ?>
        </tbody>
    </table>

    <br>

    <table style="width:100%; min-height: 100px;"  border="1">
        <tr>
            <td style="vertical-align: top;">
                <span>Note :</span> <br>
                <span><?= $results['data_penawaran']->notes_so ?></span>
            </td>
        </tr>
    </table>
    <br>
    <table style="width: 50%;">
        <tr>
            <td style="height: 100px;text-align: center; vertical-align:top;">Dibuat,</td>
            
            <?php 
                if($results['data_penawaran']->req_app1 == '1'){
                    echo '
                        <td style="height: 100px;text-align: center; vertical-align: top;">Disetujui,</td>
                    ';
                }
                if($results['data_penawaran']->req_app2 == '1'){
                    echo '
                        <td style="height: 100px;text-align: center; vertical-align: top;">Disetujui,</td>
                    ';
                }
                if($results['data_penawaran']->req_app3 == '1'){
                    echo '
                        <td style="height: 100px;text-align: center; vertical-align: top;">Disetujui,</td>
                    ';
                }
            ?>
        </tr>
        <tr>
            <td style="text-align: center;">
                <span style="font-weight: bold;">
                    <?= $results['data_penawaran']->nama_sales; ?>
                </span>
            </td>
            <?php 
                if($results['data_penawaran']->req_app1 == '1'){
                    echo '
                        <td style="text-align: center;">
                            <span style="font-weight: bold;">
                                
                            </span>
                        </td>
                    ';
                }
                if($results['data_penawaran']->req_app2 == '1'){
                    echo '
                        <td style="text-align: center;">
                            <span style="font-weight: bold;">

                            </span>
                        </td>
                    ';
                }
                if($results['data_penawaran']->req_app3 == '1'){
                    echo '
                        <td style="text-align: center;">
                            <span style="font-weight: bold;">
                                
                            </span>
                        </td>
                    ';
                }
            ?>
        </tr>
        <tr>
            <td style="text-align: center;">
                <span style="font-weight: bold;">
                    <br>
                    Sales
                </span>
            </td>
            <?php 
                if($results['data_penawaran']->req_app1 == '1'){
                    echo '
                        <td style="text-align: center;">
                            <span style="font-weight: bold;">
                                <br>
                                Supervisor
                            </span>
                        </td>
                    ';
                }
                if($results['data_penawaran']->req_app2 == '1'){
                    echo '
                        <td style="text-align: center;">
                            <span style="font-weight: bold;">
                                <br>
                                Manager
                            </span>
                        </td>
                    ';
                }
                if($results['data_penawaran']->req_app3 == '1'){
                    echo '
                        <td style="text-align: center;">
                            <span style="font-weight: bold;">
                                <br>
                                Cost Control
                            </span>
                        </td>
                    ';
                }
            ?>
        </tr>
    </table>
</div>

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