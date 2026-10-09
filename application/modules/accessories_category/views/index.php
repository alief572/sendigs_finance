<?php
    $ENABLE_ADD     = has_permission('Category_Stok.Add');
    $ENABLE_MANAGE  = has_permission('Category_Stok.Manage');
    $ENABLE_VIEW    = has_permission('Category_Stok.View');
    $ENABLE_DELETE  = has_permission('Category_Stok.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/css/accessories-category.css?v=' . (defined('FCPATH') && file_exists(FCPATH . 'assets/css/accessories-category.css') ? filemtime(FCPATH . 'assets/css/accessories-category.css') : '1.0.0')); ?>">

<main class="accessories-category-page" id="accessories-category-page"
    data-save-url="<?= site_url('accessories_category/add'); ?>"
    data-delete-url="<?= site_url('accessories_category/delete'); ?>">

    <header class="category-page-header">
        <div>
            <h1>Kategori Stok Aksesoris</h1>
            <p>Kelola klasifikasi dan kategori stok aksesoris untuk pengelompokan material inventaris.</p>
        </div>
        <?php if ($ENABLE_ADD) : ?>
            <button type="button" class="category-button category-button-primary" id="category-add">
                <i class="fa fa-plus" aria-hidden="true"></i>
                <span>Tambah kategori</span>
            </button>
        <?php endif; ?>
    </header>

    <div class="category-notice" id="category-feedback" role="status" aria-live="polite" hidden></div>

    <section class="category-panel" aria-labelledby="category-list-title">
        <div class="category-panel-header">
            <div class="category-panel-heading">
                <span class="category-panel-icon" aria-hidden="true"><i class="fa fa-tags"></i></span>
                <div>
                    <h2 id="category-list-title">Daftar Kategori Stok</h2>
                    <p>Kode ID, nama kategori stok, dan deskripsi kategori terdaftar.</p>
                </div>
            </div>
            <div class="category-toolbar">
                <label class="category-search" for="category-search">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <span class="sr-only">Cari kategori stok</span>
                    <input type="search" id="category-search" placeholder="Cari kategori stok…" autocomplete="off">
                </label>
                <button type="button" class="category-button category-button-secondary category-refresh" id="category-refresh" aria-label="Muat ulang data" title="Muat ulang">
                    <i class="fa fa-refresh" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="category-table-wrap">
            <table id="example1" class="table category-table" aria-labelledby="category-list-title">
                <thead>
                    <tr>
                        <th class="category-th-num">No.</th>
                        <th class="category-th-id">ID</th>
                        <th class="category-th-name">Nama Kategori</th>
                        <th class="category-th-desc">Deskripsi</th>
                        <th class="category-th-action">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($result)) : $numb = 0; foreach ($result as $record) : $numb++; ?>
                        <tr id="row-category-<?= $record->id; ?>" data-id="<?= $record->id; ?>">
                            <td class="category-row-number"><?= $numb; ?></td>
                            <td class="category-row-id">
                                <span class="category-code-badge"><?= html_escape(strtoupper($record->id)); ?></span>
                            </td>
                            <td class="category-row-name">
                                <?= html_escape(ucwords($record->nm_category)); ?>
                            </td>
                            <td class="category-row-desc">
                                <?= !empty($record->description) ? html_escape($record->description) : '<span style="color:#a8b5ae; font-style:italic;">Tidak ada deskripsi</span>'; ?>
                            </td>
                            <td class="category-actions">
                                <?php if ($ENABLE_MANAGE) : ?>
                                    <button type="button" class="btn btn-warning category-btn-action category-btn-edit" title="Edit kategori"
                                        data-id="<?= $record->id; ?>"
                                        data-name="<?= html_escape($record->nm_category); ?>"
                                        data-description="<?= html_escape($record->description); ?>">
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                        <span class="sr-only">Edit</span>
                                    </button>
                                <?php endif; ?>
                                <?php if ($ENABLE_DELETE) : ?>
                                    <button type="button" class="btn btn-danger category-btn-action category-btn-delete" title="Hapus kategori"
                                        data-id="<?= $record->id; ?>"
                                        data-name="<?= html_escape(ucwords($record->nm_category)); ?>">
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

    <p class="category-footnote">
        <i class="fa fa-info-circle" aria-hidden="true"></i>
        Kategori stok digunakan untuk pengelompokan barang dan pelaporan stok inventaris aksesoris.
    </p>
</main>

<div class="modal fade category-modal" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="head_title" aria-describedby="category-form-description">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup form">
                    <span aria-hidden="true">&times;</span>
                </button>
                <span class="category-modal-icon" aria-hidden="true"><i class="fa fa-tags"></i></span>
                <h2 class="modal-title" id="head_title">Tambah Kategori Stok</h2>
                <p id="category-form-description">Lengkapi nama kategori dan deskripsi kategori stok aksesoris.</p>
            </div>
            <form id="data_form" autocomplete="off" novalidate>
                <input type="hidden" id="id" name="id" value="">
                <div class="modal-body">
                    <div id="category-form-feedback" class="category-notice category-notice-error" role="alert" hidden></div>
                    
                    <div class="category-form-grid">
                        <div class="form-group">
                            <label for="nm_category">Nama Kategori <span class="category-required">*</span></label>
                            <input type="text" class="form-control" id="nm_category" name="nm_category" required 
                                   placeholder="Contoh: Baut & Mur, Kancing, Ritsleting, Hangtag" maxlength="100">
                            <p class="category-field-help">Nama pengelompokan jenis kategori aksesoris.</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                      placeholder="Keterangan tambahan mengenai kelompok kategori ini..."></textarea>
                            <p class="category-field-help">Catatan atau spesifikasi lingkup barang dalam kategori.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <span class="category-form-note"><span class="category-required">*</span> Wajib diisi</span>
                    <button type="button" class="category-button category-button-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="category-button category-button-primary" id="category-save">
                        <i class="fa fa-check" aria-hidden="true"></i>
                        <span>Simpan kategori</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="<?= base_url('assets/js/accessories_category/index.js?v=' . (defined('FCPATH') && file_exists(FCPATH . 'assets/js/accessories_category/index.js') ? filemtime(FCPATH . 'assets/js/accessories_category/index.js') : '1.0.0')) ?>"></script>
