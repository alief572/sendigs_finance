<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/select2/select2.css">
<script src="<?= base_url() ?>assets/plugins/select2/select2.full.min.js"></script>
<?php
$gambar = '';
$dept = '';
$app = '';
$bank_id = '';
$accnumber = '';
$accname = '';
if (!isset($data->departement)) {
    $data_user = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
    $data_employee = $this->db->get_where('employee', ['id' => $data_user->employee_id])->row();
    $dept = $data_user->department_id;
    if (!empty($data_employee)) {
        $bank_id = $data_employee->bank_id;
        $accnumber = $data_employee->accnumber;
        $accname = $data_employee->accname;
        $data_head = $this->db->get_where('divisions_head', ['id' => $data_employee->division_head])->row();
        $app = $data_head->employee_id;
    }
}
?>
<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data')); ?>
<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
<input type="hidden" id="departement" name="departement" value="<?php echo $dept; ?>">
<input type="hidden" id="nama" name="nama" value="<?php echo (isset($data->nama) ? $data->nama : $this->auth->user_name()); ?>">
<input type="hidden" id="approval" name="approval" value="<?php echo (isset($data->approval) ? $data->approval : $app); ?>">
<input type="hidden" name="" class="stsview" value="<?= (isset($stsview)) ? $stsview : null ?>">
<style>
    @media screen and (max-width: 520px) {
        table {
            width: 100%;
        }

        thead th.column-primary {
            width: 100%;
        }

        thead th:not(.column-primary) {
            display: none;
        }

        th[scope="row"] {
            vertical-align: top;
        }

        td {
            display: block;
            width: auto;
            text-align: right;
        }

        thead th::before {
            text-transform: uppercase;
            font-weight: bold;
            content: attr(data-header);
        }

        thead th:first-child span {
            display: none;
        }

        td::before {
            float: left;
            text-transform: uppercase;
            font-weight: bold;
            content: attr(data-header);
        }
    }
</style>
<div class="tab-content">
    <div class="tab-pane active">
        <div class="box box-primary">
            <div class="box-body">
                <div class="form-group ">
                    <label class="col-sm-2 col-md-2 control-label">No Dokumen</label>
                    <div class="col-sm-4 col-md-4">
                        <input type="text" class="form-control" id="no_doc" name="no_doc" value="<?php echo (isset($data->no_doc) ? $data->no_doc : ""); ?>" placeholder="Automatic" readonly>
                    </div>
                    <label class="col-sm-2 col-md-2 control-label">Tanggal <b class="text-red">*</b></label>
                    <div class="col-sm-4 col-md-4">
                        <input type="text" class="form-control tanggal" id="tgl_doc" name="tgl_doc" value="<?php echo (isset($data->tgl_doc) ? $data->tgl_doc : date("Y-m-d")); ?>" placeholder="Tanggal Dokumen" required>
                    </div>
                </div>
                <div class="form-group ">
                    <label class="col-sm-2 col-md-2 control-label">Keterangan <b class="text-red">*</b></label>
                    <div class="col-sm-4 col-md-6">
                        <input type="text" class="form-control" id="informasi" name="informasi" value="<?php echo (isset($data->informasi) ? $data->informasi : ""); ?>" placeholder="Keterangan" required>
                    </div>
                    <div class="col-md-4">
                        <?php
                        if (isset($data->st_reject)) {
                            if ($data->st_reject != '') {
                                echo '
							  <div class="alert alert-danger alert-dismissible">
								<h4><i class="icon fa fa-ban"></i> Alasan Penolakan!</h4>
								' . $data->st_reject . '
							  </div>';
                            }
                        }
                        ?>
                    </div>
                </div>
                <div>
                    <h4>Transfer ke</h4>
                    <div class="form-group ">
                        <label class="col-md-1 control-label">Bank</label>
                        <div class="col-md-2">
                            <input type="text" class="form-control" id="bank_id" name="bank_id" value="<?php echo (isset($data->bank_id) ? $data->bank_id : $bank_id); ?>" placeholder="Bank">
                        </div>
                        <label class="col-md-2 control-label">Nomor Rekening</label>
                        <div class="col-md-2">
                            <input type="text" class="form-control" id="accnumber" name="accnumber" value="<?php echo (isset($data->accnumber) ? $data->accnumber : $accnumber); ?>" placeholder="Nomor Rekening">
                        </div>
                        <label class="col-md-2 control-label">Nama Rekening</label>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="accname" name="accname" value="<?php echo (isset($data->accname) ? $data->accname : $accname); ?>" placeholder="Nama Pemilik Rekening">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" width="100%">
                        <thead>
                            <tr>
                                <th width="5" scope="col" class="column-primary">#</th>
                                <th scope="col" width="250">Jenis dan<br /> Tanggal</th>
                                <th scope="col" width="250">Barang/Jasa <br />&Keterangan</th>
                                <th scope="col" width=150 nowrap>Jumlah</th>
                                <th scope="col" width=200 nowrap>Harga Satuan</th>
                                <th scope="col" width="200">Expense</th>
                                <th scope="col" width="200">Kasbon</th>
                                <th scope="col" width="50">Bon Bukti</th>
                                <th scope="col" class="column-primary">
                                    <div class="pull-right">
                                        <a class="btn btn-info btn-xs stsview" href="javascript:void(0)" title="Kasbon" onclick="add_kasbon()" id="add-kasbon"><i class="fa fa-user"></i> Kasbon</a><br />
                                        <a class="btn btn-success btn-xs stsview" href="javascript:void(0)" title="Tambah" onclick="add_detail()" id="add-material"><i class="fa fa-plus"></i> Tambah</a>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="detail_body">
                            <?php $total = 0;
                            $idd = 1;
                            $grand_total = 0;
                            $total_expense = 0;
                            $total_kasbon = 0;
                            if (!empty($data_detail)) {
                                foreach ($data_detail as $record) {
                                    $tekskasbon = "";
                                    if ($record->id_kasbon != '') $tekskasbon = ' readonly'; ?>
                                    <tr id='tr1_<?= $idd ?>' class='delAll <?= ($record->id_kasbon != '' ? 'kasbonrow' : '') ?>'>
                                        <td data-header="#">
                                            <input type='hidden' name='id_kasbon[]' id='id_kasbon_<?= $idd ?>' value='<?= $record->id_kasbon; ?>'>
                                            <input type="hidden" name="filename[]" id="filename_<?= $idd ?>" value="<?= $record->doc_file; ?>">
                                            <input type="hidden" name="detail_id[]" id="raw_id_<?= $idd ?>" value="<?= $idd; ?>" class="dtlloop">
                                            <input type="hidden" name="id_detail[]" id="id_detail_<?= $idd ?>" value="<?= $record->id; ?>" class="dtlloop"><?= $idd ?>
                                        </td>
                                        <td data-header="Jenis & Tanggal">
                                            <?php
                                            if ($tekskasbon == '') {
                                                echo form_dropdown('coa[]', $data_budget, (isset($record->coa) ? $record->coa : ''), array('id' => 'coa' . $idd, 'required' => 'required', 'class' => 'form-control select2', 'style' => 'width:300px'));
                                            } else {
                                                echo '<input type="hidden" name="coa[]" id="coa' . $idd . '" value="' . $record->coa . '">';
                                            }
                                            ?>
                                            <input type="text" class="form-control tanggal input-sm" name="tanggal[]" id="tanggal<?= $idd; ?>" value="<?= $record->tanggal; ?>" <?= $tekskasbon ?>>
                                        </td>
                                        <td data-header="Barang / Jasa & Keterangan"><input type="text" class="form-control input-sm" name="deskripsi[]" id="deskripsi_<?= $idd; ?>" value="<?= $record->deskripsi; ?>" <?= $tekskasbon ?> style="width:100px;">
                                            <input type="text" class="form-control input-sm" name="keterangan[]" id="keterangan_<?= $idd; ?>" value="<?= $record->keterangan; ?>">
                                        </td>
                                        <td data-header="Qty"><input type="text" class="form-control divide input-sm" name="qty[]" id="qty_<?= $idd; ?>" value="<?= $record->qty; ?>" onblur="cektotal(<?= $idd; ?>)" <?= $tekskasbon ?> size="15" style="width:60px;"></td>
                                        <td data-header="Harga Satuan"><input type="text" class="form-control divide input-sm" name="harga[]" id="harga_<?= $idd; ?>" value="<?= $record->harga; ?>" onblur="cektotal(<?= $idd; ?>)" <?= $tekskasbon ?> style="width:100px;"></td>
                                        <td data-header="Expense"><input type="text" class="form-control divide subtotal input-sm" name="expense[]" id="expense_<?= $idd; ?>" value="<?= ($record->expense); ?>" tabindex="-1" readonly style="width:100px;"></td>
                                        <td data-header="Kasbon"><input type="text" class="form-control divide subkasbon input-sm" name="kasbon[]" id="kasbon_<?= $idd; ?>" value="<?= ($record->kasbon); ?>" tabindex="-1" readonly style="width:100px;"></td>
                                        <td data-header="Bon Bukti" width="50">
                                            <div class="upload-btn-wrapper">
                                                <!--<label for="doc_file<?= $idd ?>" <?= ($tekskasbon != '' ? 'class="hidden"' : '') ?> >Upload file</label>-->
                                                <input type="file" name="doc_file_<?= $idd ?>" id="doc_file_<?= $idd ?>" />
                                            </div>
                                            <span class="pull-right"><?= ($record->doc_file != '' ? '<a href="' . base_url('assets/expense/' . $record->doc_file) . '" download target="_blank"><i class="fa fa-download"></i></a>' : '') ?></span>
                                        </td>
                                        <th scope="row" align='center'><button type='button' class='btn btn-danger btn-xs stsview' data-toggle='tooltip' onClick='delDetail(<?= $idd ?>)' title='Hapus data'><i class='fa fa-close'></i> Hapus</button></th>
                                    </tr>
                            <?php
                                    if ($record->doc_file != '') {
                                        if (strpos($record->doc_file, 'pdf', 0) > 1) {
                                            $gambar .= '<div class="col-md-12">
								<iframe src="' . base_url('assets/expense/' . $record->doc_file) . '#toolbar=0&navpanes=0" title="PDF" style="width:600px; height:500px;" frameborder="0">
										 <a href="' . base_url('assets/expense/' . $record->doc_file) . '">Download PDF</a>
								</iframe>
								<br />' . $record->no_doc . '</div>';
                                        } else {
                                            $gambar .= '<div class="col-md-4"><a href="' . base_url('assets/expense/' . $record->doc_file) . '" target="_blank"><img src="' . base_url('assets/expense/' . $record->doc_file) . '" class="img-responsive"></a><br />' . $record->no_doc . '</div>';
                                        }
                                    }
                                    $total_expense = ($total_expense + ($record->expense));
                                    $total_kasbon = ($total_kasbon + ($record->kasbon));
                                    $idd++;
                                }
                                $grand_total = ($grand_total + ($total_expense - $total_kasbon));
                            } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" align=right>TOTAL</td>
                                <td><input type="text" class="form-control divide input-sm" id="total_expense" name="total_expense" value="<?= $total_expense ?>" placeholder="Total Expense" tabindex="-1" readonly style='width:100px;'></td>
                                <td><input type="text" class="form-control divide input-sm" id="total_kasbon" name="total_kasbon" value="<?= $total_kasbon ?>" placeholder="Total Kasbon" tabindex="-1" readonly style='width:100px;'></td>
                                <td align=right colspan=2>
                                    <div class="row">
                                        <div class="col-md-2">Saldo</div>
                                        <div class="col-md-10"><input type="text" class="form-control divide input-sm" id="grand_total" name="grand_total" value="<?= $grand_total ?>" placeholder="Grand Total" tabindex="-1" readonly></div>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="col-md-6">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Pengembalian Kasbon</th>
                                    <th>Penggantian Kasbon</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="radio" name="pengembalian" id="" value="1" <?= (isset($data->tipe_pengembalian) && $data->tipe_pengembalian == 1) ? 'checked' : null ?>> Cash
                                    </td>
                                    <td>
                                        <input type="radio" name="penggantian" id="" value="1" <?= (isset($data->tipe_penggantian) && $data->tipe_penggantian == 1) ? 'checked' : null ?>> Cash
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="radio" name="pengembalian" id="" value="2" <?= (isset($data->tipe_pengembalian) && $data->tipe_pengembalian == 2) ? 'checked' : null ?>> Transfer
                                    </td>
                                    <td>
                                        <input type="radio" name="penggantian" id="" value="2" <?= (isset($data->tipe_penggantian) && $data->tipe_penggantian == 2) ? 'checked' : null ?>> Transfer
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php
                    if (isset($data_exp_kasbon)) {
                        if (!empty($data_exp_kasbon)) {
                            foreach ($data_exp_kasbon as $exp_kasbon) :
                                $no_kasbon_detail = 1;
                                $this->db->select('a.*, IF(b.code IS NULL, "Pcs", b.code) AS satuan');
                                $this->db->from('tr_pr_detail_kasbon a');
                                $this->db->join('ms_satuan b', 'b.id = a.unit', 'left');
                                $this->db->where('a.id_kasbon', $exp_kasbon['id_kasbon']);
                                $get_pr_kasbon_detail = $this->db->get()->result_array();

                                if (!empty($get_pr_kasbon_detail)) {
                                    echo '<h4>No PR: ' . $get_pr_kasbon_detail[0]['no_pr'] . '</h4>';
                                    echo '<table class="table table-bordered">';
                                    echo '<thead>';
                                    echo '<tr>';
                                    echo '<th class="text-center">No.</th>';
                                    echo '<th class="text-center">Material Name</th>';
                                    echo '<th class="text-center">Qty</th>';
                                    echo '<th class="text-center">Unit</th>';
                                    echo '<th class="text-center">Price</th>';
                                    echo '<th class="text-center">Total Price</th>';
                                    echo '</tr>';
                                    echo '</thead>';
                                    echo '<tbody>';

                                    foreach ($get_pr_kasbon_detail as $kasbon_detail) :
                                        echo '<tr>';
                                        echo '<td class="text-center">' . $no_kasbon_detail . '</td>';
                                        echo '<td class="text-center">' . $kasbon_detail['nm_material'] . '</td>';
                                        echo '<td class="text-center">' . number_format($kasbon_detail['qty']) . '</td>';
                                        echo '<td class="text-center">' . $kasbon_detail['satuan'] . '</td>';
                                        echo '<td class="text-right">' . number_format($kasbon_detail['harga']) . '</td>';
                                        echo '<td class="text-right">' . number_format($kasbon_detail['total_harga']) . '</td>';
                                        echo '</tr>';

                                        $no_kasbon_detail++;
                                    endforeach;

                                    echo '</tbody>';
                                    echo '</table>';
                                }
                            endforeach;
                        }
                    }
                    ?>
                </div>
                <div class="box-footer">
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <?php
                            $urlback = '';
                            if (isset($data)) {
                                if ($data->status == 0) {
                                    if ($stsview == 'approval') {
                                        $urlback = 'list_expense_approval';
                                        echo '<a class="btn btn-warning btn-sm" onclick="data_approve()"><i class="fa fa-check-square-o">&nbsp;</i>Approve</a>';
                                        echo ' <a class="btn btn-danger btn-sm" onclick="data_reject()"><i class="fa fa-ban">&nbsp;</i> Reject</a>';
                                    }
                                }
                            }

                            ?>
                            <button type="submit" name="save" class="btn btn-success btn-sm stsview" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>
                            <a class="btn btn-default btn-sm" onclick="window.location.reload();return false;"><i class="fa fa-reply">&nbsp;</i>Batal</a>
                        </div>
                    </div>
                    <div class="row">
                        <?= $gambar ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>
    <?php
    $datacombocoa = "";
    foreach ($data_budget as $keys => $val) {
        $datacombocoa .= "<option value='" . $keys . "'>" . $val . "</option>";
    }
    ?>
    <script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
    <script type="text/javascript">
        var url_save = siteurl + 'expense/save/';
        var url_approve = siteurl + 'expense/approve/';
        var nomor = parseInt("<?= $idd ?>");
        $('.divide').divide();
        $('.select2').select2();
        $('#frm_data').on('submit', function(e) {
            e.preventDefault();
            var errors = "";
            var lops = 0;
            $('.dtlloop').each(function() {
                lops++;
                var iddtl = $(this).val();
                if ($("#filename_" + iddtl).val() == "") {
                    if ($('#doc_file_' + iddtl).get(0).files.length === 0) {
                        errors = "Bon Bukti harus diupload";
                    }
                }
            });
            if (lops == 0) errors = "Detail harus diisi";
            if ($("#informasi").val() == "") errors = "Keterangan tidak boleh kosong";
            if ($("#coa").val() == "0") errors = "Jenis Expense tidak boleh kosong";
            if ($("#tgl_doc").val() == "") errors = "Tanggal Transaksi tidak boleh kosong";
            if (errors == "") {

                swal({
                        title: "Anda Yakin?",
                        text: "Data Akan Disimpan!",
                        type: "info",
                        showCancelButton: true,
                        confirmButtonText: "Ya, simpan!",
                        cancelButtonText: "Tidak!",
                        closeOnConfirm: false,
                        closeOnCancel: true
                    },
                    function(isConfirm) {
                        if (isConfirm) {
                            var formdata = new FormData($('#frm_data')[0]);
                            $.ajax({
                                url: url_save,
                                dataType: "json",
                                type: 'POST',
                                data: formdata,
                                processData: false,
                                contentType: false,
                                success: function(msg) {
                                    if (msg['save'] == '1') {
                                        swal({
                                            title: "Sukses!",
                                            text: "Data Berhasil Di Simpan",
                                            type: "success",
                                            timer: 1500,
                                            showConfirmButton: false
                                        });
                                        window.location.reload();
                                    } else {
                                        swal({
                                            title: "Gagal!",
                                            text: "Data Gagal Di Simpan",
                                            type: "error",
                                            timer: 1500,
                                            showConfirmButton: false
                                        });
                                    };
                                    console.log(msg);
                                },
                                error: function(msg) {
                                    swal({
                                        title: "Gagal!",
                                        text: "Ajax Data Gagal Di Proses",
                                        type: "error",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    console.log(msg);
                                }
                            });
                        }
                    });

                //			data_save();
            } else {
                swal(errors);
                return false;
            }
        });

        var stsview = $('.stsview').val();
        if (stsview == 'view' || stsview == 'approval') {
            $(".stsview").addClass("hidden");
            $("#frm_data :input").prop("disabled", true);
        }


        $(function() {
            $(".tanggal").datepicker({
                todayHighlight: true,
                format: "yyyy-mm-dd",
                showInputs: true,
                autoclose: true
            });
        });

        function cektotal(id) {
            var sqty = $("#qty_" + id).val();
            var pref = $("#harga_" + id).val();
            var subtotal = (parseFloat(sqty) * parseFloat(pref));
            $("#expense_" + id).val(subtotal);
            var sum = 0;
            $('.subtotal').each(function() {
                sum += Number($(this).val());
            });
            $("#total_expense").val(sum);
            var sumkasbon = 0;
            $('.subkasbon').each(function() {
                sumkasbon += Number($(this).val());
            });
            $("#total_kasbon").val(sumkasbon);
            $("#grand_total").val(Number(sum) - Number(sumkasbon));
        }

        function add_kasbon() {
            $('.kasbonrow').remove();
            var nama = $("#nama").val();
            var departement = $("#departement").val();
            $.ajax({
                url: siteurl + 'expense/get_kasbon/' + nama + '/' + departement + '/<?= (isset($data->no_doc) ? $data->no_doc : ""); ?>',
                cache: false,
                type: "POST",
                dataType: "json",
                success: function(data) {
                    var i;
                    for (i = 0; i < data.length; i++) {
                        var Rows = "<tr id='tr1_" + nomor + "' class='delAll kasbonrow'>";
                        Rows += "<td data-header='#'><input type='hidden' name='id_kasbon[]' id='id_kasbon_" + nomor + "' value='" + data[i].no_doc + "'>";
                        Rows += "<input type='hidden' name='detail_id[]' id='raw_id_" + nomor + "' value='" + nomor + "'>";
                        Rows += "<input type='hidden' name='id_detail[]' id='id_detail_" + nomor + "' value='" + data[i].id + "'>";
                        Rows += "<input type='hidden' name='filename[]' id='filename_" + nomor + "' value='" + data[i].doc_file + "'></td>";
                        Rows += "<td data-header='Tanggal'>";
                        Rows += "<input type='text' class='form-control tanggal input-sm' name='tanggal[]' id='tanggal_" + nomor + "' tabindex='-1' readonly value='" + data[i].tgl_doc + "' />";
                        Rows += "<input type='hidden' name='coa[]' id='coa_" + nomor + "' value='" + data[i].coa + "' />";
                        Rows += "</td>";
                        Rows += "<td data-header='Barang / Jasa & Keteranga'>";
                        Rows += "<input type='text' class='form-control input-sm' name='deskripsi[]' id='deskripsi_" + nomor + "' value='" + data[i].keperluan + "' tabindex='-1' readonly />";
                        Rows += "<input type='text' class='form-control input-sm' name='keterangan[]' id='keterangan_" + nomor + "' value='' />";
                        Rows += "<input type='hidden' class='form-control input-sm' name='id_expense_detail[]' id='id_expense_detail_" + nomor + "' value='" + data[i].id_expense_detail + "' />";
                        Rows += "</td>";
                        Rows += "<td data-header='Qty'>";
                        Rows += "<input type='text' class='form-control divide input-sm' name='qty[]' value='1' id='qty_" + nomor + "' tabindex='-1' readonly />";
                        Rows += "</td>";
                        Rows += "<td data-header='Harga Satuan'>";
                        Rows += "<input type='text' class='form-control divide input-sm' name='harga[]' value='0' id='harga_" + nomor + "' tabindex='-1' readonly style='width:100px;' />";
                        Rows += "</td>";
                        Rows += "<td data-header='Expense'>";
                        Rows += "<input type='text' class='form-control divide input-sm subtotal hidden' name='expense[]' value='0' id='expense_" + nomor + "' tabindex='-1' readonly />";
                        Rows += "</td>";
                        Rows += "<td data-header='Kasbon'>";
                        Rows += "<input type='text' class='form-control divide input-sm subkasbon' name='kasbon[]' value='" + data[i].jumlah_kasbon + "' id='kasbon_" + nomor + "' tabindex='-1' readonly style='width:100px;' />";
                        Rows += "</td>";
                        Rows += "<td data-header='Bon Bukti'>";
                        Rows += "<input type='file'  name='doc_file_" + nomor + "' id='doc_file_" + nomor + "' class='hidden' />";
                        Rows += "<span class='pull-right'>";
                        if (data[i].doc_file != '') {
                            Rows += "<a href='<?= base_url('assets/expense/') ?>" + data[i].doc_file + "' download target='_blank'><i class='fa fa-download'></i></a></span>";
                        }
                        Rows += "</td>";
                        Rows += "<td align='center'>";
                        Rows += "<button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail(" + nomor + ")' title='Hapus data'><i class='fa fa-close'></i> Hapus</button>";
                        Rows += "</td>";
                        Rows += "</tr>";
                        nomor++;
                        $('#detail_body').append(Rows);
                        cektotal(nomor - 1);
                    }
                    $(".divide").divide();
                },
                error: function() {
                    swal({
                        title: "Error Message !",
                        text: 'Connection Time Out. Please try again..',
                        type: "warning",
                        timer: 3000,
                        showCancelButton: false,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                }
            });
        }

        function add_detail() {
            var datacombocoa = "<?= $datacombocoa ?>";
            var Rows = "<tr id='tr1_" + nomor + "' class='delAll'>";
            Rows += "<td data-header='#'><input type='hidden' name='id_kasbon[]' id='id_kasbon_" + nomor + "' value=''>";
            Rows += "<input type='hidden' name='detail_id[]' id='raw_id_" + nomor + "' value='" + nomor + "' class='dtlloop'>";
            Rows += "<input type='hidden' name='id_detail[]' id='id_detail_" + nomor + "' value='" + nomor + "' class='dtlloop'>";
            Rows += "<input type='hidden' name='filename[]' id='filename_" + nomor + "' value=''></td>";
            Rows += "<td data-header='Jenis & Tanggal'>";
            Rows += "<select name='coa[]' id='coa_" + nomor + "' required='required' class='form-control select2' style='width:300px'><?= $datacombocoa ?></select>";
            Rows += "<input type='text' class='form-control tanggal input-sm' placeholder='Tanggal' name='tanggal[]' id='tanggal_" + nomor + "' />";
            Rows += "</td>";
            Rows += "<td data-header='Barang / Jasa & Keterangan'>";
            Rows += "<input type='text' class='form-control input-sm' placeholder='Barang/Jasa' name='deskripsi[]' id='deskripsi_" + nomor + "' style='width:100px;' />";
            Rows += "<input type='text' class='form-control input-sm' placeholder='Keterangan' name='keterangan[]' id='keterangan_" + nomor + "' />";
            Rows += "</td>";
            Rows += "<td data-header='Qty'>";
            Rows += "<input type='text' class='form-control divide input-sm' name='qty[]' value='0' id='qty_" + nomor + "' onblur='cektotal(" + nomor + ")' style='width:60px;' />";
            Rows += "</td>";
            Rows += "<td data-header='Harga Satuan'>";
            Rows += "<input type='text' class='form-control divide input-sm' name='harga[]' value='0' id='harga_" + nomor + "' onblur='cektotal(" + nomor + ")' style='width:100px;' />";
            Rows += "</td>";
            Rows += "<td data-header='Expense'>";
            Rows += "<input type='text' class='form-control divide input-sm subtotal' name='expense[]' value='0' id='expense_" + nomor + "' tabindex='-1' readonly style='width:100px;' />";
            Rows += "</td>";
            Rows += "<td data-header='Kasbon'>";
            Rows += "<input type='text' class='form-control divide input-sm subkasbon hidden' name='kasbon[]' value='0' id='kasbon_" + nomor + "' tabindex='-1' readonly />";
            Rows += "</td>";
            Rows += "<td data-header='Bon Bukti'>";
            Rows += "<input type='file'  name='doc_file_" + nomor + "' id='doc_file_" + nomor + "' required />";
            Rows += "</td>";
            Rows += "<th align='center' th scope='row'>";
            Rows += "<button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail(" + nomor + ")' title='Hapus data'><i class='fa fa-close'></i> Hapus</button>";
            Rows += "</th>";
            Rows += "</tr>";
            $("#tanggal_" + nomor).focus();
            nomor++;
            $('#detail_body').append(Rows);
            $(".tanggal").datepicker({
                todayHighlight: true,
                format: "yyyy-mm-dd",
                showInputs: true,
                autoclose: true
            });
            $('.select2').select2();
            $(".divide").divide();
        }

        function delDetail(row) {
            $('#tr1_' + row).remove();
            cektotal(row);
        }

        function data_approve() {
            swal({
                    title: "Anda Yakin?",
                    text: "Data Akan Disetujui!",
                    type: "info",
                    showCancelButton: true,
                    confirmButtonText: "Ya, setuju!",
                    cancelButtonText: "Tidak!",
                    closeOnConfirm: false,
                    closeOnCancel: true
                },
                function(isConfirm) {
                    if (isConfirm) {
                        id = $("#id").val();
                        $.ajax({
                            url: url_approve + id,
                            dataType: "json",
                            type: 'POST',
                            success: function(msg) {
                                if (msg['save'] == '1') {
                                    swal({
                                        title: "Sukses!",
                                        text: "Data Berhasil Di Setujui",
                                        type: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    window.location.reload();
                                } else {
                                    swal({
                                        title: "Gagal!",
                                        text: "Data Gagal Di Setujui",
                                        type: "error",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                };
                                console.log(msg);
                            },
                            error: function(msg) {
                                swal({
                                    title: "Gagal!",
                                    text: "Ajax Data Gagal Di Proses",
                                    type: "error",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                console.log(msg);
                            }
                        });
                    }
                });
        }

        function data_reject() {
            swal({
                    title: "Perhatian",
                    text: "Berikan alasan penolakan",
                    type: "input",
                    showCancelButton: true,
                    closeOnConfirm: false,
                    closeOnCancel: true
                },
                function(inputValue) {
                    if (inputValue === false) return false;
                    if (inputValue === "") {
                        swal.showInputError("Tuliskan alasan anda");
                        return false
                    }

                    swal({
                            title: "Anda Yakin?",
                            text: "Data Akan Tolak!",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonText: "Ya, tolak!",
                            cancelButtonText: "Tidak!",
                            closeOnConfirm: false,
                            closeOnCancel: true
                        },
                        function(isConfirm) {
                            if (isConfirm) {
                                id = $("#id").val();
                                $.ajax({
                                    url: base_url + 'expense/reject/',
                                    data: {
                                        'id': id,
                                        'reason': inputValue,
                                        'table': 'tr_expense'
                                    },
                                    dataType: "json",
                                    type: 'POST',
                                    success: function(msg) {
                                        if (msg['save'] == '1') {
                                            swal({
                                                title: "Sukses!",
                                                text: "Data Berhasil Di Tolak",
                                                type: "success",
                                                timer: 1500,
                                                showConfirmButton: false
                                            });
                                            window.location.reload();
                                        } else {
                                            swal({
                                                title: "Gagal!",
                                                text: "Data Gagal Di Tolak",
                                                type: "error",
                                                timer: 1500,
                                                showConfirmButton: false
                                            });
                                        };
                                        console.log(msg);
                                    },
                                    error: function(msg) {
                                        swal({
                                            title: "Gagal!",
                                            text: "Ajax Data Gagal Di Proses",
                                            type: "error",
                                            timer: 1500,
                                            showConfirmButton: false
                                        });
                                        console.log(msg);
                                    }
                                });
                            }
                        });

                });
        }
    </script>