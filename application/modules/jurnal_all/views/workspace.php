<?php
$e=function($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');};
$shortTitles=['INV'=>'Invoicing','RCV'=>'Penerimaan','PAY'=>'Pembayaran','EXP'=>'Expense Report','PCS'=>'Pencatatan Petty Cash'];
?>
<link rel="stylesheet" href="<?= base_url('assets/dist/sweetalert2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/jurnal-all.css') ?>?v=<?= filemtime(FCPATH.'assets/css/jurnal-all.css') ?>">
<section id="journal-workspace" data-base="<?= $e(site_url($posted?'report_jurnal_all':'jurnal_all')) ?>" data-posted="<?= $posted?'1':'0' ?>" data-tab="<?= $e($initialTab) ?>" data-manage="<?= $canManage?'1':'0' ?>">
    <p class="ja-intro"><?= $posted?'Telusuri jurnal yang sudah diposting dan periksa detail pencatatannya.':'Periksa transaksi yang menunggu posting ke accounting.' ?></p>
    <div id="ja-message" class="alert alert-danger" role="alert" hidden></div>
    <?php if(!$posted): ?><div class="ja-cards" aria-label="Ringkasan jurnal pending">
        <?php foreach($tabs as $key=>$title): ?><button type="button" class="ja-card" aria-pressed="false" aria-label="<?= $e($title) ?>: buka antrean pending" data-tab="<?= $e($key) ?>"><span class="ja-card-title"><?= $e($shortTitles[$key]) ?></span><span class="ja-card-count"><strong data-count>&mdash;</strong><span>belum posting</span></span><small data-amount>Memuat ringkasan&hellip;</small><small data-age></small></button><?php endforeach; ?>
    </div><p class="ja-hint">Ringkasan mengikuti filter setiap tab. Pilih sumber untuk melihat transaksinya.</p><?php endif; ?>
    <div class="ja-tabs" role="tablist" aria-label="Jenis jurnal">
        <?php foreach($tabs as $key=>$title): ?><button type="button" id="ja-tab-<?= $e($key) ?>" class="ja-tab" data-tab="<?= $e($key) ?>" data-title="<?= $e($title) ?>" aria-label="<?= $e($title) ?>" role="tab" aria-selected="false" aria-controls="ja-panel" tabindex="-1"><?= $e($shortTitles[$key]) ?><span data-badge></span></button><?php endforeach; ?>
    </div>
    <div class="box ja-panel" id="ja-panel" role="tabpanel">
        <div id="ja-loading" class="ja-loading" role="status" aria-live="polite" aria-atomic="true" hidden>
            <span class="ja-spinner" aria-hidden="true"></span>
            <strong id="ja-loading-text">Memuat data jurnal…</strong>
            <span>Mohon tunggu, data sedang disiapkan.</span>
        </div>
        <div class="box-body">
            <div class="ja-section-head">
                <div><h2 id="ja-section-title"><?= $e($tabs[$initialTab]) ?></h2><p><?= $posted?'Riwayat jurnal sesuai filter yang dipilih.':'Pilih transaksi untuk memeriksa detail sebelum posting.' ?></p></div>
                <button type="button" id="ja-export" class="btn btn-default ja-export" title="Unduh detail COA seluruh hasil filter dan pencarian" disabled><i class="fa fa-download" aria-hidden="true"></i> Export Excel</button>
            </div>
            <form id="ja-filters" class="ja-filters">
                <label>Tahun<select name="year" class="form-control"></select></label>
                <label>Bulan<select name="month" class="form-control"><option value="">Semua Bulan</option><?php foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i=>$month): ?><option value="<?= $i+1 ?>"><?= $month ?></option><?php endforeach; ?></select></label>
                <label>Company<select name="company" class="form-control"><option value="">Semua Company</option></select></label>
                <label id="ja-category">Kategori Transaksi<select name="category" class="form-control"><option value="">Semua Kategori</option></select></label>
                <div class="ja-buttons"><button type="submit" id="ja-apply" class="btn btn-primary">Terapkan filter</button><button type="button" id="ja-reset" class="btn btn-default">Reset</button></div>
            </form>
            <p id="ja-invoice-period-hint" class="ja-hint" hidden>Filter Invoicing mencocokkan tanggal baris jurnal. Satu invoice tetap satu transaksi lengkap; Tgl Jurnal dan Tertua memakai tanggal pertamanya. Pilih Semua Tahun untuk melihat seluruh periode.</p>
            <p class="ja-hint">Filter tersimpan per tab. Ekspor mencakup detail COA seluruh hasil filter dan pencarian.</p>
            <?php if($posted): ?><p class="ja-hint">No. Jurnal Accounting berasal dari hasil posting yang terkonfirmasi. Metadata histori yang tidak tersedia ditampilkan -.</p><?php endif; ?>
            <div class="ja-table-tools"><label>Tampilkan <select id="ja-length" class="form-control"><option>10</option><option>25</option><option>50</option></select> baris</label><label class="ja-search-label"><span class="sr-only">Cari transaksi, referensi, customer, project, company atau COA</span><i class="fa fa-search" aria-hidden="true"></i><input id="ja-search" class="form-control" type="search" placeholder="Cari transaksi, referensi, customer, COA&hellip;"></label></div>
            <div class="table-responsive ja-table-scroll" role="region" aria-label="Daftar transaksi jurnal, geser untuk kolom lainnya" tabindex="0"><table class="table ja-table" id="ja-table"><caption class="sr-only">Daftar jurnal</caption><thead></thead><tbody></tbody><tfoot></tfoot></table></div>
            <div class="ja-footer"><span id="ja-count" role="status" aria-live="polite"></span><nav aria-label="Halaman daftar jurnal"><button type="button" id="ja-prev" class="btn btn-default" disabled>Sebelumnya</button><span id="ja-page"></span><button type="button" id="ja-next" class="btn btn-default" disabled>Berikutnya</button></nav></div>
        </div>
    </div>
    <div class="modal fade" id="ja-modal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="ja-modal-title">
        <div class="modal-dialog modal-lg"><form class="modal-content" id="ja-action-form">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">×</span></button><h2 class="modal-title" id="ja-modal-title">Detail jurnal</h2></div>
            <div class="modal-body"><div id="ja-modal-message" class="alert alert-danger" role="alert" hidden></div><div id="ja-detail"></div><label id="ja-reason-wrap" hidden>Alasan Revisi<textarea id="ja-reason" class="form-control" maxlength="2000" rows="3"></textarea></label></div>
            <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button><button type="button" id="ja-revise" class="btn btn-warning" hidden>Revisi</button><button type="submit" id="ja-post" class="btn btn-primary" hidden>Posting</button></div>
        </form></div>
    </div>
</section>
<script src="<?= base_url('assets/dist/sweetalert2.min.js') ?>"></script>
<script src="<?= base_url('assets/js/jurnal-all.js') ?>?v=<?= filemtime(FCPATH.'assets/js/jurnal-all.js') ?>"></script>
