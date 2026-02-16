<?php
// $total_nominal = (!empty($data_actual)) ? $data_actual->nominal_payment : 0;
// $dpp_nilai_lain = ($total_nominal * 11 / 12);
// $pajak = ($dpp_nilai_lain * 12 / 100);
// $total_akhir = ($total_nominal + $pajak);
// $dpp_lain_lain = ($total_nominal * 11 / 12);

// $total_nominal_jurnal = (!empty($data_actual)) ? $data_actual->nominal_payment : 0;
// $ppn = ($dpp_lain_lain * 12 / 100);
// $pph = ($total_nominal * 2 / 100);
// $total_akhir_jurnal = ($total_nominal_jurnal + $ppn - $pph);
?>
<div class="box">
    <form action="" method="post" id="frm-data">
        <input type="hidden" name="id_penawaran" value="<?= $data_penawaran->id_penawaran ?>">
        <div class="box-body">
            <div class="col-6">
                <table width="100%" border="0">
                    <tr>
                        <th width="13%">Kepada</th>
                        <td width="12%"><?= $data_penawaran->nm_customer ?></td>
                        <th width="13%">Tanggal Invoice</th>
                        <td width="12%">
                            <input type="date" name="tanggal_invoice" id="" class="form-control form-control-sm" placeholder="Tanggal Invoice">
                        </td>
                    </tr>
                    <tr>
                        <th width="13%">Alamat</th>
                        <td width="12%"><?= $data_penawaran->address ?></td>
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
                    <tr>
                        <th width="13%"></th>
                        <td width="12%"></td>
                        <th width="13%">Nomor Faktur</th>
                        <td width="12%">
                            <br>
                            <input type="text" class="form-control form-control-sm" name="nomor_faktur" placeholder="Nomor Faktur">
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="box-body">
            <button type="button" class="btn btn-sm btn-success add_item"><i class="fa fa-plus"></i> Tambah Item</button> <br>
            <table class="table table-striped table-bordered">
                <thead class="bg-primary">
                    <tr>
                        <th class="text-center" width="5%">No.</th>
                        <th class="text-center" width="15%">Item</th>
                        <th class="text-center" width="15%">Qty</th>
                        <th class="text-center" width="15%">Harga</th>
                        <th class="text-center" width="15%">Total</th>
                        <th class="text-center" width="15%">Action</th>
                    </tr>
                </thead>
                <tbody class="list_item">

                </tbody>
                <tfoot class="footer_item">
                    <tr>
                        <th colspan="4" class="text-center">Biaya Kirim</th>
                        <th>
                            <input type="text" name="biaya_kirim" id="" class="form-control form-control-sm text-right auto_num" value="0">
                        </th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="box-body">
            <h4>Detail Penawaran</h4>
            <table class="table table-striped table-bordered">
                <thead class="bg-primary">
                    <tr>
                        <th class="text-center">No.</th>
                        <th class="text-center">Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Harga</th>
                        <th class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 0;
                    $ttl_grand = 0;
                    if (!empty($data_penawaran_detail)) :
                        foreach ($data_penawaran_detail as $item) :
                            $no++;
                    ?>
                            <tr>
                                <td class="text-center"><?= $no ?></td>
                                <td><?= $item->nm_item ?></td>
                                <td class="text-center"><?= round($item->qty) ?></td>
                                <td class="text-right"><?= number_format($item->harga, 2) ?></td>
                                <td class="text-right"><?= number_format($item->total, 2) ?></td>
                            </tr>
                        <?php

                            $ttl_grand += ($item->total);
                        endforeach;
                    else :
                        ?>
                        <tr>
                            <td colspan="5" class="text-center">Data tidak ditemukan</td>
                        </tr>
                    <?php
                    endif;

                    $total_nominal = $ttl_grand;
                    $dpp_nilai_lain = ($total_nominal * 11 / 12);
                    $pajak = ($dpp_nilai_lain * 12 / 100);
                    $total_akhir = ($total_nominal + $pajak);
                    $dpp_lain_lain = ($total_nominal * 11 / 12);

                    $total_nominal_jurnal = $ttl_grand;
                    $ppn = ($dpp_lain_lain * 12 / 100);
                    $pph = ($total_nominal * 2 / 100);
                    $total_akhir_jurnal = ($total_nominal_jurnal + $ppn - $pph);
                    ?>
                </tbody>
                <tfoot class="bg-gray">
                    <tr>
                        <th colspan="4" class="text-right">DPP</th>
                        <th class="text-right"><?= number_format($total_nominal, 2) ?></th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-right">DPP Lain-lain</th>
                        <th class="text-right"><?= number_format($dpp_lain_lain, 2) ?></th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-right">PPn 12% dari DPP Lain</th>
                        <th class="text-right"><?= number_format($ppn, 2) ?></th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-right">Total Tagihan + PPN</th>
                        <th class="text-right"><?= number_format(($total_nominal + $ppn), 2) ?></th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-right">PPh 23</th>
                        <th class="text-right"><?= number_format($pph, 2) ?></th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-right">Total Akhir</th>
                        <th class="text-right"><?= number_format(($total_nominal + $ppn) - $pph, 2) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>


        <input type="hidden" name="total_nominal" value="<?= $ttl_grand ?>">
        <input type="hidden" name="dpp_nilai_lain" value="<?= $dpp_nilai_lain ?>">
        <input type="hidden" name="pajak" value="<?= $pajak ?>">
        <input type="hidden" name="total_akhir" value="<?= $total_akhir ?>">
        <input type="hidden" name="total_nominal_jurnal" value="<?= $total_nominal ?>">
        <input type="hidden" name="dpp_lain_lain" value="<?= $dpp_lain_lain ?>">
        <input type="hidden" name="ppn_jurnal" value="<?= $ppn ?>">
        <input type="hidden" name="total_tagihan_ppn" value="<?= ($total_nominal + $ppn) ?>">
        <input type="hidden" name="pph_jurnal" value="<?= $pph ?>">
        <input type="hidden" name="total_akhir_jurnal" value="<?= ($total_nominal + $ppn) - $pph ?>">

        <div class="box-body">
            <h4>Jurnal Invoice</h4>
            <table class="table table-striped">
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
                    <?= $hasil_jurnal ?>
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

            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
            <a href="<?= base_url('invoicing') ?>" class="btn btn-danger"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
    $('.auto_num').autoNumeric('init');

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Warning !',
            text: 'This data will be saved !',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
        }).then((next) => {
            if (next.isConfirmed) {
                var formData = $('#frm-data').serialize();

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_invoice_non_konsultasi',
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    success: function(result) {
                        if (result.status == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success !',
                                text: result.msg,
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                timer: 3000
                            }).then((next) => {
                                window.location.href = siteurl + active_controller;
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Failed !',
                                text: result.msg,
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    error: function(result) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error !',
                            text: 'Please try again later !',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });
            }
        });
    });
</script>