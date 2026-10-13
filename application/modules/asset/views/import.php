<?php if ($ENABLE_ADD && $import_ready): ?>
<div class="modal fade" id="AssetImportModal" tabindex="-1" role="dialog" aria-labelledby="asset-import-title"
     data-preview-url="<?= html_escape(site_url('asset/preview_import')); ?>"
     data-save-url="<?= html_escape(site_url('asset/save_import')); ?>"
     data-nonce="<?= html_escape($import_nonce); ?>">
    <div class="modal-dialog modal-lg" role="document" style="width:95%;max-width:1200px">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="asset-import-title">Import Excel Asset</h4>
            </div>
            <div class="modal-body">
                <p>Download format, isi sheet <strong>Data Asset</strong>, lalu upload untuk diperiksa. Semua baris harus valid sebelum disimpan.</p>
                <p class="text-muted">Maksimal 5 MB, 500 baris, dan 1.000 unit. Nilai perolehan diisi <strong>per unit</strong>. Qty 5 × Rp3.000.000 menghasilkan lima asset, masing-masing Rp3.000.000. Department boleh kosong.</p>
                <label for="asset-import-file">File Excel (.xlsx)</label>
                <input id="asset-import-file" type="file" accept=".xlsx" class="form-control">
                <button id="asset-import-preview" type="button" class="btn btn-primary" style="margin-top:10px">Periksa Preview</button>
                <div id="asset-import-message" role="status" aria-live="polite" style="margin-top:15px"></div>
                <div id="asset-import-summary"></div>
                <div id="asset-import-errors" style="max-height:250px;overflow:auto"></div>
                <div class="table-responsive" style="max-height:400px;overflow:auto">
                    <table id="asset-import-rows" class="table table-striped table-bordered" style="display:none">
                        <thead><tr><th>Baris</th><th>Nama Asset</th><th>Kategori</th><th>Company</th><th>Department</th><th>Tanggal</th><th>Qty</th><th>Nilai per Unit</th><th>Masa Manfaat</th><th>Penyusutan/Bulan per Unit</th><th>Status</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted">Preview berlaku 30 menit.</span>
                <button id="asset-import-save" type="button" class="btn btn-success" disabled>Simpan Semua Asset</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/js/asset/import.js'); ?>"></script>
<?php endif; ?>
