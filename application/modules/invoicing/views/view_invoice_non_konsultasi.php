<?php
$tanggal_invoice = $data_invoicing->tanggal_invoice ?? '';
$nomor_invoice = $data_invoicing->no_invoice ?? '';
$no_po = $data_invoicing->no_po ?? '';
$no_faktur = $data_invoicing->no_faktur ?? '';
?>
<div class="box">
    <!-- <form action="" method="post" id="frm-data"> -->
    <input type="hidden" name="id_penawaran" value="<?= $data_invoicing->id_penawaran ?>">
    <input type="hidden" name="id_invoicing" value="<?= $data_invoicing->id ?>">
    <div class="box-body">
        <div class="col-6">
            <table width="100%" border="0">
                <tr>
                    <th width="13%">Kepada</th>
                    <td width="12%"><?= $data_invoicing->nm_customer ?></td>
                    <th width="13%">Tanggal Invoice</th>
                    <td width="12%">
                        <input type="date" name="tanggal_invoice" id="" class="form-control form-control-sm" value="<?= $tanggal_invoice ?>" placeholder="Tanggal Invoice">
                    </td>
                </tr>
                <tr>
                    <th width="13%">Alamat</th>
                    <td width="12%"><?= $data_invoicing->address ?></td>
                    <th width="13%">Nomor Invoice</th>
                    <td width="12%">
                        <input type="text" name="nomor_invoice" id="" class="form-control form-control-sm" placeholder="Nomor Invoice" value="<?= $nomor_invoice ?>">
                    </td>
                </tr>
                <tr>
                    <th width="13%">Ditujukan</th>
                    <td width="12%">Finance Dept.</td>
                    <th width="13%">Nomor PO</th>
                    <td width="12%">
                        <input type="text" name="nomor_po" id="" class="form-control form-control-sm" value="<?= $no_po ?>">
                    </td>
                </tr>
                <tr>
                    <th width="13%"></th>
                    <td width="12%"></td>
                    <th width="13%">Nomor Faktur</th>
                    <td width="12%">
                        <br>
                        <input type="text" class="form-control form-control-sm" name="nomor_faktur" placeholder="Nomor Faktur" value="<?= $no_faktur ?>">
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="box-body">
        <table class="table table-striped table-bordered">
            <thead class="bg-primary">
                <tr>
                    <th class="text-center" width="5%">No.</th>
                    <th class="text-center" width="15%">Item</th>
                    <th class="text-center" width="15%">Qty</th>
                    <th class="text-center" width="15%">Harga</th>
                    <th class="text-center" width="15%">Total</th>
                    <!-- <th class="text-center" width="5%">Action</th> -->
                </tr>
            </thead>
            <tbody class="list_item">
                <?php

                $grand_total = 0;
                $no = 0;
                foreach ($data_item_invoice as $item) {
                    $no++;

                    echo '
                            <tr>
                                <td class="text-center">' . $no . '</td>
                                <td class="">' . $item->nm_item . '</td>
                                <td class="text-center">' . round($item->qty) . '</td>
                                <td class="text-right">' . number_format($item->harga) . '</td>
                                <td class="text-right">' . number_format($item->total) . '</td>
                            </tr>
                        ';

                    $grand_total += $item->total;
                }
                ?>
            </tbody>
            <tfoot class="footer_item bg-gray">
                <tr>
                    <th colspan="4" class="text-center">Biaya Kirim</th>
                    <th>
                        <input type="text" name="biaya_kirim" id="" class="form-control form-control-sm text-right auto_num" value="<?= $data_invoicing->biaya_kirim ?>" onchange="hitung_all();">
                    </th>
                </tr>
                <tr>
                    <th colspan="4" class="text-center">Total</th>
                    <th>
                        <input type="text" name="total" id="total" class="form-control form-control-sm text-right auto_num" value="<?= ($grand_total + $data_invoicing->biaya_kirim) ?>" readonly>
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="box-body">
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <table class="table table-bordered table-striped">
                    <thead class="bg-primary">
                        <tr>
                            <th class="text-center">Keterangan</th>
                            <th class="text-center">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>DPP</td>
                            <td>
                                <input type="text" name="dpp" id="dpp" class="form-control form-control-sm text-right auto_num" value="<?= $data_invoicing->total_nominal ?>" readonly>
                            </td>
                        </tr>
                        <tr>
                            <td>DPP Lain-lain</td>
                            <td>
                                <input type="text" name="dpp_lain_lain" id="dpp_lain_lain" class="form-control form-control-sm text-right auto_num" value="<?= $data_invoicing->dpp_lain_lain_jurnal ?>" readonly>
                            </td>
                        </tr>
                        <tr>
                            <td>PPN <?= isset($data_invoicing->ppn_persen) ? $data_invoicing->ppn_persen : 12 ?>% dari DPP lain</td>
                            <td>
                                <input type="text" name="ppn" id="ppn" class="form-control form-control-sm text-right auto_num" value="<?= $data_invoicing->pajak ?>" readonly>
                            </td>
                        </tr>
                        <tr>
                            <td>Total Tagihan + PPN <?= isset($data_invoicing->ppn_persen) ? $data_invoicing->ppn_persen : 12 ?>%</td>
                            <td>
                                <input type="text" name="total_tagihan_ppn" id="total_tagihan_ppn" class="form-control form-control-sm text-right auto_num" value="<?= $data_invoicing->tagihan_ppn_jurnal ?>" readonly>
                            </td>
                        </tr>
                        <tr>
                            <td>Pph 23</td>
                            <td>
                                <input type="text" name="pph" id="pph" class="form-control form-control-sm text-right auto_num" value="<?= $data_invoicing->pph_jurnal ?>" readonly>
                            </td>
                        </tr>
                        <tr>
                            <td>Total Tagihan + PPn - Pph</td>
                            <td>
                                <input type="text" name="total_tagihan_all" id="total_tagihan_all" class="form-control form-control-sm text-right auto_num" value="<?= $data_invoicing->total_akhir_jurnal ?>" readonly>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="box-body">
        <h4>Jurnal Invoice</h4>
        <table class="table table-bordered table-striped">
            <thead class="bg-primary">
                <tr>
                    <th class="text-center">Tanggal Jurnal</th>
                    <th class="text-center">COA</th>
                    <th class="text-center">Nama Company</th>
                    <th class="text-center">Nama Account</th>
                    <th class="text-center">Debit</th>
                    <th class="text-center">Credit</th>
                </tr>
            </thead>
            <tbody>
                <?= $list_jurnal_invoice ?>
            </tbody>
            <tfoot class="bg-gray">
                <tr>
                    <th colspan="4" class="text-center">Balancing</th>
                    <th class="text-right th_total_debit">
                        <?= number_format($total_debit) ?>
                    </th>
                    <th class="text-right th_total_kredit">
                        <?= number_format($total_kredit) ?>
                    </th>
                </tr>
            </tfoot>
        </table>

        <br><br>


        <a href="<?= base_url('invoicing') ?>" class="btn btn-danger"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
    <!-- </form> -->
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
    $('input').attr('disabled', true);

    $(document).ready(function() {
        $('.auto_num').autoNumeric('init');
    })
</script>