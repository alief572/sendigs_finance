<?php
$id_dept        = (!empty($header)) ? $header[0]->id_dept : '';
$id_costcenter  = (!empty($header)) ? $header[0]->id_costcenter : '';
$budget         = (!empty($header)) ? number_format($header[0]->budget) : '0';
$sisa_budget    = (!empty($header)) ? number_format($header[0]->sisa_budget) : '0';
$coa            = (!empty($header)) ? $header[0]->coa : '';
$upload_spk     = (!empty($header)) ? $header[0]->document : '';
$no_so          = (!empty($header)) ? $header[0]->no_so : '';
$project_name   = (!empty($header)) ? $header[0]->project_name : '';
$pr_coa         = (!empty($header)) ? $header[0]->coa : '';
$tingkat_pr     = (!empty($header)) ? $header[0]->tingkat_pr : '';
$nm_pembuat     = (!empty($header)) ? $header[0]->nm_pembuat : '';
$tgl_dibuat     = (!empty($header) && !empty($header[0]->created_date)) ? date('d-M-Y', strtotime($header[0]->created_date)) : '-';
$bank_name         = (!empty($header)) ? $header[0]->bank_name : '';
$bank_account_no   = (!empty($header)) ? $header[0]->bank_account_no : '';
$bank_account_name = (!empty($header)) ? $header[0]->bank_account_name : '';

// Detail Approval
$alasan_reject1 = (!empty($header)) ? $header[0]->reject_reason1 : '';
$alasan_reject2 = (!empty($header)) ? $header[0]->reject_reason2 : '';
$alasan_reject3 = (!empty($header)) ? $header[0]->reject_reason3 : '';

$keterangan_1   = (!empty($header)) ? $header[0]->keterangan_1 : '';
$keterangan_2   = (!empty($header)) ? $header[0]->keterangan_2 : '';
$keterangan_3   = (!empty($header)) ? $header[0]->keterangan_3 : '';

$status1        = '';
$tgl_appre_1    = '';
$status2        = '';
$tgl_appre_2    = '';
$status3        = '';
$tgl_appre_3    = '';

if (!empty($header)) {
    if ($header[0]->app_3 == '1') {
        $status3     = '<span class="badge" style="background:#00a65a; font-size:11px;">Approved</span>';
        $tgl_appre_3 = date('d F Y', strtotime($header[0]->app_3_date));
    } else {
        if ($header[0]->sts_reject3 == '1') {
            $status3     = '<span class="badge" style="background:#dd4b39; font-size:11px;">Rejected</span>';
            $tgl_appre_3 = date('d F Y', strtotime($header[0]->sts_reject3_date));
        } else {
            $status3 = '<span class="badge" style="background:#f39c12; font-size:11px;">Waiting</span>';
        }
    }
}
// End Detail Status

$tanda     = (!empty($code)) ? 'Update' : 'Insert';
$disabled  = (!empty($approve)) ? 'disabled' : '';
$disabled2 = ($approve == 'view') ? 'disabled' : '';
$disabled3 = ($approve == 'view') ? 'readonly' : '';
?>
<style>
    .section-title {
        font-size: 13px;
        font-weight: 700;
        color: #3c8dbc;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #3c8dbc;
        padding-bottom: 6px;
        margin-bottom: 15px;
    }

    .breadcrumb {
        background: none;
        padding: 0;
        font-size: 12px;
        margin-bottom: 5px;
    }

    .approval-progress {
        display: flex;
        gap: 0;
        border: 1px solid #ddd;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .approval-step {
        flex: 1;
        padding: 12px 15px;
        border-right: 1px solid #ddd;
        background: #f9f9f9;
    }

    .approval-step:last-child {
        border-right: none;
    }

    .approval-step .step-label {
        font-size: 10px;
        font-weight: 700;
        color: #777;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .approval-step .step-status {
        margin-bottom: 4px;
    }

    .approval-step .step-date {
        font-size: 11px;
        color: #888;
        margin-bottom: 4px;
    }

    .approval-step .step-reason {
        font-size: 11px;
        color: #c0392b;
        font-style: italic;
    }

    .detail-table thead tr th {
        background-color: #3c8dbc;
        color: #fff;
        font-size: 12px;
        border-color: #357ca5;
    }

    .action-footer {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #eee;
    }

    .chosen-container-active .chosen-single {
        border: none;
        box-shadow: none;
    }

    .chosen-container-single .chosen-single {
        height: 34px;
        border: 1px solid #d2d6de;
        border-radius: 0px;
        background: none;
        box-shadow: none;
        color: #444;
        line-height: 32px;
    }

    .chosen-container-single .chosen-single div {
        top: 5px;
    }

    .datepicker {
        cursor: pointer;
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" integrity="sha512-0nkKORjFgcyxv3HbE4rzFUlENUMNqic/EzDIeYCgsKa/nwqr2B91Vu/tNAu4Q0cBuG4Xe/D1f/freEci/7GDRA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<form action="#" method="POST" id="form_ct" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="id" value="<?= $id; ?>">
    <input type="hidden" name="tanda" value="<?= $tanda; ?>">
    <input type="hidden" id="approve" name="approve" value="<?= $approve; ?>">
    <input type="hidden" name="tingkat_approval" id="tingkat_approval" value="<?= $tingkat_approval ?>">

    <div class="box box-primary">
        <div class="box-header with-border">
            <div>
                <ol class="breadcrumb">
                    <li><i class="fa fa-shopping-cart"></i> Procurement</li>
                    <li><a href="<?= site_url('non_rutin') ?>">PR Non-Rutin</a></li>
                    <li class="active">Form PR (Finance)</li>
                </ol>
                <h3 class="box-title" style="font-size:16px; font-weight:700;"><?php echo $title; ?></h3>
            </div>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-sm btn-default" id="back">
                    <i class="fa fa-arrow-left"></i>&nbsp; Kembali
                </button>
            </div>
        </div>
        <!-- /.box-header -->

        <div class="box-body">

            <!-- Section: Informasi PR -->
            <div class="section-title"><i class="fa fa-info-circle"></i>&nbsp; Informasi PR</div>

            <div class="form-group row">
                <label class="label-control col-sm-2"><b>Department <span class="text-red">*</span></b></label>
                <div class="col-sm-4">
                    <select name="id_dept" id="id_dept" class="form-control input-md chosen_select" <?= $disabled; ?>>
                        <option value="0">Select An Department</option>
                        <?php
                        foreach ($list_departement as $departement) {
                            $selected = '';
                            if ($departement->id == $id_dept) {
                                $selected = 'selected';
                            }
                            echo "<option value='" . $departement->id . "' " . $selected . ">" . strtoupper($departement->name . ' - ' . $departement->nm_company) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <label class="label-control col-sm-2"><b>Project Name</b></label>
                <div class="col-sm-4">
                    <?php
                    echo form_input(array('id' => 'project_name', 'name' => 'project_name', 'class' => 'form-control input-md', 'placeholder' => 'Project Name'), $project_name);
                    ?>
                </div>
            </div>

            <div class="form-group row">
                <label class="label-control col-sm-2"><b>Upload Document</b></label>
                <div class="col-sm-4 text-right">
                    <input type="file" id="upload_spk" name="upload_spk" class="form-control input-md" placeholder="Upload Document">
                    <?php if (!empty($upload_spk)) { ?>
                        <a href="<?= base_url('assets/pr/' . $upload_spk); ?>" target="_blank" title="Download" data-role="qtip">Download</a>
                    <?php } ?>
                </div>
                <label class="label-control col-sm-2"><b>COA <span class="text-red">*</span></b></label>
                <div class="col-sm-4">
                    <select name="coa" id="coa" class="form-control chosen_select" required>
                        <option value="">- Select COA -</option>
                        <?php
                        foreach ($list_coa as $coa) :
                            $selected = "";
                            if ($coa['no_perkiraan'] == $pr_coa) {
                                $selected = "selected";
                            }
                            echo '<option value="' . $coa['no_perkiraan'] . '" ' . $selected . '>' . $coa['no_perkiraan'] . ' - ' . $coa['nama'] . '</option>';
                        endforeach;
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="label-control col-sm-2"><b>Tingkat PR</b></label>
                <div class="col-sm-4">
                    <select name="tingkat_pr" id="" class="form-control input-md">
                        <option value="1" <?= ($tingkat_pr == '1') ? 'selected' : null ?>>Normal</option>
                        <option value="2" <?= ($tingkat_pr == '2') ? 'selected' : null ?>>Urgent</option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="label-control col-sm-2"><b>Nama Pembuat PR</b></label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" value="<?= ucwords(strtolower($nm_pembuat)) ?>" readonly>
                </div>
                <label class="label-control col-sm-2"><b>Tgl Dibuat PR</b></label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" value="<?= $tgl_dibuat ?>" readonly>
                </div>
            </div>

            <!-- Section: Status Approval -->
            <div class="section-title" style="margin-top:20px;"><i class="fa fa-check-circle"></i>&nbsp; Status Approval</div>

            <div class="approval-progress">
                <div class="approval-step">
                    <div class="step-label"><i class="fa fa-building"></i>&nbsp; Management</div>
                    <div class="step-status"><?= $status3 ?: '<span class="badge" style="background:#aaa; font-size:11px;">Belum Diproses</span>' ?></div>
                    <?php if (!empty($tgl_appre_3)) { ?>
                        <div class="step-date"><i class="fa fa-calendar"></i>&nbsp; <?= $tgl_appre_3 ?></div>
                    <?php } ?>
                    <?php if (!empty($alasan_reject3)) { ?>
                        <div class="step-reason"><i class="fa fa-times"></i>&nbsp; <?= $alasan_reject3 ?></div>
                    <?php } ?>
                    <div style="margin-top:8px;">
                        <input type="hidden" name="reject_reason3" value="<?= $alasan_reject3 ?>">
                        <input type="text" name="keterangan_3" class="form-control input-sm" placeholder="Keterangan..." value="<?= $keterangan_3 ?>" style="font-size:11px;">
                    </div>
                </div>
            </div>

            <?php if ($approve == 'approve') { ?>
                <div class="form-group row">
                    <label class="label-control col-sm-2"><b>Approve <span class="text-red">*</span></b></label>
                    <div class="col-sm-2">
                        <select name="sts_app" id="sts_app" class="form-control input-md">
                            <option value="0">Select Approve</option>
                            <option value="Y">Approve</option>
                            <option value="D">Reject</option>
                        </select>
                    </div>
                    <div class="col-sm-2"></div>
                    <label class="label-control col-sm-2 tnd_reason"><b>Reason <span class="text-red">*</span></b></label>
                    <div class="col-sm-4 tnd_reason">
                        <?php
                        echo form_textarea(array('id' => 'reason', 'name' => 'reason', 'class' => 'form-control input-md', 'rows' => '2', 'cols' => '75', 'placeholder' => 'Reason'));
                        ?>
                    </div>
                </div>
            <?php } ?>

            <!-- Section: Detail Barang/Jasa -->
            <div class="section-title" style="margin-top:20px;"><i class="fa fa-list"></i>&nbsp; Detail Barang / Jasa</div>

            <table class="table table-striped table-bordered table-hover table-condensed detail-table" width="100%">
                <thead>
                    <tr>
                        <th class="text-center" style="width:3%;">#</th>
                        <th class="text-center">Nama Barang/Jasa</th>
                        <th class="text-center" style="width:13%;">Spec/ Requirement</th>
                        <th class="text-center" style="width:7%;">Qty</th>
                        <th class="text-center" style="width:8%;">Satuan</th>
                        <th class="text-center" style="width:9%;">Est Harga</th>
                        <th class="text-center" style="width:9%;">Est Total Harga</th>
                        <th class="text-center" style="width:9%;">Tanggal Dibutuhkan</th>
                        <th class="text-center" style="width:15%;">Keterangan</th>
                        <?php if (empty($approve)) { ?>
                            <th class="text-center" style="width:8%;">#</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $nomor = 0;
                    if (!empty($detail)) {
                        foreach ($detail as $val => $valx) {
                            $nomor++;
                            echo "<tr class='header_" . $nomor . "'>";
                            echo "<td align='center'>" . $nomor . "<input type='hidden' name='detail[" . $nomor . "][id]' value='" . $valx['id'] . "'></td>";
                            echo "<td align='left'>
                                <textarea class='form-control input-md nm_barang_" . $nomor . "' name='detail[" . $nomor . "][nm_barang]' " . $disabled3 . ">" . strtoupper($valx['nm_barang']) . "</textarea>
                            </td>";
                            echo "<td align='left'>
                                <textarea class='form-control input-md spec_" . $nomor . "' name='detail[" . $nomor . "][spec]' " . $disabled3 . ">" . strtoupper($valx['spec']) . "</textarea>
                            </td>";
                            echo "<td align='left'><input type='text' " . $disabled2 . " id='qty_" . $nomor . "' name='detail[" . $nomor . "][qty]' class='form-control input-md text-right autoNumeric2 sum_tot qty_" . $nomor . "' value='" . $valx['qty'] . "'></td>";
                            echo "<td align='left'>
                                <select name='detail[" . $nomor . "][satuan]' class='form-control wajib satuan_" . $nomor . "' " . $disabled2 . " required>";
                            echo "<option value=''>Pilih</option>";
                            foreach ($satuan as $key => $value) {
                                $selected = ($value['id'] == $valx['satuan']) ? 'selected' : '';
                                echo "<option value='" . $value['id'] . "' " . $selected . ">" . $value['code'] . "</option>";
                            }
                            echo "</select></td>";
                            echo "<td align='left'><input type='text' " . $disabled2 . " id='harga_" . $nomor . "' name='detail[" . $nomor . "][harga]' class='form-control input-md text-right maskM sum_tot harga_" . $nomor . "' value='" . $valx['harga'] . "' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
                            echo "<td align='left'><input type='text' " . $disabled2 . " id='total_harga_" . $nomor . "' name='detail[" . $nomor . "][total_harga]' class='form-control input-md text-right maskM jumlah_all total_harga_" . $nomor . "' value='" . ($valx['qty'] * $valx['harga']) . "' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' readonly></td>";
                            echo "<td align='left'><input type='text' " . $disabled3 . " name='detail[" . $nomor . "][tanggal]' class='form-control input-md text-center datepicker tgl_dibutuhkan tanggal_" . $nomor . "' readonly value='" . strtoupper($valx['tanggal']) . "'></td>";
                            echo "<td align='left'>
                                <textarea class='form-control input-md keterangan_" . $nomor . "' name='detail[" . $nomor . "][keterangan]' " . $disabled3 . ">" . strtoupper($valx['keterangan']) . "</textarea>
                            </td>";
                            if (empty($approve)) {
                                echo "<td align='center'><button type='button' class='btn btn-sm btn-warning edit_detail edit_detail_" . $nomor . "' data-id='" . $valx['id'] . "' data-nomor='" . $nomor . "' style='margin-right:0.5em;'><i class='fa fa-pencil'></i></button><button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button></td>";
                            }
                            echo "</tr>";
                        }
                    }
                    if (empty($approve)) {
                    ?>
                        <tr id="add_<?= $nomor; ?>">
                            <td align="center"></td>
                            <td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" class="btn btn-sm btn-warning addPart" title="Add Barang"><i class="fa fa-plus"></i>&nbsp;&nbsp;Add Barang</button></td>
                            <td align="center" colspan="8"></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Section: Informasi Bank -->
            <div class="box-bank-info" style="border: 1px solid #dce2e6; border-radius: 4px; overflow: hidden; margin-top: 20px; margin-bottom: 20px; background: #fff;">
                <div style="background: #e9ecf0; padding: 9px 15px; font-weight: 700; font-size: 11px; color: #333; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #dce2e6;">
                    INFORMASI BANK
                </div>
                <div style="padding: 15px 15px 5px 15px;">
                    <div class="form-group row">
                        <label class="label-control col-sm-2" style="font-weight: 600;">Bank <span class="text-red">*</span></label>
                        <div class="col-sm-5">
                            <input type="text" name="bank_name" id="bank_name" class="form-control input-md" placeholder="Nama Bank (e.g. BCA, Mandiri)" value="<?= $bank_name; ?>" <?= $disabled3; ?>>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="label-control col-sm-2" style="font-weight: 600;">No Rekening <span class="text-red">*</span></label>
                        <div class="col-sm-5">
                            <input type="text" name="bank_account_no" id="bank_account_no" class="form-control input-md" placeholder="No. Rekening" value="<?= $bank_account_no; ?>" <?= $disabled3; ?>>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="label-control col-sm-2" style="font-weight: 600;">Nama Rekening <span class="text-red">*</span></label>
                        <div class="col-sm-5">
                            <input type="text" name="bank_account_name" id="bank_account_name" class="form-control input-md" placeholder="Nama Pemilik Rekening" value="<?= $bank_account_name; ?>" <?= $disabled3; ?>>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Footer -->
            <div class="action-footer">
                <?php if ($approve <> 'view') { ?>
                    <button type="button" class="btn btn-md btn-success" id="save">
                        <i class="fa fa-save"></i>&nbsp; Save
                    </button>
                <?php } ?>
                <button type="button" class="btn btn-md btn-danger" id="back">
                    <i class="fa fa-arrow-left"></i>&nbsp; Back
                </button>
            </div>

            <?php
            if (!empty($header)) :
                if (strpos($header[0]->document, 'pdf', 0) > 1) :
                    echo '<div class="col-md-12" style="margin-top:15px;">
                    <iframe src="' . base_url('assets/pr/' . $header[0]->document) . '#toolbar=0&navpanes=0" title="PDF" style="width:600px; height:500px;" frameborder="0"></iframe>
                    <a href="' . base_url('assets/pr/' . $header[0]->document) . '" class="btn btn-sm btn-primary" target="_blank">Check PDF</a>
                    <br />' . $header[0]->no_pengajuan . '</div>';
                else :
                    if (file_exists('assets/pr/' . $header[0]->document)) {
                        echo '<div class="col-md-12" style="margin-top:15px;"><a href="' . base_url('assets/pr/' . $header[0]->document) . '" target="_blank"><img src="' . base_url('assets/pr/' . $header[0]->document) . '" class="img-responsive"></a><br />' . $header[0]->no_pengajuan . '</div>';
                    }
                endif;
            endif;
            ?>

        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
</form>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('.maskM').autoNumeric();
        $('.autoNumeric2').autoNumeric('init', {
            mDec: '2',
            aPad: false
        });
        $('.chosen_select').chosen();
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            //minDate: 0
        });
        $('.tnd_reason').hide();
    });

    $('#no_so').on('change', function(evt, params) {
        var data = $("select#no_so").find(":selected").data("project");
        $("#project_name").val(data);
    });

    $(document).on('change', '#sts_app', function(e) {
        var sts = $(this).val();
        if (sts == 'D') {
            $('.tnd_reason').show();
        } else {
            $('.tnd_reason').hide();
        }
    });

    $(document).on('click', '#back', function(e) {
        var app = $("#approve").val();
        var tingkat_approval = $('#tingkat_approval').val();
        var tanda = "";
        if (app == 'approve') {
            tanda = 'app_pr_dept_finance';
        }
        window.location.href = base_url + active_controller + tanda;
    });

    $(document).on('click', '.addPart', function() {
        var get_id = $(this).parent().parent().attr('id');
        var split_id = get_id.split('_');
        var id = parseInt(split_id[1]) + 1;
        var id_bef = split_id[1];

        $.ajax({
            url: base_url + active_controller + '/get_add/' + id,
            cache: false,
            type: "POST",
            dataType: "json",
            success: function(data) {
                $("#add_" + id_bef).before(data.header);
                $("#add_" + id_bef).remove();
                $('.chosen_select').chosen({
                    width: '100%'
                });
                $('.maskM').autoNumeric();
                $('.datepicker').datepicker({
                    dateFormat: 'yy-mm-dd',
                    //minDate: 0
                });
                $('.chosen_select').chosen();
                swal.close();
            },
            error: function() {
                Swal.fire({
                    title: "Error Message !",
                    text: 'Connection Time Out. Please try again..',
                    icon: "warning",
                    timer: 3000,
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
            }
        });
    });

    // delete part
    $(document).on('click', '.delPart', function() {
        var get_id = $(this).parent().parent().attr('class');
        $("." + get_id).remove();
    });

    $(document).on('keyup', '.sum_tot', function() {
        var id = $(this).attr('id');
        var det_id = id.split('_');
        var a = det_id[1];
        sum_total(a);
    });

    // SAVE
    $(document).on('click', '#save', function(e) {
        e.preventDefault();
        $('#save').prop('disabled', true);

        var tingkat_approval = $('#tingkat_approval').val();
        var id_dept = $('#id_dept').val();
        var coa = $('#coa').val();
        var sts_app = $('#sts_app').val();

        if (id_dept == '0') {
            Swal.fire({
                title: "Error Message!",
                text: 'Department name empty, select first ...',
                icon: "warning"
            });
            $('#save').prop('disabled', false);
            return false;
        }

        var app = $("#approve").val();
        var tanda = "";
        if (app == 'approve') {
            if (sts_app == '0') {
                Swal.fire({
                    title: "Error Message!",
                    text: 'Status Approve empty, select first ...',
                    icon: "warning",
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    timer: 2000
                });
                $('#save').prop('disabled', false);
                return false;
            }
        }

        let wajib;
        let FALIDASIwajib = true;
        $(".wajib").each(function() {
            satuan = $(this).val();
            if (satuan == '' || satuan == '0') {
                FALIDASIwajib = false;
                return false;
            }
        });
        if (FALIDASIwajib === false) {
            Swal.fire({
                title: "Error Message!",
                text: 'Satuan wajib diisi !',
                icon: "warning",
                showCancelButton: false,
                showConfirmButton: false,
                allowOutsideClick: false,
                timer: 2000
            });
            $('#save').prop('disabled', false);
            return false;
        }

        let tgl_butuh;
        let FALIDASI = true;
        $(".tgl_dibutuhkan").each(function() {
            tgl_butuh = $(this).val();
            if (tgl_butuh == '' || tgl_butuh == '0000-00-00') {
                FALIDASI = false;
                return false;
            }
        });
        if (FALIDASI === false) {
            Swal.fire({
                title: "Error Message!",
                text: 'Tgl dibutuhkan wajib diisi !',
                icon: "warning",
                showCancelButton: false,
                showConfirmButton: false,
                allowOutsideClick: false,
                timer: 2000
            });
            $('#save').prop('disabled', false);
            return false;
        }

        $('#save').prop('disabled', true);

        Swal.fire({
            title: "Are you sure?",
            text: "Save this data ?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Yes, Process it!",
            cancelButtonText: "No, cancel process!",
            closeOnConfirm: true,
            closeOnCancel: false
        }).then((isConfirm) => {
            if (isConfirm.isConfirmed) {
                var formData = new FormData($('#form_ct')[0]);
                var baseurl = base_url + active_controller + '/add_finance';
                $.ajax({
                    url: baseurl,
                    type: "POST",
                    data: formData,
                    cache: false,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data.status == 1) {
                            Swal.fire({
                                title: "Save Success!",
                                text: data.pesan,
                                icon: "success",
                                timer: 3000,
                                showCancelButton: false,
                                showConfirmButton: false,
                                allowOutsideClick: false
                            }).then((next) => {
                                var return_link = 'app_pr_dept_finance';
                                window.location.href = base_url + active_controller + return_link;
                            });
                        } else if (data.status == 0) {
                            Swal.fire({
                                title: "Save Failed!",
                                text: data.pesan,
                                icon: "warning",
                                timer: 3000,
                                showCancelButton: false,
                                showConfirmButton: false,
                                allowOutsideClick: false
                            }).then((next) => {
                                $('#save').prop('disabled', false);
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: "Error Message !",
                            text: 'An Error Occured During Process. Please try again..',
                            icon: "warning",
                            timer: 3000,
                            showCancelButton: false,
                            showConfirmButton: false,
                            allowOutsideClick: false
                        }).then(next => {
                            $('#save').prop('disabled', false);
                        });
                    }
                });
            } else {
                Swal.fire("Cancelled", "Data can be process again :)", "error");
                $('#save').prop('disabled', false);
                return false;
            }
        });
    });

    $(document).on('click', '.edit_detail', function() {
        var id = $(this).data('id');
        var nomor = $(this).data('nomor');

        var nm_barang = $('.nm_barang_' + nomor).val();
        var spec = $('.spec_' + nomor).val();
        var qty = $('.qty_' + nomor).val();
        var satuan = $('.satuan_' + nomor).val();
        var harga = $('.harga_' + nomor).val();
        var total_harga = $('.total_harga_' + nomor).val();
        var tanggal = $('.tanggal_' + nomor).val();
        var keterangan = $('.keterangan_' + nomor).val();

        if (qty == '' || qty == null) {
            qty = 0;
        } else {
            qty = qty.split(',').join();
            qty = parseFloat(qty);
        }
        if (harga == '' || harga == null) {
            harga = 0;
        } else {
            harga = harga.split(',').join();
            harga = parseFloat(harga);
        }
        if (total_harga == '' || total_harga == null) {
            total_harga = 0;
        } else {
            total_harga = total_harga.split(',').join();
            total_harga = parseFloat(total_harga);
        }

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + '/edit_detail',
            data: {
                'id': id,
                'nm_barang': nm_barang,
                'spec': spec,
                'qty': qty,
                'satuan': satuan,
                'harga': harga,
                'total_harga': total_harga,
                'tanggal': tanggal,
                'keterangan': keterangan
            },
            cache: false,
            dataType: 'json',
            beforeSend: function(result) {
                $('.edit_detail_' + nomor).html('<i class="fa fa-spin fa-spinner"></i>');
                $('.edit_detail_' + nomor).prop('disabled', true);
            },
            success: function(result) {
                if (result.status == 1) {
                    Swal.fire({
                        title: 'Success !',
                        text: 'Success, item data has been updated !',
                        icon: 'success',
                        timer: 2000,
                        showCancelButton: false,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    }).then((next) => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Failed !',
                        text: 'Failed, item data has not been updated !',
                        icon: 'error',
                        timer: 2000,
                        showCancelButton: false,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    }).then((next) => {
                        location.reload();
                    });
                }
            },
            error: function(result) {
                swal({
                    title: 'Failed !',
                    text: 'Failed, item data has not been updated !',
                    type: 'error',
                    timer: 2000,
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowOutsideClick: false
                }).then((next) => {
                    location.reload();
                });
            }
        });
    });

    function sum_total(a) {
        var qty = getNum($('#qty_' + a).val().split(",").join(""));
        var harga = getNum($('#harga_' + a).val().split(",").join(""));
        var total = qty * harga;
        $('#total_harga_' + a).val(number_format(total));

        var SUM = 0;
        $(".jumlah_all").each(function() {
            SUM += Number(getNum($(this).val().split(",").join("")));
        });
        $('#budget').val(number_format(SUM));
    }

    function number_format(number, decimals, dec_point, thousands_sep) {
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }
</script>