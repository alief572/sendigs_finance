<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    .tax-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .tax-PKP { background: #e8f4fd; color: #1976d2; border: 1px solid #bbdefb; }
    .tax-WAPU { background: #fef8e7; color: #b78103; border: 1px solid #ffe082; }
    .tax-KB { background: #f3e5f5; color: #7b1fa2; border: 1px solid #e1bee7; }

    .table_list th {
        background-color: #f4f6f9;
        font-weight: 700;
        font-size: 12px;
        color: #333;
        vertical-align: middle !important;
        white-space: nowrap;
    }
    .table_list td {
        vertical-align: middle !important;
        font-size: 13px;
    }
    .nowrap {
        white-space: nowrap;
    }
    .font-bold {
        font-weight: 700;
    }
    .form-control-sm {
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
    }
</style>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title" style="font-weight: 700; color: #2c3e50;">
            <i class="fa fa-pencil-square-o"></i> Form Penerimaan Piutang
        </h3>
    </div>
    <div class="box-body">
        <form action="" method="post" id="form_data">
            <input type="hidden" name="id_alokasi" value="<?= $results['id_alokasi'] ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="customer">Nama Customer <span class="text-red">*</span></label>
                        <select class="form-control form-control-sm" name="customer" id="customer">
                            <option value="">- Pilih Customer -</option>
                            <?php
                            foreach ($results['list_customer'] as $item) {
                                echo '<option value="' . htmlspecialchars($item['nm_customer'], ENT_QUOTES) . '">' . htmlspecialchars($item['nm_customer']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pph23_dipotong">PPH23 Dipotong <span class="text-red">*</span></label>
                        <select class="form-control form-control-sm" name="pph23_dipotong" id="pph23_dipotong">
                            <option value="Y" selected>Dipotong PPh</option>
                            <option value="N">Tidak Dipotong PPh</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nominal_penerimaan_bank">Nominal Penerimaan Bank <span class="text-red">*</span></label>
                        <input type="text" name="nominal_penerimaan_bank" id="nominal_penerimaan_bank" class="form-control form-control-sm text-right" value="<?= $results['nominal_penerimaan_bank'] ?>" readonly style="font-size: 16px; font-weight: 700; background-color: #f8fafc;">
                    </div>
                    <div class="alert alert-info" style="margin-top: 10px; padding: 10px 14px; font-size: 12px; border-radius: 4px;">
                        <i class="fa fa-info-circle"></i> Jenis perlakuan PPN (PKP, WAPU, Kawasan Berikat) terkunci otomatis dari data invoice asal.
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top: 15px;">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table_list">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 100px;">Tgl Invoice</th>
                                    <th class="text-center" style="width: 140px;">Nomor Invoice</th>
                                    <th class="text-center" style="width: 80px;">TAX Code</th>
                                    <th class="text-right" style="width: 110px;">Penjualan</th>
                                    <th class="text-right" style="width: 100px;">DPP</th>
                                    <th class="text-right" style="width: 90px;">PPN</th>
                                    <th class="text-right" style="width: 90px;">PPH 23</th>
                                    <th class="text-right" style="width: 120px;">Total Tagihan<br><small class="text-muted font-normal">(Penjualan+PPN)</small></th>
                                    <th class="text-right" style="width: 120px;">Total Tagihan<br><small class="text-muted font-normal">- PPH</small></th>
                                    <th class="text-right" style="width: 120px;">Saldo Piutang</th>
                                    <th class="text-center" style="width: 60px;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="tbody_list">
                                <tr>
                                    <td colspan="11" class="text-center text-muted" style="padding: 20px;">Pilih Customer terlebih dahulu untuk memunculkan daftar invoice.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top: 20px;">
                <div class="col-md-12">
                    <a href="<?= base_url('penerimaan_uang') ?>" class="btn btn-default" style="margin-right: 10px;">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-success btn_submit_process">
                        <i class="fa fa-check"></i> Proses Alokasi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Dialog Popup Alokasi -->
<div class="modal fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" style="width: 98% !important; max-width: 1440px;">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #3c8dbc; color: #fff;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.8;"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-money"></i>&nbsp; Penerimaan Piutang & Jurnal Otomatis</h4>
            </div>
            <form action="" id="frm_data" enctype="multipart/form-data">
                <div class="modal-body" id="MyModalBody" style="max-height: 80vh; overflow-y: auto; padding: 15px;">
                    <!-- Konten alokasi dimuat via AJAX process_alokasi -->
                </div>
                <div class="modal-footer" style="background-color: #f8fafc;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success btn_proses" disabled>
                        <i class="fa fa-save"></i> Simpan Penerimaan & Bentuk Jurnal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function() {
        $('#customer').chosen({
            width: '100%',
            search_contains: true
        });
        $('#pph23_dipotong').chosen({
            width: '100%'
        });

        $('#nominal_penerimaan_bank').autoNumeric('init', {
            vMin: '-9999999999999.99',
            vMax: '9999999999999.99',
            aSep: '.',
            aDec: ','
        });
    });

    function loadInvoices() {
        var nm_customer = $('#customer').val();
        var pph23_dipotong = $('#pph23_dipotong').val();

        if (nm_customer !== '') {
            $('.tbody_list').html('<tr><td colspan="11" class="text-center"><i class="fa fa-spinner fa-spin"></i> Memuat data invoice...</td></tr>');
            $.ajax({
                type: 'post',
                url: siteurl + active_controller + 'get_inv_by_cust',
                data: {
                    'nm_customer': nm_customer,
                    'pph23_dipotong': pph23_dipotong
                },
                cache: false,
                success: function(result) {
                    $('.tbody_list').html(result);
                },
                error: function() {
                    swal({
                        type: 'error',
                        title: 'Error !',
                        text: 'Gagal memuat invoice. Silakan coba kembali.',
                        timer: 3000
                    });
                }
            });
        } else {
            $('.tbody_list').html('<tr><td colspan="11" class="text-center text-muted" style="padding: 20px;">Pilih Customer terlebih dahulu untuk memunculkan daftar invoice.</td></tr>');
        }
    }

    $(document).on('change', '#customer', loadInvoices);
    $(document).on('change', '#pph23_dipotong', loadInvoices);

    $(document).on('submit', '#form_data', function(e) {
        e.preventDefault();

        var customer = $('#customer').val();
        var pph23_dipotong = $('#pph23_dipotong').val();
        var check_data = $('input[name="choose_inv\\[\\]"]:checked').length;

        if (customer === '') {
            swal({
                type: 'warning',
                title: 'Peringatan !',
                text: 'Nama Customer wajib dipilih !',
                timer: 3000
            });
            return false;
        }

        if (pph23_dipotong === '') {
            swal({
                type: 'warning',
                title: 'Peringatan !',
                text: 'Pilihan PPH23 Dipotong wajib dipilih !',
                timer: 3000
            });
            return false;
        }

        if (check_data < 1) {
            swal({
                type: 'warning',
                title: 'Peringatan !',
                text: 'Centang minimal satu invoice yang akan dialokasikan !',
                timer: 3000
            });
            return false;
        }

        var formData = $(this).serialize();
        $('#MyModalBody').html('<div class="text-center" style="padding: 40px;"><i class="fa fa-spinner fa-spin fa-3x text-primary"></i><p style="margin-top: 10px;">Menyiapkan preview alokasi & jurnal...</p></div>');
        $('#dialog-popup').modal('show');

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'process_alokasi',
            data: formData,
            cache: false,
            success: function(result) {
                $('#MyModalBody').html(result);
                $('.autonum').autoNumeric('init', {
                    vMin: '-9999999999999.99',
                    vMax: '9999999999999.99',
                    aSep: ',',
                    aDec: '.'
                });
            },
            error: function() {
                swal({
                    type: 'error',
                    title: 'Error !',
                    text: 'Gagal memproses preview alokasi. Coba lagi nanti.',
                    timer: 3000
                });
                $('#dialog-popup').modal('hide');
            }
        });
    });

    $(document).on('submit', '#frm_data', function(e) {
        e.preventDefault();

        var kontrolVal = parseFloat($('input[name="kontrol"]').val()) || 0;
        if (Math.abs(kontrolVal) > 0.009) {
            swal({
                type: 'warning',
                title: 'Kontrol Belum 0 !',
                text: 'Kontrol harus 0 sebelum penerimaan dapat disimpan.',
                timer: 3000
            });
            return false;
        }

        var debitJurnal = parseFloat($('input[name="total_debit_jurnal"]').val()) || 0;
        var kreditJurnal = parseFloat($('input[name="total_kredit_jurnal"]').val()) || 0;

        if (Math.abs(debitJurnal - kreditJurnal) > 0.009) {
            swal({
                type: 'error',
                title: 'Jurnal Tidak Balance !',
                text: 'Total Debit tidak sama dengan Total Kredit. Harap periksa kembali alokasi.',
                timer: 3000
            });
            return false;
        }

        swal({
            type: 'warning',
            title: 'Konfirmasi Simpan',
            text: 'Simpan penerimaan piutang dan bentuk jurnal otomatis?',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#00a65a'
        }, function(isConfirm) {
            if (isConfirm) {
                var postData = $('#frm_data').serialize();
                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_penerimaan_piutang',
                    data: postData,
                    cache: false,
                    dataType: 'json',
                    success: function(result) {
                        if (result.status == '1' || result.save == '1') {
                            swal({
                                type: 'success',
                                title: 'Berhasil !',
                                text: result.msg || result.message || 'Penerimaan piutang berhasil disimpan.',
                                timer: 2500
                            }, function() {
                                window.location.href = siteurl + active_controller;
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Gagal Menyimpan',
                                text: result.msg || result.message || 'Terjadi kesalahan saat menyimpan.',
                                timer: 4000
                            });
                        }
                    },
                    error: function() {
                        swal({
                            type: 'error',
                            title: 'Error !',
                            text: 'Gagal menghubungi server. Silakan coba kembali.',
                            timer: 3000
                        });
                    }
                });
            }
        });
    });
</script>
