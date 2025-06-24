<?php
$total_nominal = $data_invoice->total_nominal;
$dpp_nilai_lain = $data_invoice->dpp_nilai_lain;
$pajak = $data_invoice->pajak;
$total_akhir = $data_invoice->total_akhir;

$total_nominal_jurnal = $data_invoice->total_nominal_jurnal;
$ppn = $data_invoice->ppn_jurnal;
$pph = $data_invoice->pph_jurnal;
$total_akhir_jurnal = $data_invoice->total_akhir_jurnal;
?>
<div class="box">
    <form action="" method="post" id="frm-data">
        <input type="hidden" name="id" value="<?= $data_invoice->id ?>">
        <div class=" box-body">
            <div class="col-6">
                <table width="100%" border="0">
                    <tr>
                        <th width="13%">Kepada</th>
                        <td width="12%"><?= $data_invoice->nm_customer ?></td>
                        <th width="13%">Tanggal Invoice</th>
                        <td width="12%">
                            <?= date('d M Y', strtotime($data_invoice->tanggal_invoice)) ?>
                        </td>
                    </tr>
                    <tr>
                        <th width="13%">Alamat</th>
                        <td width="12%"><?= $data_invoice->address ?></td>
                        <th width="13%">Nomor Invoice</th>
                        <td width="12%">
                            <?= $data_invoice->no_invoice ?>
                        </td>
                    </tr>
                    <tr>
                        <th width="13%">Ditujukan</th>
                        <td width="12%">Finance Dept.</td>
                        <th width="13%">Nomor PO</th>
                        <td width="12%">
                            <?= $data_invoice->no_po ?>
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
                    <th width="10%">Total Nominal</th>
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
                    <th width="10%">Pajak</th>
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
                    <th width="10%">Total Nominal</th>
                    <td class="text-right">
                        Rp. <?= number_format($total_nominal, 2) ?>
                        <input type="hidden" name="total_nominal_jurnal" value="<?= $total_nominal ?>">
                    </td>
                </tr>
                <tr>
                    <th width="10%">PPn 12%</th>
                    <td class="text-right">
                        Rp. <?= number_format($ppn, 2) ?>
                        <input type="hidden" name="ppn_jurnal" value="<?= $ppn ?>">
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

            <a href="<?= base_url('invoicing') ?>" class="btn btn-sm btn-danger">Back</a>
        </div>

    </form>
</div>