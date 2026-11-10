<?php defined('BASEPATH') || exit('No direct script access allowed');
$auditIcon = function ($name) {
    $paths = array(
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'filter' => '<path d="M4 7h16M7 12h10M10 17h4"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/>',
        'download' => '<path d="M12 3v12m-4-4 4 4 4-4M4 16v4h16v-4"/>',
        'arrow' => '<path d="M5 12h14m-5-5 5 5-5 5"/>',
        'shield' => '<path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6z"/><path d="m8 12 3 3 5-6"/>'
    );
    return '<svg class="audit-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[$name] . '</svg>';
}; ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/audit-trail.css') ?>">
<div class="audit-trail" id="audit-trail" data-url="<?= htmlspecialchars(site_url('audit_trail'), ENT_QUOTES, 'UTF-8') ?>"
     data-from="<?= htmlspecialchars($date_from, ENT_QUOTES, 'UTF-8') ?>" data-to="<?= htmlspecialchars($date_to, ENT_QUOTES, 'UTF-8') ?>">
    <div class="audit-intro">
        <div><span class="audit-eyebrow">MONITOR AKTIVITAS</span><h2>Jejak aktivitas sistem</h2><p>Telusuri aktivitas pengguna, hasil proses, dan detail setiap kejadian.</p></div>
        <span class="audit-timezone"><?= $auditIcon('clock') ?> Waktu Indonesia Barat <strong>UTC+7</strong></span>
    </div>
    <div id="audit-error" class="alert alert-danger" role="alert" hidden></div>
    <div class="audit-shell"><form id="audit-filters" class="audit-card">
        <div class="audit-section-heading"><div class="audit-heading-icon"><?= $auditIcon('filter') ?></div><div><h3>Filter aktivitas</h3><p>Tentukan periode dan aktivitas yang ingin ditelusuri.</p></div></div>
        <div class="audit-filters">
        <div><label for="audit-from">Dari</label><input class="form-control" type="date" id="audit-from" value="<?= htmlspecialchars($date_from, ENT_QUOTES, 'UTF-8') ?>"></div>
        <div><label for="audit-to">Sampai</label><input class="form-control" type="date" id="audit-to" value="<?= htmlspecialchars($date_to, ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="audit-module-filter"><label for="audit-module">Modul</label><select class="form-control" id="audit-module">
            <option value="">Semua modul</option>
            <?php foreach ($modules as $module): ?>
                <option value="<?= htmlspecialchars($module, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($module, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select></div>
        <div><label for="audit-status">Status</label><select class="form-control" id="audit-status"><option value="">Semua status</option><option value="1">Sukses</option><option value="0">Gagal</option><option value="unknown">Tidak diketahui</option></select></div>
        <div class="audit-filter-actions"><button class="btn btn-primary" type="submit">Terapkan Filter <span class="audit-button-icon"><?= $auditIcon('arrow') ?></span></button><button class="btn btn-default" type="button" id="audit-reset">Reset</button></div>
        </div>
    </form></div>
    <div class="audit-shell"><div class="audit-card audit-results">
        <div class="audit-results-heading"><div><span class="audit-eyebrow">RIWAYAT SISTEM</span><h3>Aktivitas pengguna <span id="audit-result-count" class="audit-count" aria-live="polite">—</span></h3><p id="audit-active-filters">Memuat riwayat aktivitas...</p></div><span class="audit-readonly"><?= $auditIcon('shield') ?> Riwayat hanya baca</span></div>
        <div class="audit-toolbar">
            <div class="audit-search"><label class="sr-only" for="audit-search">Cari aktivitas</label><?= $auditIcon('search') ?><input class="form-control" type="search" id="audit-search" maxlength="500" placeholder="Cari pesan, user, modul, IP, SQL..."></div>
            <button type="button" class="btn btn-success" id="audit-export"><?= $auditIcon('download') ?> Export CSV <span class="audit-export-note">Lengkap</span></button>
        </div>
        <table id="audit-table" class="table table-striped table-hover" width="100%">
            <thead><tr><th>No</th><th>Waktu (WIB)</th><th>User</th><th>Modul</th><th>Aksi</th><th>Status</th><th>Pesan / Ringkasan</th><th>Detail</th></tr></thead>
        </table>
    </div></div>
</div>
<div class="modal fade audit-detail" id="audit-detail" tabindex="-1" role="dialog" aria-labelledby="audit-detail-title">
    <div class="modal-dialog modal-lg" role="document"><div class="modal-content">
        <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Tutup detail"><span aria-hidden="true">&times;</span></button><span class="audit-eyebrow">CATATAN AUDIT</span><h4 class="modal-title" id="audit-detail-title">Detail aktivitas</h4><p class="audit-modal-description">Identitas, hasil proses, dan konteks kejadian.</p></div>
        <div class="modal-body"><p id="audit-detail-state" role="status">Memuat detail...</p><div id="audit-detail-content" hidden>
            <dl class="audit-detail-grid">
                <?php foreach (array('id' => 'ID Log', 'created_at' => 'Waktu (WIB)', 'username' => 'User', 'created_by' => 'ID User', 'module' => 'Modul', 'methode' => 'Aksi', 'status_label' => 'Status', 'ip_address' => 'Alamat IP', 'platform' => 'Platform') as $field => $label): ?>
                    <div><dt><?= $label ?></dt><dd data-field="<?= $field ?>"></dd></div>
                <?php endforeach; ?>
            </dl>
            <?php foreach (array('message' => 'Pesan / Ringkasan', 'data' => 'Data', 'sql' => 'SQL', 'agent' => 'User Agent') as $field => $label): ?>
                <h5><?= $label ?></h5><pre data-field="<?= $field ?>"></pre>
            <?php endforeach; ?>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button></div>
    </div></div>
</div>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/audit-trail.js') ?>"></script>
