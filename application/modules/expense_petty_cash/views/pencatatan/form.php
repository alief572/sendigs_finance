<?php
$edit_mode = (isset($mode) && $mode === 'edit');
$header_id = ($edit_mode && isset($pencatatan->id)) ? $pencatatan->id : '';
$no_pencatatan = ($edit_mode && isset($pencatatan->no_pencatatan)) ? $pencatatan->no_pencatatan : 'Auto-generated';
$tanggal = ($edit_mode && isset($pencatatan->tanggal)) ? $pencatatan->tanggal : date('Y-m-d');
$company = ($edit_mode && isset($pencatatan->company)) ? $pencatatan->company : '';
$request_by = ($edit_mode && isset($pencatatan->request_by)) ? $pencatatan->request_by : '';
$keterangan = ($edit_mode && isset($pencatatan->keterangan)) ? $pencatatan->keterangan : '';
?>
<!-- Select2 CSS -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">

<!-- Budget Info Row - 3 small-box AdminLTE -->
<div class="row">
    <div class="col-lg-4 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3 id="budget-display"><?= isset($budget_info->budget) ? number_format($budget_info->budget, 0, ',', '.') : '0' ?></h3>
                <p>Budget</p>
            </div>
            <div class="icon">
                <i class="fa fa-money"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3 id="budget-terpakai-display"><?= isset($budget_info->budget_terpakai) ? number_format($budget_info->budget_terpakai, 0, ',', '.') : '0' ?></h3>
                <p>Budget Terpakai</p>
            </div>
            <div class="icon">
                <i class="fa fa-credit-card"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <h3 id="sisa-budget-display"><?= isset($budget_info->sisa_budget) ? number_format($budget_info->sisa_budget, 0, ',', '.') : '0' ?></h3>
                <p>Sisa Budget</p>
            </div>
            <div class="icon">
                <i class="fa fa-balance-scale"></i>
            </div>
        </div>
    </div>
</div>

<!-- Form Pencatatan -->
<?= form_open_multipart('expense_petty_cash/save', array('id' => 'frm_pencatatan', 'name' => 'frm_pencatatan', 'role' => 'form')) ?>
<input type="hidden" id="pencatatan_id" name="id" value="<?= $header_id ?>">
<input type="hidden" id="petty_cash_id" name="petty_cash_id" value="<?= $petty_cash_id ?>">

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-pencil-square-o"></i>&nbsp;<?= $edit_mode ? 'Edit' : 'Tambah' ?> Pencatatan Petty Cash</h3>
    </div>
    <div class="box-body">
        <!-- Header Fields -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="no_pencatatan">No Pencatatan</label>
                    <input type="text" class="form-control" id="no_pencatatan" name="no_pencatatan" value="<?= $no_pencatatan ?>" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tanggal">Tanggal <b class="text-red">*</b></label>
                    <div class="input-group date">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <input type="text" class="form-control datepicker" id="tanggal" name="tanggal" value="<?= $tanggal ?>" autocomplete="off">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="company">Company <b class="text-red">*</b></label>
                    <select class="form-control select2" id="company" name="company" style="width: 100%;">
                        <option value="">-- Pilih Company --</option>
                        <option value="STM" <?= ($company === 'STM') ? 'selected' : '' ?>>STM</option>
                        <option value="VUCA" <?= ($company === 'VUCA') ? 'selected' : '' ?>>VUCA</option>
                        <option value="SUSTAIN" <?= ($company === 'SUSTAIN') ? 'selected' : '' ?>>SUSTAIN</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="request_by">Request By <b class="text-red">*</b></label>
                    <input type="text" class="form-control" id="request_by" name="request_by" value="<?= $request_by ?>" placeholder="Nama pemohon" maxlength="100">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Keterangan (opsional)"><?= $keterangan ?></textarea>
                </div>
            </div>
        </div>

        <hr>

        <!-- Detail Items Table -->
        <h4><i class="fa fa-list"></i>&nbsp;Detail Item Pengeluaran</h4>
        <div class="table-responsive">
            <table id="detail-table" class="table table-bordered table-striped">
                <thead>
                    <tr class="bg-primary">
                        <th width="40" class="text-center">No</th>
                        <th width="200">COA</th>
                        <th width="150">Pengeluaran</th>
                        <th width="150">Spesifikasi</th>
                        <th width="80" class="text-center">Jumlah</th>
                        <th width="130" class="text-center">Nominal</th>
                        <th width="130" class="text-center">Total</th>
                        <th width="180">Evidence</th>
                        <th width="60" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($edit_mode && isset($pencatatan->details) && !empty($pencatatan->details)) : ?>
                        <?php foreach ($pencatatan->details as $idx => $detail) : ?>
                            <tr data-row="<?= $idx ?>">
                                <td class="text-center row-number"><?= $idx + 1 ?></td>
                                <td>
                                    <select name="details[<?= $idx ?>][coa_code]" class="form-control select2 coa-select" style="width: 100%;">
                                        <option value="">-- Pilih COA --</option>
                                        <?php foreach ($coa_list as $coa) : ?>
                                            <option value="<?= $coa->coa_code ?>" data-pengeluaran="<?= htmlspecialchars($coa->jenis_pengeluaran) ?>" <?= ($detail->coa_code == $coa->coa_code) ? 'selected' : '' ?>>
                                                <?= $coa->coa_code ?> - <?= $coa->coa_nama ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="details[<?= $idx ?>][pengeluaran]" class="form-control pengeluaran-input" value="<?= htmlspecialchars($detail->pengeluaran) ?>" placeholder="Pengeluaran" maxlength="255">
                                </td>
                                <td>
                                    <input type="text" name="details[<?= $idx ?>][spesifikasi]" class="form-control" value="<?= htmlspecialchars($detail->spesifikasi) ?>" placeholder="Spesifikasi" maxlength="255">
                                </td>
                                <td>
                                    <input type="number" name="details[<?= $idx ?>][jumlah]" class="form-control text-center jumlah-input" value="<?= $detail->jumlah ?>" min="1" max="9999">
                                </td>
                                <td>
                                    <input type="text" name="details[<?= $idx ?>][nominal]" class="form-control text-right nominal-input" value="<?= number_format($detail->nominal, 0, ',', '.') ?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control text-right total-display" value="<?= number_format($detail->total, 0, ',', '.') ?>" readonly>
                                    <input type="hidden" name="details[<?= $idx ?>][total]" class="total-hidden" value="<?= $detail->total ?>">
                                </td>
                                <td>
                                    <div class="evidence-container" data-row="<?= $idx ?>">
                                        <?php if (isset($pencatatan->evidences[$detail->id]) && !empty($pencatatan->evidences[$detail->id])) : ?>
                                            <ul class="list-unstyled evidence-list">
                                                <?php foreach ($pencatatan->evidences[$detail->id] as $evidence) : ?>
                                                    <li class="evidence-item" data-id="<?= $evidence->id ?>">
                                                        <small>
                                                            <i class="fa fa-file"></i>
                                                            <span class="evidence-name" title="<?= htmlspecialchars($evidence->original_name) ?>"><?= htmlspecialchars(strlen($evidence->original_name) > 15 ? substr($evidence->original_name, 0, 15) . '...' : $evidence->original_name) ?></span>
                                                            <a href="javascript:void(0)" class="text-red btn-remove-evidence" data-id="<?= $evidence->id ?>" title="Hapus"><i class="fa fa-times"></i></a>
                                                        </small>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                        <input type="file" name="evidence_<?= $idx ?>[]" class="evidence-input" multiple accept=".png,.jpg,.pdf,.xlsx,.xls" style="display:none;">
                                        <button type="button" class="btn btn-xs btn-default btn-upload-evidence" title="Upload Evidence">
                                            <i class="fa fa-upload"></i> Upload
                                        </button>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm btn-remove-row" title="Hapus Baris"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <!-- Default empty row for create mode -->
                        <tr data-row="0">
                            <td class="text-center row-number">1</td>
                            <td>
                                <select name="details[0][coa_code]" class="form-control select2 coa-select" style="width: 100%;">
                                    <option value="">-- Pilih COA --</option>
                                    <?php foreach ($coa_list as $coa) : ?>
                                        <option value="<?= $coa->coa_code ?>" data-pengeluaran="<?= htmlspecialchars($coa->jenis_pengeluaran) ?>">
                                            <?= $coa->coa_code ?> - <?= $coa->coa_nama ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="details[0][pengeluaran]" class="form-control pengeluaran-input" value="" placeholder="Pengeluaran" maxlength="255">
                            </td>
                            <td>
                                <input type="text" name="details[0][spesifikasi]" class="form-control" value="" placeholder="Spesifikasi" maxlength="255">
                            </td>
                            <td>
                                <input type="number" name="details[0][jumlah]" class="form-control text-center jumlah-input" value="" min="1" max="9999">
                            </td>
                            <td>
                                <input type="text" name="details[0][nominal]" class="form-control text-right nominal-input" value="" placeholder="0">
                            </td>
                            <td>
                                <input type="text" class="form-control text-right total-display" value="0" readonly>
                                <input type="hidden" name="details[0][total]" class="total-hidden" value="0">
                            </td>
                            <td>
                                <div class="evidence-container" data-row="0">
                                    <input type="file" name="evidence_0[]" class="evidence-input" multiple accept=".png,.jpg,.pdf,.xlsx,.xls" style="display:none;">
                                    <button type="button" class="btn btn-xs btn-default btn-upload-evidence" title="Upload Evidence">
                                        <i class="fa fa-upload"></i> Upload
                                    </button>
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm btn-remove-row" title="Hapus Baris"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Tambah Baris Button -->
        <button type="button" class="btn btn-info btn-sm" id="btn-add-row">
            <i class="fa fa-plus"></i>&nbsp;Tambah Baris
        </button>

        <hr>

        <!-- Grand Total Display -->
        <div class="row">
            <div class="col-md-6 col-md-offset-6">
                <div class="callout callout-info" style="padding: 15px 20px;">
                    <h4 style="margin: 0; font-size: 20px; font-weight: bold;">
                        <i class="fa fa-calculator"></i>&nbsp;
                        Grand Total: <span id="grand-total-display">0</span>
                    </h4>
                </div>
            </div>
        </div>

        <!-- Warning: Grand Total > Sisa Budget -->
        <div class="row" id="budget-warning-container" style="display: none;">
            <div class="col-md-12">
                <div class="callout callout-danger">
                    <h4><i class="fa fa-exclamation-triangle"></i>&nbsp;Peringatan</h4>
                    <p>Grand Total melebihi Sisa Budget! Pengeluaran Anda melebihi sisa anggaran yang tersedia. Anda masih dapat menyimpan pencatatan ini.</p>
                </div>
            </div>
        </div>

        <hr>

        <!-- Simulasi Jurnal (Preview) -->
        <div id="jurnal-simulation-container">
            <h4><i class="fa fa-book"></i>&nbsp;Simulasi Jurnal</h4>
            <p class="text-muted" style="font-size: 12px; margin-bottom: 10px;">
                <i class="fa fa-info-circle"></i> Preview jurnal yang akan dibuat saat pencatatan disimpan. Data belum tersimpan sampai tombol Simpan ditekan.
            </p>

            <!-- Jurnal STM -->
            <div id="jurnal-stm-section">
                <label class="label label-primary" style="font-size: 12px; margin-bottom: 8px; display: inline-block;">
                    <i class="fa fa-building"></i> Jurnal Pencatatan STM
                </label>
                <div class="table-responsive">
                    <table class="table table-bordered table-condensed" style="font-size: 12px;">
                        <thead style="background: #f5f5f5;">
                            <tr>
                                <th width="100">Tanggal</th>
                                <th width="100">COA</th>
                                <th>Nama Account</th>
                                <th width="80">Company</th>
                                <th width="120" class="text-right">Debit</th>
                                <th width="120" class="text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody id="jurnal-stm-body">
                            <tr>
                                <td colspan="6" class="text-center text-muted" style="padding: 10px;"><em>Tidak ada data (Company bukan STM)</em></td>
                            </tr>
                        </tbody>
                        <tfoot style="background: #f9f9f9; font-weight: bold;">
                            <tr>
                                <td colspan="4" class="text-right">Balancing</td>
                                <td class="text-right" id="jurnal-stm-total-debit">-</td>
                                <td class="text-right" id="jurnal-stm-total-kredit">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Jurnal Inter-Company: Sisi VUCA/SUSTAIN -->
            <div id="jurnal-intercompany-section" style="margin-top: 15px;">
                <label class="label label-warning" style="font-size: 12px; margin-bottom: 8px; display: inline-block;">
                    <i class="fa fa-building"></i> Sisi <span class="jurnal-company-name">VUCA/SUSTAIN</span>
                </label>
                <div class="table-responsive">
                    <table class="table table-bordered table-condensed" style="font-size: 12px;">
                        <thead style="background: #f5f5f5;">
                            <tr>
                                <th width="100">Tanggal</th>
                                <th width="100">COA</th>
                                <th>Nama Account</th>
                                <th width="80">Company</th>
                                <th width="120" class="text-right">Debit</th>
                                <th width="120" class="text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody id="jurnal-company-body">
                            <tr>
                                <td colspan="6" class="text-center text-muted" style="padding: 10px;"><em>Tidak ada data (Company bukan VUCA/SUSTAIN)</em></td>
                            </tr>
                        </tbody>
                        <tfoot style="background: #f9f9f9; font-weight: bold;">
                            <tr>
                                <td colspan="4" class="text-right">Balancing</td>
                                <td class="text-right" id="jurnal-company-total-debit">-</td>
                                <td class="text-right" id="jurnal-company-total-kredit">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Sisi STM (inter-company) -->
                <label class="label label-info" style="font-size: 12px; margin-bottom: 8px; display: inline-block; margin-top: 10px;">
                    <i class="fa fa-building"></i> Sisi STM (Inter-Company)
                </label>
                <div class="table-responsive">
                    <table class="table table-bordered table-condensed" style="font-size: 12px;">
                        <thead style="background: #f5f5f5;">
                            <tr>
                                <th width="100">Tanggal</th>
                                <th width="100">COA</th>
                                <th>Nama Account</th>
                                <th width="80">Company</th>
                                <th width="120" class="text-right">Debit</th>
                                <th width="120" class="text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody id="jurnal-stm-side-body">
                            <tr>
                                <td colspan="6" class="text-center text-muted" style="padding: 10px;"><em>Tidak ada data (Company bukan VUCA/SUSTAIN)</em></td>
                            </tr>
                        </tbody>
                        <tfoot style="background: #f9f9f9; font-weight: bold;">
                            <tr>
                                <td colspan="4" class="text-right">Balancing</td>
                                <td class="text-right" id="jurnal-stm-side-total-debit">-</td>
                                <td class="text-right" id="jurnal-stm-side-total-kredit">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <button type="button" class="btn btn-success" id="btn-save">
            <i class="fa fa-save"></i>&nbsp;Simpan
        </button>
        <a href="<?= site_url('expense_petty_cash') ?>" class="btn btn-warning">
            <i class="fa fa-reply"></i>&nbsp;Batal
        </a>
    </div>
</div>
<?= form_close() ?>

<!-- Hidden COA options template for JavaScript dynamic row creation -->
<script type="text/template" id="coa-options-template">
    <option value="">-- Pilih COA --</option>
    <?php foreach ($coa_list as $coa) : ?>
        <option value="<?= $coa->coa_code ?>" data-pengeluaran="<?= htmlspecialchars($coa->jenis_pengeluaran) ?>">
            <?= $coa->coa_code ?> - <?= $coa->coa_nama ?>
        </option>
    <?php endforeach; ?>
</script>

<!-- Select2 JS -->
<script src="<?= base_url('assets/plugins/select2/select2.min.js') ?>"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- FORM_DATA must be defined BEFORE form_pencatatan.js loads -->
<script>
    var FORM_DATA = {
        mode: '<?= $mode ?>',
        petty_cash_id: <?= json_encode($petty_cash_id) ?>,
        budget_info: <?= json_encode($budget_info) ?>,
        coa_list: <?= json_encode($coa_list) ?>,
        pencatatan: <?= isset($pencatatan) ? json_encode($pencatatan) : 'null' ?>,
        base_url: '<?= site_url('expense_petty_cash/') ?>'
    };
</script>

<!-- Form Pencatatan JS (dynamic rows, calculations, uploads, validation) -->
<script src="<?= base_url('assets/js/modules/expense_petty_cash/form_pencatatan.js') ?>"></script>