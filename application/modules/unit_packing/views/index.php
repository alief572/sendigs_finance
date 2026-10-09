<?php
    $ENABLE_ADD     = has_permission('Master_Unit_Packing.Add');
    $ENABLE_MANAGE  = has_permission('Master_Unit_Packing.Manage');
    $ENABLE_VIEW    = has_permission('Master_Unit_Packing.View');
    $ENABLE_DELETE  = has_permission('Master_Unit_Packing.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/css/master-unit.css?v=' . (defined('FCPATH') && file_exists(FCPATH . 'assets/css/master-unit.css') ? filemtime(FCPATH . 'assets/css/master-unit.css') : '1.0.2')); ?>">

<main class="master-unit-page" id="master-unit-packing-page"
    data-save-url="<?= site_url('unit_packing/add'); ?>"
    data-delete-url="<?= site_url('unit_packing/hapus'); ?>"
    data-detail-url="<?= site_url('unit_packing/get_detail'); ?>">

    <header class="unit-page-header">
        <div>
            <h1>Satuan Kemasan (Unit Packing)</h1>
            <p>Kelola standar satuan kemasan barang dan logistik (seperti Box, Carton, Pallet, Roll, Pack).</p>
        </div>
        <?php if ($ENABLE_ADD) : ?>
            <button type="button" class="unit-button unit-button-primary" id="unit-add">
                <i class="fa fa-plus" aria-hidden="true"></i>
                <span>Tambah satuan packing</span>
            </button>
        <?php endif; ?>
    </header>

    <div class="unit-notice" id="unit-feedback" role="status" aria-live="polite" hidden></div>

    <section class="unit-panel" aria-labelledby="unit-list-title">
        <div class="unit-panel-header">
            <div class="unit-panel-heading">
                <span class="unit-panel-icon" aria-hidden="true"><i class="fa fa-archive"></i></span>
                <div>
                    <h2 id="unit-list-title">Daftar Satuan Packing</h2>
                    <p>Kode kemasan dan nama lengkap satuan packing terdaftar.</p>
                </div>
            </div>
            <div class="unit-toolbar">
                <label class="unit-search" for="unit-search">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <span class="sr-only">Cari kode atau nama packing</span>
                    <input type="search" id="unit-search" placeholder="Cari kode atau nama packing…" autocomplete="off">
                </label>
                <button type="button" class="unit-button unit-button-secondary unit-refresh" id="unit-refresh" aria-label="Muat ulang data" title="Muat ulang">
                    <i class="fa fa-refresh" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="unit-table-wrap">
            <table id="example1" class="table unit-table" aria-labelledby="unit-list-title">
                <thead>
                    <tr>
                        <th class="unit-th-num">No.</th>
                        <th class="unit-th-code">Kode Satuan</th>
                        <th class="unit-th-name">Nama Satuan Packing</th>
                        <th class="unit-th-action">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($results)) : $numb = 0; foreach ($results as $record) : $numb++; ?>
                        <tr id="row-unit-<?= $record->id; ?>" data-id="<?= $record->id; ?>">
                            <td class="unit-row-number"><?= $numb; ?></td>
                            <td class="unit-row-code">
                                <span class="unit-code-badge"><?= html_escape(strtoupper($record->code)); ?></span>
                            </td>
                            <td class="unit-row-name">
                                <?= html_escape(ucwords($record->nama)); ?>
                            </td>
                            <td class="unit-actions">
                                <?php if ($ENABLE_MANAGE) : ?>
                                    <button type="button" class="btn btn-warning unit-btn-action unit-btn-edit" title="Edit satuan packing"
                                        data-id="<?= $record->id; ?>"
                                        data-code="<?= html_escape($record->code); ?>"
                                        data-name="<?= html_escape($record->nama); ?>">
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                        <span class="sr-only">Edit</span>
                                    </button>
                                <?php endif; ?>
                                <?php if ($ENABLE_DELETE) : ?>
                                    <button type="button" class="btn btn-danger unit-btn-action unit-btn-delete" title="Hapus satuan packing"
                                        data-id="<?= $record->id; ?>"
                                        data-code="<?= html_escape(strtoupper($record->code)); ?>"
                                        data-name="<?= html_escape(ucwords($record->nama)); ?>">
                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                        <span class="sr-only">Hapus</span>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <p class="unit-footnote">
        <i class="fa fa-info-circle" aria-hidden="true"></i>
        Kode satuan packing digunakan untuk pengelompokan kemasan pada penerimaan barang, stok gudang, dan pengiriman.
    </p>
</main>

<div class="modal fade unit-modal" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="head_title" aria-describedby="unit-form-description">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup form">
                    <span aria-hidden="true">&times;</span>
                </button>
                <span class="unit-modal-icon" aria-hidden="true"><i class="fa fa-archive"></i></span>
                <h2 class="modal-title" id="head_title">Tambah Satuan Packing</h2>
                <p id="unit-form-description">Lengkapi kode singkatan dan nama satuan kemasan (packing).</p>
            </div>
            <form id="data_form" autocomplete="off" novalidate>
                <input type="hidden" id="id" name="id" value="">
                <div class="modal-body">
                    <div id="unit-form-feedback" class="unit-notice unit-notice-error" role="alert" hidden></div>
                    
                    <div class="unit-form-grid">
                        <div class="form-group">
                            <label for="code">Kode Satuan Packing <span class="unit-required">*</span></label>
                            <input type="text" class="form-control" id="code" name="code" required 
                                   placeholder="Contoh: BOX, CTN, PLT, ROLL, PACK" maxlength="20" style="text-transform: uppercase;">
                            <p class="unit-field-help">Singkatan ringkas untuk label kemasan dan dokumen pengiriman.</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="nama">Nama Lengkap Satuan Packing <span class="unit-required">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required 
                                   placeholder="Contoh: Box, Carton, Pallet, Roll, Pack">
                            <p class="unit-field-help">Nama lengkap jenis kemasan.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <span class="unit-form-note"><span class="unit-required">*</span> Wajib diisi</span>
                    <button type="button" class="unit-button unit-button-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="unit-button unit-button-primary" id="unit-save">
                        <i class="fa fa-check" aria-hidden="true"></i>
                        <span>Simpan satuan packing</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="<?= base_url('assets/js/unit_packing/index.js?v=' . (defined('FCPATH') && file_exists(FCPATH . 'assets/js/unit_packing/index.js') ? filemtime(FCPATH . 'assets/js/unit_packing/index.js') : '1.0.2')) ?>"></script>
