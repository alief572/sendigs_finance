<?php
$total_nominal = $data_invoice->total_nominal;
$dpp_nilai_lain = $data_invoice->dpp_nilai_lain;
$pajak = $data_invoice->pajak;
$total_akhir = $data_invoice->total_akhir;

$total_nominal_jurnal = $data_invoice->total_nominal_jurnal;
$dpp = $data_invoice->total_nominal_jurnal;
$dpp_lain_lain = $data_invoice->dpp_lain_lain_jurnal;
$ppn = $data_invoice->ppn_jurnal;
$pph = $data_invoice->pph_jurnal;
$tagihan_ppn = $data_invoice->tagihan_ppn_jurnal;
$total_akhir_jurnal = $data_invoice->total_akhir_jurnal;
?>
<div class="box">
    <form action="" method="post" id="frm-data">
        <input type="hidden" name="id_invoicing" value="<?= $id_invoicing ?>">
        <div class=" box-body">
            <div class="col-6">
                <table width="100%" border="0">
                    <tr>
                        <th width="13%">Kepada</th>
                        <td width="12%"><?= $data_invoice->nm_customer ?></td>
                        <th width="13%">Tanggal Invoice</th>
                        <td width="12%">
                            <input type="date" name="tanggal_invoice" id="" class="form-control form-control-sm" value="<?= $data_invoice->tanggal_invoice ?>">
                        </td>
                    </tr>
                    <tr>
                        <th width="13%">Alamat</th>
                        <td width="12%"><?= $data_invoice->address ?></td>
                        <th width="13%">Nomor Invoice</th>
                        <td width="12%">
                            <input type="text" name="nomor_invoice" id="" class="form-control form-control-sm" value="<?= $data_invoice->no_invoice ?>">
                        </td>
                    </tr>
                    <tr>
                        <th width="13%">Ditujukan</th>
                        <td width="12%">Finance Dept.</td>
                        <th width="13%">Nomor PO</th>
                        <td width="12%">
                            <input type="text" name="no_po" id="" class="form-control form-control-sm" value="<?= $data_invoice->no_po ?>">
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="box-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">No.</th>
                        <th class="text-center">Term of Payment</th>
                        <th class="text-center">Persentase</th>
                        <th class="text-center">Nominal</th>
                        <th class="text-center">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td class="text-left"><?= $data_actual_plan_tagih->term_payment ?></td>
                        <td class="text-center"><?= number_format($data_actual_plan_tagih->persen_payment, 2) ?>%</td>
                        <td class="text-right">Rp. <?= number_format($data_actual_plan_tagih->nominal_payment, 2) ?></td>
                        <td class="text-left"><?= $data_actual_plan_tagih->desc_payment ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="box-body">
            <table class="table table-bordered">
                <tr>
                    <th width="10%">DPP</th>
                    <td class="text-right">
                        Rp. <?= number_format($dpp, 2) ?>
                        <input type="hidden" name="total_nominal_jurnal" value="<?= $dpp ?>">
                    </td>
                </tr>
                <tr>
                    <th width="10%">DPP Lain-lain</th>
                    <td class="text-right">
                        Rp. <?= number_format($dpp_lain_lain, 2) ?>
                        <input type="hidden" name="dpp_lain_lain_jurnal" value="<?= $dpp_lain_lain ?>">
                    </td>
                </tr>
                <tr>
                    <th width="10%">PPn 12% dari DPP Lain</th>
                    <td class="text-right">
                        Rp. <?= number_format($ppn, 2) ?>
                        <input type="hidden" name="ppn_jurnal" value="<?= $ppn ?>">
                    </td>
                </tr>
                <tr>
                    <th width="10%">Total Tagihan + PPN</th>
                    <td class="text-right">
                        Rp. <?= number_format($tagihan_ppn, 2) ?>
                        <input type="hidden" name="tagihan_ppn_jurnal" value="<?= $tagihan_ppn ?>">
                    </td>
                </tr>
                <tr>
                    <th width="10%">Pph 23</th>
                    <td class="text-right">
                        - <span style="color: red;">Rp. <?= number_format($pph, 2) ?></span>
                        <input type="hidden" name="pph_jurnal" value="<?= $pph ?>">
                    </td>
                </tr>
                <tr>
                    <th width="10%">Total Akhir</th>
                    <td class="text-right">
                        <span style="font-weight: bold;">Rp. <?= number_format($total_akhir_jurnal, 2) ?></span>
                        <input type="hidden" name="total_akhir_jurnal" value="<?= $total_akhir_jurnal ?>">
                    </td>
                </tr>
            </table>

            <br><br>

            <table class="table table-striped">
                <thead>
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
                    <?= $hasil_jurnal ?>
                </tbody>
                <tbody>
                    <tr>
                        <th colspan="4" class="text-center">Balancing</th>
                        <th class="text-right th_total_debit">
                            <?= number_format($total_debit) ?>
                        </th>
                        <th class="text-right th_total_kredit">
                            <?= number_format($total_kredit) ?>
                        </th>
                    </tr>
                </tbody>
            </table>

            <br><br>

            <button type="submit" class="btn btn-sm btn-success">Update</button>
            <a href="<?= base_url('invoicing') ?>" class="btn btn-sm btn-danger">Back</a>
        </div>

    </form>
</div>

<script>
    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        swal({
            type: 'warning',
            title: 'Warning !',
            text: 'This data will be updated !',
            showCancelButton: true
        }, function(next) {
            if (next) {
                var formData = $('#frm-data').serialize();

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'update_invoice',
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    success: function(result) {
                        if (result.status == 1) {
                            swal({
                                type: 'success',
                                title: 'Success !',
                                text: result.msg
                            }, function(lanjut) {
                                window.location.href = siteurl + active_controller;
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Failed !',
                                text: result.msg
                            });
                        }
                    },
                    error: function(result) {
                        swal({
                            type: 'error',
                            title: 'Error !',
                            text: 'Please try again later !'
                        });
                    }
                });
            }
        });
    });
</script>