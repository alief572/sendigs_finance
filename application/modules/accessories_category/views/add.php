<?php
    $id = (!empty($listData[0]->id)) ? $listData[0]->id : '';
    $nm_category = (!empty($listData[0]->nm_category)) ? $listData[0]->nm_category : '';
    $description = (!empty($listData[0]->description)) ? $listData[0]->description : '';
?>
<link rel="stylesheet" href="<?= base_url('assets/css/accessories-category.css?v=' . (defined('FCPATH') && file_exists(FCPATH . 'assets/css/accessories-category.css') ? filemtime(FCPATH . 'assets/css/accessories-category.css') : '1.0.0')); ?>">

<div class="accessories-category-page" style="max-width: 680px; margin: 0 auto; padding-top: 10px;">
    <div class="category-panel">
        <div class="category-panel-header">
            <div class="category-panel-heading">
                <span class="category-panel-icon" aria-hidden="true"><i class="fa fa-tags"></i></span>
                <div>
                    <h2><?= empty($id) ? 'Tambah Kategori Stok' : 'Edit Kategori Stok'; ?></h2>
                    <p>Lengkapi nama kategori dan deskripsi kategori stok aksesoris.</p>
                </div>
            </div>
            <a href="<?= site_url('accessories_category'); ?>" class="category-button category-button-secondary">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                <span>Kembali</span>
            </a>
        </div>
        <form id="data_form" autocomplete="off" novalidate style="padding: 24px 28px;">
            <input type="hidden" id="id" name="id" value="<?= html_escape($id); ?>">
            <div id="category-form-feedback" class="category-notice category-notice-error" role="alert" hidden></div>
            
            <div class="category-form-grid">
                <div class="form-group">
                    <label for="nm_category">Nama Kategori <span class="category-required">*</span></label>
                    <input type="text" class="form-control" id="nm_category" name="nm_category" required 
                           placeholder="Contoh: Baut & Mur, Kancing, Ritsleting, Hangtag" maxlength="100"
                           value="<?= html_escape($nm_category); ?>">
                    <p class="category-field-help">Nama pengelompokan jenis kategori aksesoris.</p>
                </div>
                
                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="3"
                              placeholder="Keterangan tambahan mengenai kelompok kategori ini..."><?= html_escape($description); ?></textarea>
                    <p class="category-field-help">Catatan atau spesifikasi lingkup barang dalam kategori.</p>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; padding-top: 18px; border-top: 1px solid #e9eeeb;">
                <a href="<?= site_url('accessories_category'); ?>" class="category-button category-button-secondary">Batal</a>
                <button type="submit" class="category-button category-button-primary" id="category-save">
                    <i class="fa fa-check" aria-hidden="true"></i>
                    <span>Simpan kategori</span>
                </button>
            </div>
        </form>
    </div>
</div>
