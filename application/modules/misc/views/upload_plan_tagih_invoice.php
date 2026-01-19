<div class="row">
    <form action="" method="post" id="frm-data" enctype="multipart/form-data">
        <div class="col-md-6">
            <div class="form-group">
                <label for="">Upload CSV</label>
                <input type="file" name="upload_csv" id="" class="form-control form-control-sm">
            </div>
        </div>
        <div class="col-md-12">
            <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> Upload</button>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Warning !',
            text: 'Data will be uploaded !',
            showConfirmButton: true,
            showCancelButton: true
        }).then((next) => {
            if(next.isConfirmed) {
                var frmdata = new FormData($(this)[0]);

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'update_plan_tagih_invoice',
                    data: frmdata,
                    cache: false,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function(result) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success !',
                            text: 'Data has been updated !',
                            showConfirmButton: false,
                            showCancelButton: false,
                            allowEscapeKey: false,
                            allowClickOutside: false,
                            timer: 3000
                        }).then(() => {
                            location.reload(true);
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error !',
                            text: 'Please try again later !',
                            showConfirmButton: false,
                            showCancelButton: true
                        });
                    }
                });
            }
        });
    })
</script>