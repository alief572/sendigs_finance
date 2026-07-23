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
        <div class="box-body">
            <div class="col-6">
                <table width="100%" border="0">
                    <tr>
                        <th width="13%">Kepada</th>
                        <td width="12%"><?= $data_actual->nm_customer ?></td>
                        <th width="13%">Tanggal Invoice</th>
                        <td width="12%">
                            <input type="date" name="tanggal_invoice" id="tanggal_invoice" class="form-control form-control-sm" placeholder="Tanggal Invoice" onchange="syncTanggalJurnal(this.value)" value="<?= date('Y-m-d') ?>" readonly>
                        </td>
                    </tr>
                    <tr>
                        <th width="13%">Alamat</th>
                        <td width="12%"><?= $data_actual->address ?></td>
                        <th width="13%">Nomor Invoice <span class="text-red">*</span></th>
                        <td width="12%">
                            <input type="text" name="nomor_invoice" id="" class="form-control form-control-sm" value="<?= isset($preview_no_invoice) ? $preview_no_invoice : '' ?>" placeholder="- Auto Generated -" readonly>
                        </td>
                    </tr>
                    <tr>
                        <th width="13%">Ditujukan</th>
                        <td width="12%">Finance Dept.</td>
                        <th width="13%">Nomor PO <span class="text-red">*</span></th>
                        <td width="12%">
                            <input type="text" name="nomor_po" id="" class="form-control form-control-sm" placeholder="Nomor PO" required>
                        </td>
                    </tr>
                    <tr>
                        <th width="13%">PPN</th>
                        <td width="12%">
                            <select name="ppn_persen" id="ppn_persen" class="form-control form-control-sm">
                                <option value="12">12%</option>
                                <option value="0">0%</option>
                            </select>
                        </td>
                        <th width="13%">Nomor Faktur <span class="text-red">*</span></th>
                        <td width="12%">
                            <br>
                            <input type="text" class="form-control form-control-sm" name="nomor_faktur" placeholder="Nomor Faktur" required>
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

        <input type="hidden" name="total_nominal" value="<?= $total_nominal ?>">
        <input type="hidden" name="dpp_nilai_lain" value="<?= $dpp_nilai_lain ?>">
        <input type="hidden" name="pajak" value="<?= $pajak ?>">
        <input type="hidden" name="total_akhir" value="<?= $total_akhir ?>">

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
                    <th width="10%">PPN dari DPP Lain</th>
                    <td class="text-right">
                        Rp. <span id="text_ppn_jurnal"><?= number_format($ppn, 2) ?></span>
                        <input type="hidden" name="ppn_jurnal" value="<?= $ppn ?>">
                    </td>
                </tr>
                <tr>
                    <th width="10%">Total Tagihan + PPN</th>
                    <td class="text-right">
                        Rp. <span id="text_total_tagihan_ppn"><?= number_format(($total_nominal + $ppn), 2) ?></span>
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
                        <span style="font-weight: bold;">Rp. <span id="text_total_akhir_jurnal"><?= number_format(($total_nominal + $ppn) - $pph, 2) ?></span></span>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function format_num(x, decimals) {
        if(decimals > 0){
            return parseFloat(x).toLocaleString('en-US', {minimumFractionDigits: decimals, maximumFractionDigits: decimals});
        } else {
            return parseFloat(x).toLocaleString('en-US');
        }
    }

    $(document).on('change', '#ppn_persen', function() {
        var ppn_persen = parseFloat($(this).val());
        var total_nominal = parseFloat($('input[name="total_nominal"]').val()) || 0;
        var dpp_lain_lain = parseFloat($('input[name="dpp_lain_lain"]').val()) || 0;
        
        var ppn = dpp_lain_lain * ppn_persen / 100;
        var pph = total_nominal * 2 / 100;
        var total_tagihan_ppn = total_nominal + ppn;
        var total_akhir = total_tagihan_ppn - pph;

        // Update main table
        $('#text_ppn_jurnal').text(format_num(ppn, 2));
        $('#text_total_tagihan_ppn').text(format_num(total_tagihan_ppn, 2));
        $('#text_total_akhir_jurnal').text(format_num(total_akhir, 2));

        $('input[name="ppn_jurnal"]').val(ppn);
        $('input[name="total_tagihan_ppn"]').val(total_tagihan_ppn);
        $('input[name="total_akhir_jurnal"]').val(total_akhir);
        $('input[name="pajak"]').val(ppn); // update pajak as well
        $('input[name="total_akhir"]').val(total_tagihan_ppn); // update total_akhir before pph
        
        // Update journal table
        $('input[name^="coa_jurnal_"]').each(function() {
            var coa = $(this).val();
            var idx = $(this).attr('name').replace('coa_jurnal_', '');
            
            var debit = 0;
            var kredit = 0;
            
            if (coa == '1102-01-01') {
                debit = total_nominal + ppn - pph;
            } else if (coa == '2104-01-07') {
                kredit = ppn;
            } else if (coa == '1106-01-02') {
                debit = pph;
            } else if (coa == '4101-01-01') {
                kredit = total_nominal;
            }
            
            $('input[name="debit_' + idx + '"]').val(debit);
            $('input[name="kredit_' + idx + '"]').val(kredit);
            
            $('input[name="debit_' + idx + '"]').parent().contents().filter(function() { return this.nodeType === 3; }).remove();
            $('input[name="debit_' + idx + '"]').parent().prepend(format_num(debit, 0)); 
            
            $('input[name="kredit_' + idx + '"]').parent().contents().filter(function() { return this.nodeType === 3; }).remove();
            $('input[name="kredit_' + idx + '"]').parent().prepend(format_num(kredit, 0)); 
        });
        
        // Update balancing
        var total_debit = 0;
        var total_kredit = 0;
        $('input[name^="debit_"]').each(function() { total_debit += parseFloat($(this).val()) || 0; });
        $('input[name^="kredit_"]').each(function() { total_kredit += parseFloat($(this).val()) || 0; });
        $('.th_total_debit').text(format_num(total_debit, 0));
        $('.th_total_kredit').text(format_num(total_kredit, 0));
    });

    function syncTanggalJurnal(val) {
        if (!val) return;
        var dateObj = new Date(val);
        var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        var formatted = ('0' + dateObj.getDate()).slice(-2) + '-' + months[dateObj.getMonth()] + '-' + dateObj.getFullYear();
        var ymd = val; // already in Y-m-d format from input[type=date]

        $('input[name^="tgl_jurnal_"]').each(function() {
            $(this).val(ymd);
            $(this).closest('td').contents().first().replaceWith(formatted);
        });
    }

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
                    url: siteurl + active_controller + 'save_invoice',
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