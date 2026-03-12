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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="box">
    <form action="" method="post" id="frm-data">
        <input type="hidden" name="id_penawaran" value="<?= $data_penawaran->id_penawaran ?>">
        <input type="hidden" name="total_penawaran" value="<?= ($data_penawaran->grand_total - $total_invoiced) ?>">
        <div class="box-body">
            <div class="col-6">
                <table width="100%" border="0">
                    <tr>
                        <th width="13%">Kepada</th>
                        <td width="11%">
                            <input type="text" name="nm_customer" id="" class="form-control form-control-sm" value="<?= $data_penawaran->nm_customer ?>">
                        </td>
                        <th width="13%">Tanggal Invoice</th>
                        <td width="12%">
                            <input type="date" name="tanggal_invoice" id="" class="form-control form-control-sm" placeholder="Tanggal Invoice" value="<?= date('Y-m-d') ?>" readonly>
                        </td>
                    </tr>
                    <tr>
                        <th width="13%">Alamat</th>
                        <td width="12%">
                            <textarea name="address" id="" class="form-control form-control-sm" rows="3"><?= $data_penawaran->address ?></textarea>
                        </td>
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
                        <th class="text-center" width="5%">Action</th>
                    </tr>
                </thead>
                <tbody class="list_item">
                    <?php
                    $no_item = 0;
                    foreach ($data_penawaran_detail as $item) {
                        $no_item++;

                        echo '
                                <tr class="tr_item_' . $no_item . '">
                                    <td class="text-center">' . $no_item . '</td>
                                    <td><textarea name="item[' . $no_item . '][nama]" class="form-control form-control-sm">' . $item->nm_item . '</textarea></td>
                                    <td><input type="number" name="item[' . $no_item . '][qty]" id="" class="form-control form-control-sm" min="1" value="' . round($item->qty) . '" onchange="hitung_all();"></td>
                                    <td><input type="text" name="item[' . $no_item . '][harga]" id="" class="form-control form-control-sm text-right auto_num" value="' . $item->harga . '" onchange="hitung_all();"></td>
                                    <td><input type="text" name="item[' . $no_item . '][total]" id="" class="form-control form-control-sm text-right auto_num" value="' . $item->total . '"></td>
                                    <td><button type="button" class="btn btn-sm btn-danger remove_item" data-no="' . $no_item . '"><i class="fa fa-trash"></i></button></td>
                                </tr>
                            ';
                    }
                    ?>
                </tbody>
                <tfoot class="footer_item bg-gray">
                    <tr>
                        <th colspan="4" class="text-center">Discount</th>
                        <th>
                            <input type="text" name="discount" id="discount" class="form-control form-control-sm text-right auto_num" value="<?= $data_penawaran->nominal_disc ?>" readonly>
                        </th>
                        <th></th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-center">Biaya Kirim</th>
                        <th>
                            <input type="text" name="biaya_kirim" id="" class="form-control form-control-sm text-right auto_num" value="<?= $data_penawaran->biaya_kirim ?>" onchange="hitung_all();" readonly>
                        </th>
                        <th></th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-center">PPn (Consultant)</th>
                        <th>
                            <input type="text" name="ppn_consultant" id="ppn_consultant" class="form-control form-control-sm text-right auto_num" value="<?= $data_penawaran->ppn ?>" readonly>
                        </th>
                        <th></th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-center">Total</th>
                        <th>
                            <input type="text" name="total" id="total" class="form-control form-control-sm text-right auto_num" value="0" readonly>
                        </th>
                        <th></th>
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
                                <td>Outstanding Invoice</td>
                                <td>
                                    <input type="text" name="outstanding_invoice" id="outstanding_invoice" class="form-control form-control-sm text-right auto_num" value="<?= ($data_penawaran->grand_total - $total_invoiced) ?>" readonly>
                                </td>
                            </tr>
                            <tr>
                                <td>DPP</td>
                                <td>
                                    <input type="text" name="dpp" id="dpp" class="form-control form-control-sm text-right auto_num" value="0" readonly>
                                </td>
                            </tr>
                            <tr>
                                <td>DPP Lain-lain</td>
                                <td>
                                    <input type="text" name="dpp_lain_lain" id="dpp_lain_lain" class="form-control form-control-sm text-right auto_num" value="0" readonly>
                                </td>
                            </tr>
                            <tr>
                                <td>PPN 12% dari DPP lain</td>
                                <td>
                                    <input type="text" name="ppn" id="ppn" class="form-control form-control-sm text-right auto_num" value="0" readonly>
                                </td>
                            </tr>
                            <tr>
                                <td>Total Tagihan + PPN 12%</td>
                                <td>
                                    <input type="text" name="total_tagihan_ppn" id="total_tagihan_ppn" class="form-control form-control-sm text-right auto_num" value="0" readonly>
                                </td>
                            </tr>
                            <tr>
                                <td>Pph 23</td>
                                <td>
                                    <input type="text" name="pph" id="pph" class="form-control form-control-sm text-right auto_num" value="0" readonly>
                                </td>
                            </tr>
                            <tr class="bg-gray text-bold">
                                <td>Total Tagihan + PPn - Pph</td>
                                <td>
                                    <input type="text" name="total_tagihan_all" id="total_tagihan_all" class="form-control form-control-sm text-right auto_num" value="0" readonly>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="box-body" style="display: none;">
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


        <!-- <input type="hidden" name="total_nominal" value="<?= $ttl_grand ?>">
        <input type="hidden" name="dpp_nilai_lain" value="<?= $dpp_nilai_lain ?>">
        <input type="hidden" name="pajak" value="<?= $pajak ?>">
        <input type="hidden" name="total_akhir" value="<?= $total_akhir ?>">
        <input type="hidden" name="total_nominal_jurnal" value="<?= $total_nominal ?>">
        <input type="hidden" name="dpp_lain_lain" value="<?= $dpp_lain_lain ?>">
        <input type="hidden" name="ppn_jurnal" value="<?= $ppn ?>">
        <input type="hidden" name="total_tagihan_ppn" value="<?= ($total_nominal + $ppn) ?>">
        <input type="hidden" name="pph_jurnal" value="<?= $pph ?>">
        <input type="hidden" name="total_akhir_jurnal" value="<?= ($total_nominal + $ppn) - $pph ?>"> -->

        <div class="box-body">
            <h4>Jurnal Invoice</h4>
            <table class="table table-bordered table-striped">
                <thead class="bg-primary">
                    <tr>
                        <th class="text-center" width="10%">Tanggal Jurnal</th>
                        <th class="text-center" width="35%">COA</th>
                        <th class="text-center" width="15%">Nama Company</th>
                        <th class="text-center" width="15%">Nama Account</th>
                        <th class="text-center" width="15%">Debit</th>
                        <th class="text-center" width="15%">Credit</th>
                    </tr>
                </thead>
                <tbody class="list_jurnal">

                </tbody>
                <tfoot class="bg-gray">
                    <tr>
                        <th colspan="4" class="text-center">Balancing</th>
                        <th class="text-right th_total_debit">
                            <?= number_format(0) ?>
                        </th>
                        <th class="text-right th_total_kredit">
                            <?= number_format(0) ?>
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

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var no = "<?= $no_item ?>";

    $(document).ready(function() {
        $('.auto_num').autoNumeric('init');
        $('.select2').select2({
            width: '100%'
        });
        hitung_all();
    })

    $(document).on('click', '.add_item', function() {
        no++;

        var html = '<tr class="tr_item_' + no + '">';
        html += '<td class="text-center">' + no + '</td>';
        html += '<td><textarea name="item[' + no + '][nama]" class="form-control form-control-sm"></textarea></td>';
        html += '<td><input type="number" name="item[' + no + '][qty]" id="" class="form-control form-control-sm" min="1" onchange="hitung_all();"></td>';
        html += '<td><input type="text" name="item[' + no + '][harga]" id="" class="form-control form-control-sm text-right auto_num" onchange="hitung_all();"></td>';
        html += '<td><input type="text" name="item[' + no + '][total]" id="" class="form-control form-control-sm text-right auto_num"></td>';
        html += '<td><button type="button" class="btn btn-sm btn-danger remove_item" data-no="' + no + '"><i class="fa fa-trash"></i></button></td>';
        html += '</tr>';
        $('.list_item').append(html);


        $('.auto_num').autoNumeric('init');
    });

    $(document).on('click', '.remove_item', function() {
        var no = $(this).data('no');
        $('.tr_item_' + no).remove();
        hitung_all();
    });

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        var total_penawaran = $('input[name="outstanding_invoice"]').val();
        if (total_penawaran !== '') {
            total_penawaran = total_penawaran.split(',').join('');
            total_penawaran = parseFloat(total_penawaran);
        } else {
            total_penawaran = 0;
        }

        var totalx = $('input[name="dpp"]').val();
        if (totalx !== '') {
            totalx = totalx.split(',').join('');
            totalx = parseFloat(totalx);
        } else {
            totalx = 0;
        }

        if (totalx > total_penawaran) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning !',
                text: 'Total DPP tidak boleh melebihi outstanding invoice !',
                showCancelButton: false
            });

            return false;   
        }

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

    $(document).on('change', 'select[name="jurnal_invoice_no_coa"]', function() {
        var no = $(this).data('no');
        var no_coa = $(this).val();

        $.ajax({
            type: 'get',
            url: siteurl + active_controller + 'change_jurnal_invoice',
            data: {
                'no': no,
                'no_coa': no_coa
            },
            cache: false,
            dataType: 'json',
            success: function(result) {
                $('.colm_nm_coa').html(result.hasil);
            },
            error: function(xhr, status, error) {
                swal.fire({
                    title: 'Error !',
                    text: 'Please try again later !',
                    type: 'error',
                    allowEscapeKey: false,
                    allowOutsideClick: false
                }, function(next) {
                    location.reload(true);
                });
            }
        });
    })

    function hitung_all() {
        var id_penawaran = $('input[name="id_penawaran"]').val();

        var totall = 0;

        for (var i = 1; i <= no; i++) {
            var $qtyInput = $('input[name="item[' + i + '][qty]"]');
            var $hargaInput = $('input[name="item[' + i + '][harga]"]');

            // Pastikan input ada sebelum ambil value
            if ($qtyInput.length && $hargaInput.length) {
                var qty = parseFloat($qtyInput.val()) || 0;

                // Ambil value, ubah ke string, buang koma, lalu ubah ke float
                var hargaRaw = $hargaInput.val() || "0";
                var harga = parseFloat(String(hargaRaw).replace(/,/g, '')) || 0;

                var totals = qty * harga;

                $('input[name="item[' + i + '][total]"]').autoNumeric('set', totals);
                totall += totals;
            }
        }

        var discount = $('input[name="discount"]').val();
        if (discount == '') {
            discount = 0;
        } else {
            discount = discount.split(',').join('');
            discount = parseFloat(discount);
        }

        var biaya_kirim = $('input[name="biaya_kirim"]').val();
        if (biaya_kirim == '') {
            biaya_kirim = 0;
        } else {
            biaya_kirim = biaya_kirim.split(',').join('');
            biaya_kirim = parseFloat(biaya_kirim);
        }

        var ppn_consultant = $('input[name="ppn_consultant"]').val();
        if (ppn_consultant == '') {
            ppn_consultant = 0;
        } else {
            ppn_consultant = ppn_consultant.split(',').join('');
            ppn_consultant = parseFloat(ppn_consultant);
        }

        totall -= discount;
        totall += biaya_kirim;
        totall += ppn_consultant;

        $('#total').autoNumeric('set', totall);
        $('#dpp').autoNumeric('set', totall);

        var dpp_lain_lain = totall * 11 / 12;
        $('#dpp_lain_lain').autoNumeric('set', dpp_lain_lain);

        var ppn = dpp_lain_lain * 12 / 100;
        $('#ppn').autoNumeric('set', ppn);

        var tagihan_ppn = totall + ppn;
        $('#total_tagihan_ppn').autoNumeric('set', tagihan_ppn);

        var pph = totall * 2 / 100;
        $('#pph').autoNumeric('set', pph);

        var total_tagihan_all = totall + ppn - pph;
        $('#total_tagihan_all').autoNumeric('set', total_tagihan_all);

        $.ajax({
            type: 'get',
            url: siteurl + active_controller + 'hitung_jurnal',
            data: {
                'id_penawaran': id_penawaran,
                'dpp': totall,
                'dpp_lain_lain': dpp_lain_lain,
                'ppn': ppn,
                'pph': pph,
                'total_tagihan_all': total_tagihan_all,
            },
            cache: false,
            dataType: 'json',
            success: function(result) {
                $('.list_jurnal').html(result.hasil_jurnal);
                $('.th_total_debit').html(result.total_debit);
                $('.th_total_kredit').html(result.total_kredit);

                $('.select2').select2();
            }
        });
    }
</script>