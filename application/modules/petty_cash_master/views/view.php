<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Detail Master Petty Cash</h3>
    </div>
    <div class="box-body">
        <input type="hidden" id="id" name="id" value="<?= isset($data->id) ? $data->id : '' ?>">

        <!-- Header Fields -->
        <div class="form-horizontal">
            <div class="form-group">
                <label for="nama" class="col-sm-2 control-label">Nama</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="nama" value="<?= isset($data->nama) ? $data->nama : '' ?>" disabled>
                </div>
                <label for="keterangan" class="col-sm-2 control-label">Keterangan</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="keterangan" value="<?= isset($data->keterangan) ? $data->keterangan : '' ?>" disabled>
                </div>
            </div>

        </div>

        <!-- Detail Table -->
        <div class="table-responsive" style="margin-top: 15px;">
            <table class="table table-bordered table-striped" id="detail-table">
                <thead>
                    <tr>
                        <th width="40" class="text-center">No</th>
                        <th width="250">COA</th>
                        <th>Jenis Pengeluaran</th>
                        <th width="180" class="text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($details) && is_array($details)) : ?>
                        <?php $no = 1;
                        foreach ($details as $detail) : ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td>
                                    <select class="form-control select2" disabled>
                                        <option value="">-- Pilih COA --</option>
                                        <?php if (isset($coa_list) && is_array($coa_list)) : ?>
                                            <?php foreach ($coa_list as $code => $label) : ?>
                                                <option value="<?= $code ?>" <?= ($detail->coa_code == $code) ? 'selected' : '' ?>><?= $label ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control" value="<?= $detail->jenis_pengeluaran ?>" disabled>
                                </td>
                                <td class="text-right">
                                    <input type="text" class="form-control text-right" value="<?= number_format($detail->nominal, 0, ',', '.') ?>" disabled>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Total Budget Highlighted Box -->
        <div class="row" style="margin-top: 15px;">
            <div class="col-sm-offset-6 col-sm-6">
                <div class="well well-sm" style="background-color: #d9edf7; border-color: #bce8f1; padding: 15px; text-align: right;">
                    <h4 style="margin: 0; color: #31708f;">
                        <strong>Total Budget: Rp <?= isset($data->total_budget) ? number_format($data->total_budget, 0, ',', '.') : '0' ?></strong>
                    </h4>
                </div>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="box-footer">
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <a class="btn btn-warning btn-sm" onclick="cancel()"><i class="fa fa-reply"></i>&nbsp;Tutup</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // Initialize Select2 on disabled dropdowns for display purposes
    $('.select2').select2();

    /**
     * Close the view form and clear the form-data container
     */
    function cancel() {
        $('#form-data').html('');
    }
</script>