<div class="box box-primary">
    <div class="box-header">
        <h3 class="box-title"><b>LOT DETAIL</b></h3><br>
        <h3 class="box-title" style="color:#c85b0e;"><b><?= strtoupper(get_name('new_inventory_4', 'nama', 'code_lv4', $material)); ?></b></h3>
        <br>
        <!-- <a href="stok_gudang_pusat/export_excel/<?= $material ?>/<?= $gudang ?>" class="btn btn-sm btn-success">
            <i class="fa fa-download"></i> Download Excel
        </a> -->
    </div>
    <div class="box-body tableFixHead" style="height:500px;">
        <table class="table table-striped table-bordered table-hover table-condensed" width="100%">
            <thead>
                <tr>
                    <th class="text-center" width='4%'>#</th>
                    <th class="text-center">Hist Date</th>
                    <th class="text-left">Hist By</th>
                    <th class="text-right" width='7%'>Qty NG</th>
                    <th class="text-right" width='7%'>Qty OK</th>
                    <th class="text-right" width='7%'>Konversi</th>
                    <th class="text-right" width='7%'>Qty OK (Pack)</th>
                    <th class="text-left">Keterangan Lot</th>
                    <th class="text-center">Expired Date</th>
                    <th class="text-left">No Trans</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                if (!empty($data)) {
                    foreach ($data as $val => $valx) {
                        echo '<tr>';
                        echo '<td class="text-center">'.$no.'</td>';
                        echo '<td class="text-center">'.$valx['created_date'].'</td>';
                        echo '<td class="text-center">'.$valx['nm_lengkap'].'</td>';
                        echo '<td class="text-right">'.number_format($valx['qty_ng'], 2).'</td>';
                        echo '<td class="text-right">'.number_format(($valx['qty_oke'] - $valx['qty_used']), 2).'</td>';
                        echo '<td class="text-right">'.number_format($valx['nil_kon'], 2).'</td>';
                        echo '<td class="text-right">'.number_format(($valx['qty_oke'] - $valx['qty_used']) / (($valx['nil_kon'] > 0) ? $valx['nil_kon'] : 1), 2).'</td>';
                        echo '<td class="">'.$valx['lot_description'].'</td>';
                        echo '<td class="text-center">'.date('d F Y', strtotime($valx['expired_date'])).'</td>';
                        echo '<td class="">'.$valx['kode_trans'].'</td>';
                        echo'</tr>';
                        
                        $no++;
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<style media="screen">
    /* JUST COMMON TABLE STYLES... */
    .table {
        border-collapse: collapse;
        width: 100%;
    }

    .td {
        background: #fff;
        padding: 8px 16px;
    }

    .tableFixHead {
        overflow: auto;
        height: 300px;
        position: sticky;
        top: 0;
    }

    .thead .th {
        position: sticky;
        top: 0;
        z-index: 9999;
        background: #a0a0a0;
    }
</style>