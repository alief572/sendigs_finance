<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
<div class="box">
    <div class="box-header">
    </div>
    <div class="box-body">
        <div class="table-responsive  col-md-12">
            <table id="mytabledata" class="table table-bordered">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th class="text-center">No Dokumen</th>
                        <th class="text-center">Nilai Pengembalian</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    foreach ($data_pengembalian as $item) {

                        $status = '<div class="badge bg-blue">Waiting Approval</div>';
                        if($item->status == 2) {
                            $status = '<div class="badge bg-red">Rejected</div>';
                        }

                        echo '<tr>';
                        echo '<td class="text-center">' . $no . '</td>';
                        echo '<td class="text-center">' . $item->no_doc . '</td>';
                        echo '<td class="text-center">' . number_format($item->transfer_jumlah) . '</td>';
                        echo '<td class="text-center">' . $status . '</td>';
                        echo '<td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary approval" data-id="' . $item->id . '"><i class="fa fa-check"></i></button>
                                <button type="button" class="btn btn-sm btn-danger reject" data-id="' . $item->id . '"><i class="fa fa-close"></i></button>
                            </td>';
                        echo '</tr>';

                        $no++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.box-body -->
</div>
<div class="modal fade" id="Mymodal">
    <div class="modal-dialog" style="width:100%">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">View Expense</h4>
            </div>
            <div class="modal-body" id="listexpense">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>


<script type="text/javascript">
    $(document).ready(function() {
        $("#mytabledata").DataTable();
    });

    $(document).on('click', '.approval', function() {
        var id = $(this).data('id');

        swal({
                title: "Anda Yakin?",
                text: "Pengembalian expense akan di approve !",
                type: "info",
                showCancelButton: true,
                confirmButtonText: "Ya, Approve!",
                cancelButtonText: "Tidak!",
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: siteurl + active_controller + 'approve_pengembalian_expense',
                        dataType: "json",
                        type: 'POST',
                        data: {
                            'id': id
                        },
                        cache: false,
                        success: function(msg) {
                            if (msg.status == '1') {
                                swal({
                                    title: "Sukses!",
                                    text: "Data Pengembalian berhasil di approve",
                                    type: "success",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                window.location.reload();
                            } else {
                                swal({
                                    title: "Gagal!",
                                    text: "Data Pengembalian gagal Di Approve !",
                                    type: "error",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            };
                        },
                        error: function(msg) {
                            swal({
                                title: "Gagal!",
                                text: "Ajax Data Gagal Di Proses",
                                type: "error",
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    });
                }
            });
    });

    $(document).on('click', '.reject', function() {
        var id = $(this).data('id');

        swal({
                title: "Anda Yakin?",
                text: "Pengembalian expense akan di reject !",
                type: "info",
                showCancelButton: true,
                confirmButtonText: "Ya, Reject!",
                cancelButtonText: "Tidak!",
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: siteurl + active_controller + 'reject_pengembalian_expense',
                        dataType: "json",
                        type: 'POST',
                        data: {
                            'id': id
                        },
                        cache: false,
                        success: function(msg) {
                            if (msg.status == '1') {
                                swal({
                                    title: "Sukses!",
                                    text: "Data Pengembalian berhasil di reject",
                                    type: "success",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                window.location.reload();
                            } else {
                                swal({
                                    title: "Gagal!",
                                    text: "Data Pengembalian gagal Di Reject !",
                                    type: "error",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            };
                        },
                        error: function(msg) {
                            swal({
                                title: "Gagal!",
                                text: "Ajax Data Gagal Di Proses",
                                type: "error",
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    });
                }
            });
    });
</script>