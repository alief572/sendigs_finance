<div class="box">
    <div class="box-body">
        <table class="table table-bordered table-striped" width="100%">
            <thead class="bg-primary">
                <tr>
                    <th class="text-center">No.</th>
                    <th class="text-center">Company</th>
                    <th class="text-center">No. Penawaran</th>
                    <th class="text-center">Penjualan</th>
                    <th class="text-center">PIC</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 0;
                foreach ($list_penawaran_non_kons as $item) {
                    $no++;

                    $sts = '<div class="badge bg-yellow">Draft</div>';

                    $btn_create = '<a href="' . base_url('invoicing/create_invoice_non_konsultasi/' . $item->id_penawaran) . '" class="btn btn-sm btn-success" title="Select Quotation"><i class="fa fa-plus"></i></a>';

                    echo '
                        <tr>
                            <td class="text-center">' . $no . '</td>
                            <td class="text-left">' . $item->nm_company . '</td>
                            <td class="text-center">' . $item->id_penawaran . '</td>
                            <td class="text-right">' . number_format($item->grand_total) . '</td>
                            <td class="text-center">' . $item->pic . '</td>
                            <td class="text-center">' . $sts . '</td>
                            <td class="text-center">' . $btn_create . '</td>
                        </tr>
                    ';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>