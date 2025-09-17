<!-- <link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css"> -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" rel="stylesheet" />

<style>
    .font_11 {
        font-size: 12px;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-body">
                <table class="table w-100">
                    <tr>
                        <td width="150" class="text-center">No. PO</td>
                        <td width="10">:</td>
                        <td>
                            <select name="" id="" class="form-control form-control-sm search_no_po">
                                <option value="">- Choose No. PO -</option>
                                <?php
                                foreach ($results['list_po'] as $item) {
                                    echo '<option value="' . $item->no_po . '">' . $item->no_surat . '</option>';
                                }
                                ?>
                            </select>
                        </td>

                        <td width="150" class="text-center">Supplier Name</td>
                        <td width="10">:</td>
                        <td>
                            <select name="" id="" class="form-control form-control-sm select2 search_supplier">
                                <option value="">- Choose Supplier -</option>
                                <?php
                                foreach ($results['list_supplier'] as $item) {
                                    echo '<option value="' . $item->kode_supplier . '">' . $item->nama . '</option>';
                                }
                                ?>
                            </select>
                        </td>

                        <td width="150" class="text-center">Curr</td>
                        <td width="10">:</td>
                        <td>
                            <select name="" id="" class="form-control form-control-sm select2 search_curr">
                                <option value="">- Choose Curr -</option>
                                <?php
                                foreach ($results['list_curr'] as $item) {
                                    echo '<option value="' . $item->kode . '">' . $item->kode . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center">
                            Tgl PO
                        </td>
                        <td>:</td>
                        <td>
                            <input type="date" name="" id="" class="form-control form-control-sm search_tgl_po_before">
                        </td>
                        <td>
                            <input type="date" name="" id="" class="form-control form-control-sm search_tgl_po_after">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary search_monitoring_po">
                                <i class="fa fa-search"></i> Search
                            </button>
                        </td>
                        <td colspan="4"></td>
                    </tr>
                </table>
                <div class="table-responsive">

                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Detail Incoming</h4>
            </div>
            <div class="modal-body" id="ModalView">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .mid {
        vertical-align: middle !important;
    }

    .chosen-select {
        min-width: 200px !important;
        max-width: 100% !important;
    }

    .bold {
        font-weight: bold !important;
        padding-right: 20px !important;
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
<script type="text/javascript">
    $('.search_no_po').chosen();
    $('.search_supplier').chosen();
    $('.search_curr').chosen();

    $(document).on('click', '.detail_incoming', function() {
        var no_po = $(this).data('no_po');
        var kode_trans = $(this).data('kode_trans');
        var tipe_incoming = $(this).data('tipe_incoming');

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'detail_incoming',
            data: {
                'no_po': no_po,
                'kode_trans': kode_trans,
                'tipe_incoming': tipe_incoming
            },
            cache: false,
            success: function(result) {
                $('#ModalView').html(result);
                $('#dialog-popup').modal('show');
            }
        });
    });

    $(document).on('click', '.search_monitoring_po', function() {
        var no_po = $('.search_no_po').val();
        var supplier = $('.search_supplier').val();
        var curr = $('.search_curr').val();
        var tgl_before = $('.search_tgl_po_before').val();
        var tgl_after = $('.search_tgl_po_after').val();

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'search_monitoring_po',
            data: {
                'no_po': no_po,
                'supplier': supplier,
                'curr': curr,
                'tgl_before': tgl_before,
                'tgl_after': tgl_after
            },
            cache: false,
            dataType: 'json',
            success: function(result) {
                
                $('.table-responsive').html(result.hasil);
                // $('#dataTable').dataTable();
            },
            error: function(result) {
                swal({
                    title: 'Failed !',
                    text: 'Please try again later !',
                    type: 'warning'
                });
            }
        })
    });

    $(document).on('click', '.view_invoice', function() {
        var id = $(this).data('id');
        var tipe = $(this).data('tipe');

        $.ajax({
            type: "POST",
            url: siteurl + active_controller + 'view_invoice_po',
            data: {
                'id': id,
                'tipe': tipe
            },
            cache: false,
            success: function(result) {
                $('#ModalView').html(result);
                $('#dialog-popup').modal('show');
            },
            error: function(result) {
                swal({
                    title: 'Error !',
                    text: 'Please try again later !',
                    type: 'error'
                });
            }
        });
    });
</script>