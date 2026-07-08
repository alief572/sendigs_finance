<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    .form-inline {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    body {
        background-color: #f5f5f5;
        padding-top: 20px;
    }

    .main-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin-bottom: 30px;
    }

    .form-inline .form-group {
        margin-right: 15px;
        margin-bottom: 10px;
    }

    .form-inline .form-group label {
        margin-right: 8px;
        font-weight: 600;
        color: #555;
    }

    .form-inline .form-control {
        border-radius: 4px;
        border: 1px solid #ddd;
        padding: 8px 12px;
        font-size: 14px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-inline .form-control:focus {
        border-color: #66afe9;
        outline: 0;
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 8px rgba(102, 175, 233, .6);
    }

    .btn {
        border-radius: 6px;
        font-weight: 500;
        margin-right: 8px;
        transition: all 0.2s ease-in-out;
    }

    .btn-primary {
        background-color: #337ab7;
        border-color: #2e6da4;
    }

    .btn-primary:hover {
        background-color: #286090;
        border-color: #204d74;
    }

    .btn-success {
        background-color: #5cb85c;
        border-color: #4cae4c;
    }

    .btn-success:hover {
        background-color: #449d44;
        border-color: #398439;
    }

    .form-control-sm {
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
    }

    .search-section {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .search-section h4 {
        color: #495057;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .date-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .date-input-wrapper {
        display: flex;
        align-items: center;
        margin-right: 15px;
    }

    .date-input-wrapper label {
        margin-right: 8px;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .form-inline .form-group {
            display: block;
            margin-bottom: 15px;
        }

        .date-group {
            flex-direction: column;
            align-items: stretch;
        }

        .date-input-wrapper {
            margin-bottom: 10px;
        }
    }
</style>

<div class="box">
    <div class="box-header">
    </div>
    <div class="box-body">
        <form action="" id="frm-data" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id_detail_penerimaan" value="<?= $id_detail_penerimaan ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="no_invoice">No. Invoice</label>
                        <input type="text" name="no_invoice" id="no_invoice" class="form-control form-control-sm" value="<?= $data_penerimaan['id_inv'] ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="customer">Customer Name</label>
                        <input type="text" name="customer" id="customer" class="form-control form-control-sm" value="<?= $data_penerimaan['nm_customer'] ?>" readonly>
                        <input type="hidden" name="id_customer" value="<?= $data_penerimaan['id_customer'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="project">Project Name</label>
                        <textarea name="project" id="project" class="form-control form-control-sm" readonly><?= $data_penerimaan['nm_project'] ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="keterangan_invoice">Keterangan Invoice</label>
                        <textarea name="keterangan_invoice" id="keterangan_invoice" class="form-control form-control-sm" readonly><?= $data_penerimaan['print_keterangan'] ?></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nilai_pph">Nilai PPH</label>
                        <input type="text" name="nilai_pph" id="nilai_pph" class="form-control form-control-sm text-right" value="<?= $data_penerimaan['pph23'] ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="nilai_setor">Nilai Setor <span class="text-red">*</span></label>
                        <input type="text" name="nilai_setor" id="nilai_setor" class="form-control form-control-sm text-right" value="<?= $data_penerimaan['pph23'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="upload_bukti_setor">Upload Bukti Setor <span class="text-red">*</span></label>
                        <input type="file" name="upload_bukti_setor" id="upload_bukti_setor" class="form-control form-control-sm">
                    </div>
                </div>
            </div>

            <br><br>

            <h4 class="text-bold">Jurnal Penerimaan PPH 23</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-center">Tanggal Jurnal</th>
                        <th class="text-center">COA</th>
                        <th class="text-center">Nama Company</th>
                        <th class="text-center">Nama Account</th>
                        <th class="text-center">Debit</th>
                        <th class="text-center">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $ttl_debit = 0;
                    $ttl_kredit = 0;
                    foreach ($data_coa_jurnal as $item_coa_jurnal) {

                        $debit = 0;
                        $kredit = 0;
                        if ($item_coa_jurnal['no_perkiraan'] == '1106-01-02' || $item_coa_jurnal['no_perkiraan'] == '1106-01-05') {
                            $debit = $data_penerimaan['pph23'];
                        } else {
                            $kredit = $data_penerimaan['pph23'];
                        }

                        echo '<tr>';
                        echo '<td class="text-center">' . date('d F Y') . '</td>';
                        echo '<td class="text-center">' . $item_coa_jurnal['no_perkiraan'] . '</td>';
                        echo '<td class="text-center">' . $nm_company . '</td>';
                        echo '<td class="text-center">' . $item_coa_jurnal['nm_coa'] . '</td>';
                        echo '<td class="text-right">' . number_format($debit) . '</td>';
                        echo '<td class="text-right">' . number_format($kredit) . '</td>';
                        echo '</tr>';

                        $ttl_debit += $debit;
                        $ttl_kredit += $kredit;
                    }
                    ?>
                </tbody>
                <tbody>
                    <tr>
                        <th class="text-right" colspan="4">Grand Total</th>
                        <th class="text-right"><?= number_format($ttl_debit) ?></th>
                        <th class="text-right"><?= number_format($ttl_kredit) ?></th>
                    </tr>
                </tbody>
            </table>

            <a href="<?= base_url('penerimaan_pph_23') ?>" class="btn btn-sm btn-danger">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fa fa-save"></i> Save
            </button>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function() {
        $('#ppn_dipotong').chosen({
            width: '100%'
        });
        $('#pph23_dipotong').chosen({
            width: '100%'
        });

        $('#nilai_pph').autoNumeric('init');
        $('#nilai_setor').autoNumeric('init');
    });

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        swal({
            type: 'warning',
            title: 'Warning !',
            text: 'This data will be saved !',
            allowOutsideClick: false,
            showCancelButton: true
        }, function(next) {
            if (next) {
                var formData = new FormData($('#frm-data')[0]);

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_penerimaan_pph_23',
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(result) {
                        if (result.status == '1') {
                            swal({
                                type: 'success',
                                title: 'Success !',
                                text: result.msg,
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                timer: 3000
                            }, function(lanjut) {
                                window.location.href = siteurl + active_controller;
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Failed !',
                                text: result.msg,
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    error: function(result) {
                        swal({
                            type: 'error',
                            title: 'Error !',
                            text: 'Please try again later !',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });
            }
        });
    });

    function get_num(nilai = null) {
        if (nilai !== '' && nilai !== null) {
            nilai = nilai.split(',').join('');
            nilai = parseFloat(nilai);
        } else {
            nilai = 0;
        }

        return nilai;
    }
</script>