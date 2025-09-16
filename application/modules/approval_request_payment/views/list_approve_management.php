<?php
$ENABLE_ADD     = has_permission('Approval_Request_Payment_Management.Add');
$ENABLE_MANAGE  = has_permission('Approval_Request_Payment_Management.Manage');
$ENABLE_VIEW    = has_permission('Approval_Request_Payment_Management.View');
$ENABLE_DELETE  = has_permission('Approval_Request_Payment_Management.Delete');

$count_transport = 0;
$count_kasbon = 0;
$count_expense = 0;
$count_periodik = 0;
$count_pembayaran_po = 0;

foreach ($data as $item) :
    if ($item->tipe == 'kasbon') {
        $count_kasbon += 1;
    }
    if ($item->tipe == 'expense') {
        $count_expense += 1;
    }
endforeach;
?>
<script src="//cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal')); ?>
<div class="box">
    <div class="container">
        <div class="row">
            <div class="col-md-4" style="margin-top: 2vh;">
                <div class="panel panel-default">
                    <div class="panel-heading bg-yellow">Kasbon</div>
                    <div class="panel-body">
                        <h2><?= $count_kasbon ?></h2>
                    </div>
                    <div class="panel-footer w-100">
                        <button type="button" class="btn btn-sm btn-primary btn_view_req" style="width: 100%;" data-val="kasbon"><i class="fa fa-eye"></i> View</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4" style="margin-top: 2vh;">
                <div class="panel panel-default">
                    <div class="panel-heading bg-blue">Expense</div>
                    <div class="panel-body">
                        <h2><?= $count_expense ?></h2>
                    </div>
                    <div class="panel-footer w-100">
                        <button type="button" class="btn btn-sm btn-primary btn_view_req" style="width: 100%;" data-val="expense"><i class="fa fa-eye"></i> View</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 list_kasbon" style="display: none;">
                <a href="<?= base_url('approval_request_payment/export_excel_kasbon_checker/?tingkat=2') ?>" class="btn btn-sm btn-success"><i class="fa fa-files"></i> Export Excel</a>
                <h2>Request Payment Kasbon</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">No. Kasbon</th>
                            <th class="text-center">Request By</th>
                            <th class="text-center">Tanggal Pengajuan</th>
                            <th class="text-center">Deskripsi Pengajuan</th>
                            <th class="text-center">Kategori</th>
                            <th class="text-center">Nilai Pengajuan</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $ttl_kasbon = 0;
                        foreach ($data as $item_kasbon) :

                            $get_kasbon_header = $this->db->get_where('kons_tr_kasbon_project_header', array('id' => $item_kasbon->no_doc))->row();

                            $tipe = '';
                            if (!empty($get_kasbon_header)) {
                                if ($get_kasbon_header->tipe == '1') {
                                    $tipe = 'Kasbon Subcont';
                                }
                                if ($get_kasbon_header->tipe == '2') {
                                    $tipe = 'Kasbon Akomodasi';
                                }
                                if ($get_kasbon_header->tipe == '3') {
                                    $tipe = 'Kasbon Others';
                                }
                            }

                            echo '<tr>';
                            echo '<td>' . $item_kasbon->no_doc . '</td>';
                            echo '<td>' . $item_kasbon->nama . '</td>';
                            echo '<td>' . $item_kasbon->tgl_doc . '</td>';
                            echo '<td>' . $item_kasbon->keperluan . '</td>';
                            echo '<td>' . $tipe . '</td>';
                            echo '<td class="text-right">' . number_format($item_kasbon->jumlah) . '</td>';
                            echo '<td>';
                            if ($ENABLE_MANAGE) :
                                echo '<a href="' . base_url($this->uri->segment(1) . '/approval_payment/' . urlencode(str_replace('/', '|', $item_kasbon->no_doc))) . '" class="btn btn-primary btn-sm">';
                                echo '<i class="fa fa-check-square-o"></i>';
                                echo ' Approve';
                                echo '</a>';
                            endif;
                            echo '</td>';
                            echo '</tr>';

                            $ttl_kasbon += $item_kasbon->jumlah;
                        endforeach;
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-right">Grand Total</th>
                            <th class="text-right"><?= number_format($ttl_kasbon) ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="col-md-12 list_expense" style="display: none;">
                <a href="<?= base_url('approval_request_payment/export_excel_expense_checker/?tingkat=2') ?>" class="btn btn-sm btn-success"><i class="fa fa-files"></i> Export Excel</a>
                <h2>Request Payment Expense Report</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">No. Kasbon</th>
                            <th class="text-center">Request By</th>
                            <th class="text-center">Tanggal Pengajuan</th>
                            <th class="text-center">Deskripsi Pengajuan</th>
                            <th class="text-center">Kategori</th>
                            <th class="text-center">Nilai Pengajuan</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $ttl_expense = 0;
                        foreach ($data as $item_expense) :
                            if ($item_expense->tipe == 'expense') {
                                $tipe = ucfirst($item_expense->tipe);
                                $get_expense = $this->db->get_where('tr_expense', ['no_doc' => $item_expense->no_doc])->row_array();

                                echo '<tr>';
                                echo '<td>' . $item_expense->no_doc . '</td>';
                                echo '<td>' . $item_expense->nama . '</td>';
                                echo '<td>' . $item_expense->tgl_doc . '</td>';
                                echo '<td>' . $item_expense->keperluan . '</td>';
                                echo '<td>' . $tipe . '</td>';
                                echo '<td class="text-right">' . number_format($item_expense->jumlah) . '</td>';
                                echo '<td>';
                                if ($ENABLE_MANAGE) :
                                    echo '<a href="' . base_url($this->uri->segment(1) . '/approval_payment/' . urlencode(str_replace('/', '|', $item_expense->no_doc))) . '" class="btn btn-primary btn-sm">';
                                    echo '<i class="fa fa-check-square-o"></i>';
                                    echo ' Approve';
                                    echo '</a>';
                                endif;
                                echo '</td>';
                                echo '</tr>';

                                $ttl_expense += $item_expense->jumlah;
                            }
                        endforeach;
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-right">Grand Total</th>
                            <th class="text-right"><?= number_format($ttl_expense) ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- /.box-body -->
</div>
<?= form_close() ?>
<div class="modal modal-default fade" id="modal_view_receive_invoice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title title_modal" id="myModalLabel">View Receive Invoice</h4>
            </div>
            <div class="modal-body" id="ModalViewSPPLM">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Tutup</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script type="text/javascript">
    function trshowall() {
        $(".trows").removeClass("hidden");
    }

    function trshow(id) {
        $(".trows").addClass("hidden");
        $(".rowshow" + id).removeClass("hidden");
    }
    var url_save = siteurl + 'request_payment/save_approval/';
    //Save

    $(document).on("click", ".btn_view_req", function() {
        var val = $(this).data('val');
        // alert(val);

        $(".list_" + val).toggle();
        if (val == "transportasi") {
            $(".list_kasbon").hide();
            $(".list_expense").hide();
            $(".list_periodik").hide();
            $('.list_pembayaran_po').hide();
        }
        if (val == "kasbon") {
            $(".list_transportasi").hide();
            $(".list_expense").hide();
            $(".list_periodik").hide();
            $('.list_pembayaran_po').hide();
        }
        if (val == "expense") {
            $(".list_transportasi").hide();
            $(".list_kasbon").hide();
            $(".list_periodik").hide();
            $('.list_pembayaran_po').hide();
        }
        if (val == "periodik") {
            $(".list_transportasi").hide();
            $(".list_kasbon").hide();
            $(".list_expense").hide();
            $('.list_pembayaran_po').hide();
        }
        if (val == "pembayaran_po") {
            $(".list_transportasi").hide();
            $(".list_kasbon").hide();
            $(".list_expense").hide();
            $(".list_periodik").hide();
        }
    });

    $(document).on('click', '.view_receive_invoice', function() {
        var id_invoice = $(this).data('id_invoice');

        $.ajax({
            type: "POST",
            url: siteurl + active_controller + "view_receive_invoice",
            data: {
                "id_invoice": id_invoice
            },
            cache: false,
            success: function(result) {
                $('#ModalViewSPPLM').html(result);
                $('#modal_view_receive_invoice').modal('show');
            },
            error: function(result) {
                swal({
                    title: 'Error!',
                    text: 'Please try again later!',
                    type: 'error'
                });
            }
        });
    });

    $('#frm_data').on('submit', function(e) {
        e.preventDefault();
        var errors = "";
        if (errors == "") {
            swal({
                    title: "Anda Yakin?",
                    text: "Data Akan Di Setujui!",
                    type: "info",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Setujui!",
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
                                        text: "Data Berhasil Di Setujui",
                                        type: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    window.location.href = window.location.href;
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
        } else {
            swal(errors);
            return false;
        }
    });
    $("#btnxls").click(function() {
        $("#mytabledata").table2excel({
            exclude: ".exclass",
            name: "Request Payment Approval",
            filename: "RequestPaymentApproval.xls", // do include extension
            preserveColors: false // set to true if you want background colors and font colors preserved
        });
    });
</script>