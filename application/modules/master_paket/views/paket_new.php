<!-- Main content-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs">
                        <li><a href="<?php echo site_url('master_paket'); ?>">
                                Data Paket</a>
                        </li>
                        <li class="active"><a href="<?php echo site_url('master_paket/paket_new'); ?>">
                                Tambah Paket</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active">
                            <div class="panel panel-filled">
                                <div class="panel-body">
                                    <div class="row">
                                        <?php
                                        $form_id = "FormTambah";
                                        echo form_open(site_url('master_paket/paket_new'), array('class' => 'form-horizontal', 'id' => $form_id));
                                        ?>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">Nama Paket</label>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" name="nm_paket" placeholder="Nama Paket">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-3">Kategori</label>
                                                <div class="col-md-4">
                                                    <select class="form-control" name="kategori">
                                                        <option value="">-Pilih Kategori-</option>
                                                        <?php
                                                        if ($kategori->num_rows() > 0) {
                                                            foreach ($kategori->result() as $d) {
                                                                echo "<option value='" . $d->id_kategori_paket . "'>" . $d->kategori_paket . "</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-3"></label>
                                                <div class="col-md-9 pull-right">
                                                    <a href="<?= base_url('master_paket') ?>" class="btn btn-sm btn-danger">
                                                        <i class="fa fa-arrow-left"></i> Kembali
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-primary" id="SaveFormModal">
                                                        <i class="fa fa-save"></i> Simpan Data
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php echo form_close(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script type="text/javascript">
    $(document).on('click', '#SaveFormModal', function() {
        swal({
            type: 'warning',
            title: 'Are you sure?',
            text: 'This data will be saved !',
            showCancelButton: true
        }, function(next) {
            if(next) {
                var formData = $('.form-horizontal').serialize();

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_paket',
                    data: formData,
                    cache: false,
                    dataType: 'JSON',
                    success: function(result) {
                        if(result.status == 1) {
                            swal({
                                type: 'success',
                                title: 'Success !',
                                text: result.msg
                            }, function(after) {
                                setTimeout(function() {
                                    window.location = siteurl + active_controller
                                }, 1500)
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Failed !',
                                text: result.msg
                            });
                        }
                    },
                    error: function(result) {
                        swal({
                            type: 'error',
                            title: 'Error !',
                            text: 'Please try again later !'
                        });
                    }
                });
            }
        });
    });
</script>