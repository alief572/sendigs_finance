<?php
$ENABLE_ADD     = has_permission('Costbook_Stock_Neraca_Gudang.Add');
$ENABLE_MANAGE  = has_permission('Costbook_Stock_Neraca_Gudang.Manage');
$ENABLE_VIEW    = has_permission('Costbook_Stock_Neraca_Gudang.View');
$ENABLE_DELETE  = has_permission('Costbook_Stock_Neraca_Gudang.Delete');

?>
<style type="text/css">
    thead input {
        width: 100%;
    }
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="box">
    <div class="box-header">
        <div class="row">
            <div class="col-md-3">
                <select name="bulan_input" id="" class="form-control form-control-sm periode_bulan">
                    <option value="">- Bulan -</option>
                    <?php
                    for ($i = 1; $i <= 12; $i++) {
                        $date = date('Y') . '-' . sprintf('%02s', $i) . '-01';
                        print_r($date);
                        echo '<option value="' . $i . '">' . date('F', strtotime($date)) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="" id="" class="form-control form-control-sm periode_tahun" min='1900' placeholder="- Tahun -">
            </div>
            <div class="col-md-4">
                <select name="" class="form-control form-control-sm chosen_select select_warehouse">
                    <option value="">- Select Warehouse -</option>
                    <?php
                    foreach ($results['list_warehouse'] as $item_warehouse) {
                        echo '<option value="' . $item_warehouse->id . '">' . strtoupper($item_warehouse->nm_gudang) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-primary get_costbook_data"><i class="fa fa-search"></i> Cari</button>
            </div>
        </div>
        <span class="pull-right">
        </span>
    </div>
    <!-- /.box-header -->
    <!-- /.box-header -->
    <div class="box-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">Transaksi</th>
                    <th class="text-center">No. Transaksi</th>
                    <th class="text-center">Tgl Transaksi</th>
                    <th class="text-center">Jenis Transaksi</th>
                    <th class="text-center">Dari Gudang</th>
                    <th class="text-center">Ke Gudang</th>
                    <th class="text-center">Value In</th>
                    <th class="text-center">Value Out</th>
                    <th class="text-center">Saldo</th>
                </tr>
            </thead>
            <tbody class="list_data">

            </tbody>
        </table>
    </div>
    <!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->
<!-- Modal -->

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Detail Costbook Per Item</h4>
            </div>
            <div class="modal-body" id="ModalView">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Close</button>
            </div>
        </div>
    </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        $('.chosen_select').chosen({
            width: '100%'
        });

        // $('#example1').dataTable();
    });

    $(document).on('click', '.get_costbook_data', function() {
        var periode_bulan = $('.periode_bulan').val();
        var periode_tahun = $('.periode_tahun').val();
        var warehouse = $('.select_warehouse').val();

        if (periode_bulan == '' || periode_bulan == null || periode_tahun == '' || periode_tahun == null) {
            swal({
                title: 'Warning !',
                text: 'Please make sure Date From and To is already filled !',
                type: 'warning'
            });
        } else {
            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + 'get_costbook_data',
                data: {
                    'periode_bulan': periode_bulan,
                    'periode_tahun': periode_tahun,
                    'warehouse': warehouse
                },
                cache: false,
                success: function(result) {
                    $('.box-body').html(result);
                    // $('#example1').dataTable();
                }
            })
        }
    });
</script>