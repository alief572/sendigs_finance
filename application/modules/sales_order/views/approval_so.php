<?php
// print_r($header);
?>
<input type="hidden" name="no_so" class="no_so" value="<?= $results['sales_order']->no_so ?>">
<div class="box box-primary">
    <div class="box-body">
        <table class="table w-100">
            <tr>
                <th>Customer Name</th>
                <th>:</th>
                <td><?= $results['sales_order']->nm_customer ?></td>
                <th>Quote Number</th>
                <th>:</th>
                <td><?= $results['sales_order']->no_penawaran ?></td>
            </tr>
            <tr>
                <th>Customer Address</th>
                <th>:</th>
                <td><?= $results['sales_order']->alamat ?></td>
                <th>Quote Date</th>
                <th>:</th>
                <td><?= date('d F Y', strtotime($results['sales_order']->tgl_so)) ?></td>
            </tr>
            <tr>
                <th>Contact Person</th>
                <th>:</th>
                <td><?= $results['sales_order']->nm_pic ?></td>
                <th>Invoice Address</th>
                <th>:</th>
                <td><?= $results['sales_order']->invoice_address ?></td>
            </tr>
            <tr>
                <th>TOP</th>
                <th>:</th>
                <td><?= $results['top_name'] . ' ' . $results['sales_order']->top_custom ?></td>
                <th>Sales</th>
                <th>:</th>
                <td>
                    <?= $results['sales_order']->nm_lengkap ?>
                </td>
            </tr>
            <tr>
                <th>Delivery Address</th>
                <th>:</th>
                <td><?= $results['sales_order']->delivery_address ?></td>
                <th>Delivery Date</th>
                <th>:</th>
                <td><?= date('d F Y', strtotime($results['sales_order']->delivery_date)) ?></td>
            </tr>
            <tr>
                <th>Upload Dokumen</th>
                <th>:</th>
                <td>
                <?php
					$exp_uppo = explode('|', $results['sales_order']->upload_po);
					foreach ($exp_uppo as $uppo) {
						if (base_url($uppo) && $uppo !== '') {
							echo '<a href="' . base_url($uppo) . '" target="_blank">' . str_replace('uploads/po/', '', $uppo) . '</a> <br>';
						}
					}
					?>
                </td>
                <th colspan="3"></th>
            </tr>
            <tr>
                <th>Approve / Reject</th>
                <th>:</th>
                <td>
                    <select name="action_type" id="" class="form-control form-control-sm action_type" required>
                        <option value="">- Approve / Reject -</option>
                        <option value="1">Approve</option>
                        <option value="0">Reject</option>
                    </select>
                </td>
                <th>Keterangan Approve / Reject</th>
                <th>:</th>
                <td>
                    <input type="text" name="keterangan_approve_reject" id="" class="form-control form-control-sm keterangan_approve_reject" placeholder="Keterangan Approve / Reject" value="<?= ($results['sales_order']->keterangan_approve !== '') ? $results['sales_order']->keterangan_approve : $results['sales_order']->keterangan_loss ?>" required>
                </td>
            </tr>
        </table>
        <div class="form-group row">
            <div class="tableFixHead">
                <table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
                    <thead>
                        <tr class='bg-blue'>
                            <th class='text-center'>No.</th>
                            <th class='text-center'>Code</th>
                            <th class='text-center' style="width: 350px;">Product Name</th>
                            <th class='text-center'>Color</th>
                            <th class='text-center'>Surface</th>
                            <th class='text-center'>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $x = 1;
                        foreach ($results['data_sales_order_detail'] as $sales_order_detail) :
                            $request_production = 0;
                            if ($sales_order_detail->stok_tersedia < $sales_order_detail->qty) {
                                $request_production = ($sales_order_detail->qty - $sales_order_detail->stok_tersedia);
                            }
                            echo '
									<tr>
										<td class="text-center" style="vertical-align: middle;">' . $x . '</td>
										<td class="text-center" style="vertical-align: middle;">' . $sales_order_detail->product_code . '</td>
										<td class="text-center" style="vertical-align: middle;">' . $sales_order_detail->nama_produk . '</td>
										<td class="text-center" style="vertical-align: middle;">' . $sales_order_detail->color . '</td>
										<td class="text-center" style="vertical-align: middle;">' . $sales_order_detail->surface . '</td>
										<td class="text-center" style="vertical-align: middle;">' . $sales_order_detail->qty . '</td>
									</tr>
								';

                            $x++;
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
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
        background: #0073b7;
    }
</style>