<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<style>
    .box-header .pull-right .btn {
        margin-left: 5px;
    }
</style>
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-list"></i> Review Upload Rekening Koran</h3>
        <div class="pull-right">
            <button class="btn btn-sm btn-success" id="btn-approve-all"><i class="fa fa-check"></i> Approve Semua</button>
            <button class="btn btn-sm btn-danger" id="btn-delete-all"><i class="fa fa-trash"></i> Hapus Semua</button>
            <button class="btn btn-sm btn-primary" id="btn-approve-selected"><i class="fa fa-check-square-o"></i> Approve Selected</button>
            <button class="btn btn-sm btn-warning" id="btn-delete-selected"><i class="fa fa-trash-o"></i> Hapus Selected</button>
            <a href="<?= base_url('alokasi') ?>" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <div class="box-body">
        <input type="hidden" id="id_header" value="<?= $id_header ?>">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="table_pending">
                <thead>
                    <tr>
                        <th class="text-center" width="30"><input type="checkbox" id="check-all"></th>
                        <th class="text-center" width="50">No.</th>
                        <th class="text-center">Tanggal Transaksi</th>
                        <th class="text-center">Bank</th>
                        <th class="text-center">Keterangan</th>
                        <th class="text-center">Ref No</th>
                        <th class="text-center">Debit</th>
                        <th class="text-center">Kredit</th>
                        <th class="text-center">Saldo Akhir</th>
                        <th class="text-center" width="80">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modal-edit" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Revisi Data Alokasi</h4>
            </div>
            <div class="modal-body">
                <form id="form-edit">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" class="form-control" name="keterangan" id="edit-keterangan" required>
                    </div>
                    <div class="form-group">
                        <label>Reference No</label>
                        <input type="text" class="form-control" name="reference_no" id="edit-reference-no">
                    </div>
                    <div class="form-group">
                        <label>Debit</label>
                        <input type="text" class="form-control auto-numeric" name="nominal_debit" id="edit-debit">
                    </div>
                    <div class="form-group">
                        <label>Kredit</label>
                        <input type="text" class="form-control auto-numeric" name="nominal_kredit" id="edit-kredit">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-save-edit">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
$(document).ready(function() {
    $('.auto-numeric').autoNumeric('init', {aDec: '.', aSep: ',', aSign: '', mDec: '2'});
    loadData();

    $('#check-all').change(function() {
        $('.check-item').prop('checked', $(this).prop('checked'));
    });

    $('#btn-approve-all').click(function() {
        var id_header = $('#id_header').val();
        swal({
            title: "Approve Semua?",
            text: "Semua data pada batch ini akan diapprove.",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Ya, Approve!",
            closeOnConfirm: false
        }, function(){
            $.post(siteurl + active_controller + 'approve_data', {id_header: id_header}, function(res) {
                if(res.status == 1) {
                    swal({
                        title: "Success!",
                        text: res.msg,
                        type: "success"
                    }, function() {
                        window.location.href = siteurl + active_controller;
                    });
                }
            }, 'json');
        });
    });

    $('#btn-delete-all').click(function() {
        var id_header = $('#id_header').val();
        swal({
            title: "Hapus Semua?",
            text: "Semua data pada batch ini akan dihapus secara permanen.",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Ya, Hapus!",
            closeOnConfirm: false
        }, function(){
            $.post(siteurl + active_controller + 'delete_pending_data', {id_header: id_header}, function(res) {
                if(res.status == 1) {
                    swal({
                        title: "Deleted!",
                        text: res.msg,
                        type: "success"
                    }, function() {
                        window.location.href = siteurl + active_controller;
                    });
                }
            }, 'json');
        });
    });

    $('#btn-approve-selected').click(function() {
        var ids = [];
        $('.check-item:checked').each(function() { ids.push($(this).val()); });
        if(ids.length == 0) return swal("Warning", "Pilih data terlebih dahulu", "warning");

        swal({
            title: "Approve Terpilih?",
            text: ids.length + " data akan diapprove.",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Ya, Approve!",
            closeOnConfirm: false
        }, function(){
            $.post(siteurl + active_controller + 'approve_data', {ids: ids}, function(res) {
                if(res.status == 1) {
                    swal("Success!", res.msg, "success");
                    loadData();
                    $('#check-all').prop('checked', false);
                }
            }, 'json');
        });
    });

    $('#btn-delete-selected').click(function() {
        var ids = [];
        $('.check-item:checked').each(function() { ids.push($(this).val()); });
        if(ids.length == 0) return swal("Warning", "Pilih data terlebih dahulu", "warning");

        swal({
            title: "Hapus Terpilih?",
            text: ids.length + " data akan dihapus.",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Ya, Hapus!",
            closeOnConfirm: false
        }, function(){
            $.post(siteurl + active_controller + 'delete_pending_data', {ids: ids, id_header: $('#id_header').val()}, function(res) {
                if(res.status == 1) {
                    swal("Deleted!", res.msg, "success");
                    loadData();
                    $('#check-all').prop('checked', false);
                    
                    // If table is empty, maybe redirect
                    if ($('.check-item').length <= ids.length) {
                        setTimeout(function(){
                            window.location.href = siteurl + active_controller;
                        }, 1500);
                    }
                }
            }, 'json');
        });
    });

    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        var tr = $(this).closest('tr');
        var desc = tr.find('td:eq(4)').text();
        var ref = tr.find('td:eq(5)').text();
        var debit = tr.find('td:eq(6)').text().replace(/,/g, '');
        var kredit = tr.find('td:eq(7)').text().replace(/,/g, '');

        $('#edit-id').val(id);
        $('#edit-keterangan').val(desc);
        $('#edit-reference-no').val(ref);
        $('#edit-debit').autoNumeric('set', debit);
        $('#edit-kredit').autoNumeric('set', kredit);
        
        $('#modal-edit').modal('show');
    });

    $('#btn-save-edit').click(function() {
        var data = $('#form-edit').serialize();
        $.post(siteurl + active_controller + 'update_pending_data', data, function(res) {
            if(res.status == 1) {
                $('#modal-edit').modal('hide');
                loadData();
                swal("Success", res.msg, "success");
            } else {
                swal("Error", "Gagal menyimpan revisi", "error");
            }
        }, 'json');
    });
});

function loadData() {
    var id_header = $('#id_header').val();
    $('#table_pending').DataTable({
        destroy: true,
        ajax: {
            url: siteurl + active_controller + 'get_pending_alokasi',
            type: 'POST',
            data: {id_header: id_header}
        },
        columns: [
            {data: 'id', render: function(data) { return '<input type="checkbox" class="check-item" value="'+data+'">'; }, className: 'text-center', orderable: false},
            {data: 'no', className: 'text-center'},
            {data: 'tanggal_transaksi', className: 'text-center'},
            {data: 'bank'},
            {data: 'keterangan'},
            {data: 'reference_no', className: 'text-center'},
            {data: 'debit', className: 'text-right'},
            {data: 'kredit', className: 'text-right'},
            {data: 'saldo', className: 'text-right'},
            {data: 'id', render: function(data) { 
                return '<button class="btn btn-sm btn-info btn-edit" data-id="'+data+'" title="Revisi"><i class="fa fa-edit"></i></button>';
            }, className: 'text-center', orderable: false}
        ]
    });
}
</script>
