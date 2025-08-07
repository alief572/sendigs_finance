<?php
$total_nominal = (!empty($data_actual)) ? $data_actual->nominal_payment : 0;
$dpp_nilai_lain = ($total_nominal * 11 / 12);
$pajak = ($dpp_nilai_lain * 12 / 100);
$total_akhir = ($total_nominal + $pajak);
$dpp_lain_lain = ($total_nominal * 11 / 12);

$total_nominal_jurnal = (!empty($data_actual)) ? $data_actual->nominal_payment : 0;
$ppn = ($dpp_lain_lain * 12 / 100);
$pph = ($total_nominal * 2 / 100);
$total_akhir_jurnal = ($total_nominal_jurnal + $ppn - $pph);
?>
<div class="box">
    <form action="" method="post" id="frm-data">
        <input type="hidden" name="id" value="<?= $data_actual->id ?>">
        <div class=" box-body">
            <div class="col-6">
                <table width="100%" border="0">
                    <tr>
                        <th width="13%">Kepada</th>
                        <td width="12%"><?= $data_actual->nm_customer ?></td>
                        <th width="13%">Tanggal Invoice</th>
                        <td width="12%">
                            <input type="date" name="tanggal_invoice" id="" class="form-control form-control-sm" placeholder="Tanggal Invoice">
                        </td>
                    </tr>
                    <tr>
                        <th width="13%">Alamat</th>
                        <td width="12%"><?= $data_actual->address ?></td>
                        <th width="13%">Nomor Invoice</th>
                        <td width="12%">
                            <input type="text" name="nomor_invoice" id="" class="form-control form-control-sm" placeholder="Nomor Invoice">
                        </td>
                    </tr>
                    <tr>
                        <th width="13%">Ditujukan</th>
                        <td width="12%">Finance Dept.</td>
                        <th width="13%">Nomor PO</th>
                        <td width="12%">
                            <input type="text" name="nomor_po" id="" class="form-control form-control-sm">
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
                        <td class="text-left"><?= $data_actual->term_payment ?></td>
                        <td class="text-center"><?= number_format($data_actual->persen_payment, 2) ?>%</td>
                        <td class="text-right">Rp. <?= number_format($data_actual->nominal_payment, 2) ?></td>
                        <td class="text-left"><?= $data_actual->desc_payment ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="box-body">
            <table class="table table-bordered">
                <tr>
                    <th width="10%">DPP</th>
                    <td class="text-right">
                        Rp. <?= number_format($total_nominal, 2) ?>
                        <input type="hidden" name="total_nominal" value="<?= $total_nominal ?>">
                    </td>
                </tr>
                <tr>
                    <th width="10%">DPP Nilai Lain</th>
                    <td class="text-right">
                        Rp. <?= number_format($dpp_nilai_lain, 2) ?>
                        <input type="hidden" name="dpp_nilai_lain" value="<?= $dpp_nilai_lain ?>">
                    </td>
                </tr>
                <tr>
                    <th width="10%">PPn 12% dari DPP Lain</th>
                    <td class="text-right">
                        Rp. <?= number_format($pajak, 2) ?>
                        <input type="hidden" name="pajak" value="<?= $pajak ?>">
                    </td>
                </tr>
                <tr>
                    <th width="10%">Total Akhir</th>
                    <td class="text-right">
                        <span style="font-weight: bold">Rp. <?= number_format($total_akhir, 2) ?></span>
                        <input type="hidden" name="total_akhir" value="<?= $total_akhir ?>">
                    </td>
                </tr>
            </table>
        </div>

        <div class="box-body">
            <table class="table table-bordered">
                <tr>
                    <th width="10%">DPP</th>
                    <td class="text-right">
                        Rp. <?= number_format($total_nominal, 2) ?>
                        <input type="hidden" name="total_nominal_jurnal" value="<?= $total_nominal ?>">
                    </td>
                </tr>
                <tr>
                    <th width="10%">DPP Lain-lain</th>
                    <td class="text-right">
                        Rp. <?= number_format($dpp_lain_lain, 2) ?>
                        <input type="hidden" name="dpp_lain_lain" value="<?= $dpp_lain_lain ?>">
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
                        Rp. <?= number_format(($total_nominal + $ppn), 2) ?>
                        <input type="hidden" name="total_tagihan_ppn" value="<?= ($total_nominal + $ppn) ?>">
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
                        <span style="font-weight: bold;">Rp. <?= number_format(($total_nominal + $ppn) - $pph, 2) ?></span>
                        <input type="hidden" name="total_akhir_jurnal" value="<?= ($total_nominal + $ppn) - $pph ?>">
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

            <button type="submit" class="btn btn-sm btn-success">Save</button>
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
            text: 'This data will be saved !',
            showCancelButton: true
        }, function(next) {
            if (next) {
                var formData = $('#frm-data').serialize();

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_invoice',
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