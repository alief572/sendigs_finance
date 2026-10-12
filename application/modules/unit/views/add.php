<?php
    $ENABLE_ADD     = has_permission('Master_Unit.Add');
    $ENABLE_MANAGE  = has_permission('Master_Unit.Manage');
    $ENABLE_VIEW    = has_permission('Master_Unit.View');
    $ENABLE_DELETE  = has_permission('Master_Unit.Delete');
	
    $id   = (!empty($header[0]->id)) ? $header[0]->id : '';
    $code = (!empty($header[0]->code)) ? $header[0]->code : '';
    $nama = (!empty($header[0]->nama)) ? $header[0]->nama : '';
?>
<link rel="stylesheet" href="<?= base_url('assets/css/master-unit.css?v=' . (defined('FCPATH') && file_exists(FCPATH . 'assets/css/master-unit.css') ? filemtime(FCPATH . 'assets/css/master-unit.css') : '1.0.2')); ?>">

<div class="master-unit-page" style="max-width: 680px; margin: 0 auto; padding-top: 10px;">
    <div class="unit-panel">
        <div class="unit-panel-header">
            <div class="unit-panel-heading">
                <span class="unit-panel-icon" aria-hidden="true"><i class="fa fa-cubes"></i></span>
                <div>
                    <h2><?= empty($id) ? 'Tambah Satuan' : 'Edit Satuan'; ?></h2>
                    <p>Lengkapi kode singkatan dan nama lengkap satuan pengukuran.</p>
                </div>
            </div>
            <a href="<?= site_url('unit'); ?>" class="unit-button unit-button-secondary">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                <span>Kembali</span>
            </a>
        </div>
        <form id="data_form" autocomplete="off" novalidate style="padding: 24px 28px;">
            <input type="hidden" id="id" name="id" value="<?= html_escape($id); ?>">
            <div id="unit-form-feedback" class="unit-notice unit-notice-error" role="alert" hidden></div>
            
            <div class="unit-form-grid">
                <div class="form-group">
                    <label for="code">Kode Satuan <span class="unit-required">*</span></label>
                    <input type="text" class="form-control" id="code" name="code" required 
                           placeholder="Contoh: PCS, KG, LTR, MTR" maxlength="20" style="text-transform: uppercase;"
                           value="<?= html_escape(strtoupper($code)); ?>">
                    <p class="unit-field-help">Singkatan ringkas untuk label, PO, dan invoice.</p>
                </div>
                
                <div class="form-group">
                    <label for="nama">Nama Lengkap Satuan <span class="unit-required">*</span></label>
                    <input type="text" class="form-control" id="nama" name="nama" required 
                           placeholder="Contoh: Pieces, Kilogram, Liter, Meter"
                           value="<?= html_escape(ucwords($nama)); ?>">
                    <p class="unit-field-help">Nama lengkap atau deskriptif satuan.</p>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; padding-top: 18px; border-top: 1px solid #e9eeeb;">
                <a href="<?= site_url('unit'); ?>" class="unit-button unit-button-secondary">Batal</a>
                <button type="submit" class="unit-button unit-button-primary" id="unit-save">
                    <i class="fa fa-check" aria-hidden="true"></i>
                    <span>Simpan satuan</span>
                </button>
            </div>
        </form>
    </div>
</div>
