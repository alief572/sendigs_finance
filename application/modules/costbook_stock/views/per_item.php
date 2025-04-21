<?php
$ENABLE_ADD     = has_permission('Costbook_Stock_Per_Item.Add');
$ENABLE_MANAGE  = has_permission('Costbook_Stock_Per_Item.Manage');
$ENABLE_VIEW    = has_permission('Costbook_Stock_Per_Item.View');
$ENABLE_DELETE  = has_permission('Costbook_Stock_Per_Item.Delete');

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
                <input type="date" name="" id="" class="form-control form-control-sm tgl_from" placeholder="- From -">
            </div>
            <div class="col-md-1 text-center">
                <h5>S/D</h5>
            </div>
            <div class="col-md-3">
                <input type="date" name="" id="" class="form-control form-control-sm tgl_to" placeholder="- To -">
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
                <button type="button" class="btn btn-sm btn-primary change_warehouse"><i class="fa fa-search"></i> Cari</button>
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
                    <th class="text-center">#</th>
                    <th class="text-center">Code</th>
                    <th class="text-center">Material</th>
                    <th class="text-center">Warehouse</th>
                    <th class="text-center">Stok</th>
                    <th class="text-center">Costbook</th>
                    <th class="text-center">Value Neraca</th>
                    <th class="text-center">Last Update</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                foreach ($results['list_data'] as $item) {

                    $nilai_costbook = 0;
                    $value_neraca = 0;
                    $last_update = '';
                    $get_nilai_costbook = $this->db->query("SELECT a.costbook as nilai_costbook, a.created_on, a.value_neraca FROM tr_cost_book a WHERE a.id_material = '" . $item->id_material . "' AND (a.id_gudang_dari = '" . $item->id_gudang . "' OR a.id_gudang_ke = '" . $item->id_gudang . "') ORDER BY a.created_on DESC LIMIT 1")->row();
                    if (!empty($get_nilai_costbook)) {
                        $nilai_costbook = $get_nilai_costbook->nilai_costbook;
                        $value_neraca = $get_nilai_costbook->value_neraca;
                        $last_update = date('d F Y H:i:s', strtotime($get_nilai_costbook->created_on));
                    }

                    echo '<tr>';
                    echo '<td class="text-center">' . $no . '</td>';
                    echo '<td class="text-center">' . $item->kode_produk . '</td>';
                    echo '<td class="text-center">' . $item->nm_material . '</td>';
                    echo '<td class="text-center">' . strtoupper($item->nm_gudang) . '</td>';
                    echo '<td class="text-right">' . number_format($item->qty_stock, 2) . '</td>';
                    echo '<td class="text-right">' . number_format($nilai_costbook, 2) . '</td>';
                    echo '<td class="text-right">' . number_format($value_neraca, 2) . '</td>';
                    echo '<td class="text-center">' . $last_update . '</td>';
                    echo '<td class="text-center">
                                <button type="button" class="btn btn-sm btn-info check_costbook" data-id_material="' . $item->id_material . '" data-id_gudang="' . $item->id_gudang . '" title="Check Costbook"><i class="fa fa-eye"></i></button>
                            </td>';
                    echo '</tr>';

                    $no++;
                }
                ?>
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

        $('#example1').dataTable();
    });

    $(document).on('click', '.change_warehouse', function() {
        var warehouse = $('.select_warehouse').val();
        var tgl_from = $('.tgl_from').val();
        var tgl_to = $('.tgl_to').val();

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'change_warehouse',
            data: {
                'warehouse': warehouse,
                'tgl_from': tgl_from,
                'tgl_to': tgl_to
            },
            cache: false,
            success: function(result) {
                $('.box-body').html(result);
                $('#example1').dataTable();
            }
        });
    });

    $(document).on('click', '.check_costbook', function() {
        var id_material = $(this).data('id_material');
        var id_gudang = $(this).data('id_gudang');

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'view_per_item',
            data: {
                'id_material': id_material,
                'id_gudang': id_gudang
            },
            cache: false,
            success: function(result){
                $('#ModalView').html(result);
                $('#dialog-popup').modal('show');
            },
            error: function(results){
                swal({
                    title: 'Error !',
                    text: 'Please try again later !',
                    type: 'error'
                });
            }
        });
    });
</script>