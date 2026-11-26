<?php
$base = site_url('follow_up_piutang');
$bulan = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css') ?>">
<style>
    /* Tampilan mengikuti specs/docs/review-monitoring-piutang/mockup; warna pill memakai varian lebih gelap agar teks putih lolos kontras 4.5:1. */
    .fu-page { --line: #dfe3e8; --row: #f7f9fb; --muted: #5b6670; --blue: #1d5f93; --green: #067647; --orange: #b54708; --red: #b42318; --lock: #eef0f2; }
    .fu-page :focus-visible { outline: 2px solid #1d4ed8; outline-offset: 1px; }
    .fu-panel { background: #fff; border-top: 3px solid #3f8bba; border-radius: 3px; padding: 14px; margin-bottom: 16px; box-shadow: 0 1px 1px rgba(0, 0, 0, .08); }
    .fu-tabs { display: flex; flex-wrap: wrap; gap: 2px; border-bottom: 1px solid var(--line); margin-bottom: 12px; }
    .fu-tabs button { border: 1px solid transparent; border-bottom: 0; background: none; padding: 7px 14px; cursor: pointer; color: var(--muted); border-radius: 3px 3px 0 0; }
    .fu-tabs button.on { border-color: var(--line); background: #fff; color: #333a40; font-weight: 600; margin-bottom: -1px; }
    .fu-tabs .cnt { display: inline-block; min-width: 20px; padding: 0 6px; border-radius: 9px; background: var(--line); color: #333a40; font-size: 11.5px; font-weight: 700; text-align: center; margin-left: 4px; }
    .fu-tabs .cnt.red { background: var(--red); color: #fff; }
    .fu-tabs button.on .cnt { background: var(--blue); color: #fff; }
    .fu-groups { display: flex; flex-wrap: wrap; gap: 2px; border-bottom: 2px solid var(--blue); margin-bottom: 6px; }
    .fu-groups button { border: 0; background: none; padding: 9px 22px; cursor: pointer; border-radius: 3px 3px 0 0; font-weight: 700; color: var(--muted); }
    .fu-groups button.on { background: var(--blue); color: #fff; }
    .fu-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 16px; }
    .fu-card { display: flex; flex-direction: column; gap: 4px; min-width: 0; text-align: left; background: #fff; border: 1px solid var(--line); border-top: 4px solid var(--blue); border-radius: 3px; padding: 12px 14px; cursor: pointer; font: inherit; }
    .fu-card.on { box-shadow: 0 0 0 2px #00c0ef; }
    .fu-card.new { border-top-color: #8a949c; } .fu-card.ora { border-top-color: #f39c12; } .fu-card.grn { border-top-color: #00a65a; } .fu-card.red { border-top-color: var(--red); } .fu-card.grey { border-top-color: #555; }
    .fu-card.ora .n { color: var(--orange); } .fu-card.red .n { color: var(--red); }
    .fu-card .t { display: block; font-size: 12px; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; color: var(--muted); }
    .fu-card .n { display: block; font-size: 20px; font-weight: 700; font-variant-numeric: tabular-nums; }
    .fu-card .v { display: block; font-size: 12.5px; color: var(--muted); font-variant-numeric: tabular-nums; }
    .fu-tbl.wide th, .fu-tbl.wide td { font-size: 12.5px; }
    .fu-tbl .wrapt { white-space: normal; min-width: 150px; max-width: 260px; }
    .fu-tbl .fk { font-size: 12px; } .fu-tbl .fk b { color: var(--muted); font-weight: 600; }
    .fu-tbl .kt .kt-f { display: none; } .fu-tbl .kt.open .kt-f, .fu-tbl.kt-all .kt-f { display: inline; } .fu-tbl .kt.open .kt-s, .fu-tbl.kt-all .kt-s { display: none; }
    /* Kolom kunci (mockup pinned: 'left'): No dan Invoice tidak bisa dipindah dan tetap terlihat saat tabel digeser ke samping */
    .fu-page .fu-tbl .fu-pin { position: sticky; left: 0; z-index: 2; background-color: #fff; }
    .fu-page .fu-tbl .fu-pinno { width: 52px; min-width: 52px; }
    .fu-page .fu-tbl .fu-pin2 { left: var(--kol-pin, 52px); box-shadow: 1px 0 0 #dfe3e8; }
    .fu-page .fu-tbl.wide .fu-pin2 { min-width: 250px; }
    .fu-page .fu-tbl tbody tr:nth-child(odd) > .fu-pin, .fu-page .fu-tbl tfoot .fu-pin { background-color: var(--row); }
    .fu-page .fu-tbl tbody tr.bm-row > td.fu-pin { background-color: #fff; background-image: linear-gradient(rgba(180, 35, 24, .07), rgba(180, 35, 24, .07)); }
    .fu-tbl .more { border: 0; background: none; color: var(--blue); cursor: pointer; padding: 0; font-size: 12px; }
    .fu-pill.bm { background: var(--red); }
    .fu-tbl tr.bm-row td { background-color: rgba(180, 35, 24, .07); }
    .fu-tabhint { margin: -4px 0 12px; padding: 7px 12px; background: rgba(47, 127, 184, .10); border-left: 3px solid var(--blue); font-size: 13px; }
    .fu-filters { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; margin-bottom: 12px; }
    .fu-filters .sp { flex: 1; }
    .fu-page .fl { display: flex; flex-direction: column; gap: 3px; font-size: 12px; color: var(--muted); font-weight: 400; margin: 0; }
    .fu-page .fl select, .fu-page .fl input { min-width: 170px; }
    .fu-page .searchbox input { width: 280px; }
    .fu-page select, .fu-page input[type=text], .fu-page input[type=tel], .fu-page input[type=date], .fu-page input[type=search], .fu-page input[type=file] {
        background: #fff; border: 1px solid #c8d0d8; border-radius: 3px; padding: 5px 7px; width: 100%; font-size: 13px; color: #333a40; }
    .fu-page .fl select, .fu-page .fl input[type=search], .fu-page .fl input[type=text] { width: auto; }
    .fu-page .searchbox input { width: 280px; }
    .fu-page input:disabled, .fu-page select:disabled { background: var(--lock); }
    .fu-btn { border: 0; border-radius: 3px; padding: 6px 12px; cursor: pointer; color: #fff; background: var(--green); font-weight: 600; font-size: 13px; }
    .fu-btn.bl { background: var(--blue); } .fu-btn.r { background: var(--red); }
    .fu-btn.g { background: transparent; color: #333a40; border: 1px solid #c8d0d8; }
    .fu-btn.sm { padding: 3px 8px; font-size: 12px; }
    .fu-btn[disabled] { opacity: .5; cursor: not-allowed; }
    @media (pointer: coarse) { .fu-btn.sm, .fu-page select, .fu-page input { min-height: 44px; } }
    .okmsg { background: rgba(6, 118, 71, .10); border: 1px solid rgba(6, 118, 71, .45); border-radius: 3px; padding: 8px 12px; margin-bottom: 10px; font-size: 13px; }
    .okmsg.danger { background: rgba(180, 35, 24, .08); border-color: rgba(180, 35, 24, .45); }
    .linkbtn { background: none; border: 0; padding: 0; color: var(--blue); cursor: pointer; font-size: 13px; font-weight: 600; }
    table.fu-tbl { border-collapse: separate; border-spacing: 0; width: 100%; min-width: 860px; }
    .fu-tbl th, .fu-tbl td { padding: 8px 9px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: top; }
    .fu-tbl th { font-weight: 700; font-size: 13px; white-space: nowrap; border-top: 0; }
    .fu-tbl tbody tr:nth-child(odd) { background: var(--row); }
    .fu-tbl td.num, .fu-tbl th.num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .fu-tbl .no { white-space: nowrap; font-weight: 600; }
    .fu-tbl .sub { display: block; font-size: 12px; color: var(--muted); font-weight: 400; }
    .fu-tbl .asof { font-size: 12.5px; color: var(--muted); }
    .fu-pill { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11.5px; font-weight: 700; color: #fff; white-space: nowrap; }
    .fu-pill.lancar { background: var(--green); } .fu-pill.potensi { background: var(--orange); } .fu-pill.macet { background: var(--red); }
    .fu-pill.st-belum_kirim { background: #5f6b76; } .fu-pill.st-dikirim { background: var(--blue); } .fu-pill.st-diterima { background: #0e7490; }
    .fu-pill.st-plan_bayar { background: var(--green); } .fu-pill.st-close { background: #4b5563; }
    .fu-pill.baru { background: var(--orange); margin-top: 3px; }
    .fu-pill.info { background: var(--blue); } .fu-pill.warn { background: var(--orange); }
    .umur { font-weight: 700; font-variant-numeric: tabular-nums; }
    .umur.lancar { color: var(--green); } .umur.potensi { color: var(--orange); } .umur.macet { color: var(--red); }
    .late { color: var(--red); }
    #fu-table.fu-tbl > tbody > tr.fu-baru > td { background-color: rgba(181, 71, 8, .10); }
    #fu-table.fu-tbl > tbody > tr.fu-baru > td:first-child { box-shadow: inset 3px 0 0 var(--orange); }
    #fu-table.fu-tbl > tbody > tr.fu-baru > td.fu-pin { background-color: #fff; background-image: linear-gradient(rgba(181, 71, 8, .10), rgba(181, 71, 8, .10)); }
    .fu-foot { display: flex; flex-wrap: wrap; gap: 8px 16px; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 13px; }
    .fu-foot .dataTables_length select { width: auto; display: inline-block; }
    .fu-empty-note { padding: 26px; text-align: center; color: var(--muted); }
    /* form modal */
    .fu-page .frm h3 { font-size: 14px; margin: 14px 0 8px; padding-bottom: 4px; border-bottom: 1px solid var(--line); font-weight: 700; }
    .fu-page .frm h3:first-of-type { margin-top: 4px; }
    .fu-page .hint { font-size: 12.5px; color: var(--muted); margin: 0 0 10px; }
    /* Kolom kedua memuat tanggal + tombol "Hari ini", jadi lebih lebar; align start agar input tidak ikut meregang. */
    .frow { display: grid; grid-template-columns: 180px 240px minmax(0, 1fr); gap: 10px; align-items: start; }
    .frow label { display: flex; flex-direction: column; gap: 3px; font-size: 12px; color: var(--muted); font-weight: 400; margin: 0; }
    .frow .span2 { grid-column: span 2; }
    .req { color: var(--red); }
    .picbox { background: var(--row); border: 1px solid var(--line); border-radius: 3px; padding: 8px 10px; margin-bottom: 10px; }
    /* Dua isian PIC sama lebar; rata bawah supaya input tetap sejajar walau salah satu label terlipat. */
    .picbox .frow { grid-template-columns: 270px 270px; align-items: end; }
    @media (max-width: 760px) { .picbox .frow { grid-template-columns: 1fr; } }
    .fudone { list-style: none; padding: 0; margin: 0 0 8px; }
    .fudone li { border-left: 3px solid var(--green); background: var(--row); margin-bottom: 4px; font-size: 13px; }
    .fudone .fuhead { display: block; width: 100%; text-align: left; background: none; border: 0; padding: 6px 10px; cursor: pointer; font: inherit; color: inherit; }
    .fudone .fuhead:hover { background: var(--line); }
    .fudone .fubody { padding: 2px 10px 8px 32px; }
    .fuact { border: 1px solid var(--blue); border-left-width: 4px; border-radius: 3px; padding: 10px 12px; margin-bottom: 8px; }
    .fuact-t { font-weight: 700; margin-bottom: 8px; }
    .funext { margin-top: 8px; padding: 8px 10px; border: 1px dashed var(--blue); border-radius: 3px; background: var(--row); max-width: 480px; display: flex; flex-direction: column; gap: 4px; }
    .ingrp { display: flex; gap: 4px; align-items: stretch; } .ingrp input { flex: 1; min-width: 0; } .ingrp button { flex: none; white-space: nowrap; }
    /* Label Status FU panjang: pakai dua kolom supaya tidak patah tiga baris. */
    .frow > label:has(select[name*="status"]) { grid-column: span 2; } .frow > label:has(select[name*="status"]) select { max-width: 260px; }
    .fu-err { color: var(--red); font-size: 12.5px; margin: 0 0 8px; }
    .fu-modal-bar { display: flex; gap: 8px; align-items: center; }
    .fu-modal-bar .sp { flex: 1; }
    @media (max-width: 760px) { .frow { grid-template-columns: 1fr; } .frow .span2, .frow > label:has(select[name*="status"]) { grid-column: auto; } }

    /* MP-NFR-10: huruf lebih besar, hanya menu Follow Up (daftar dan form Update). Menu lain tidak berubah. */
    .fu-page { font-size: 16px; }
    .fu-page .fu-tbl th { font-size: 15px; }
    .fu-page .fu-tbl td { font-size: 16px; }
    .fu-page .fu-tbl.wide th, .fu-page .fu-tbl.wide td { font-size: 14.5px; }
    .fu-page .sub, .fu-page .hint, .fu-page .fk, .fu-page .asof, .fu-page .fl, .fu-page .frow label, .fu-page .fu-tabhint, .fu-page .bukti-picked,
    .fu-page .fu-err, .fu-page .okmsg, .fu-page .fu-warn { font-size: 14.5px; }
    .fu-page .fu-card .v, .fu-page .fu-foot, .fu-page .fudone li, .fu-page .fu-tbl .more { font-size: 14px; }
    .fu-page .fu-card .n { font-size: 22px; }
    .fu-page .fu-pill { font-size: 13px; }
    .fu-page .fu-btn, .fu-page .linkbtn { font-size: 14.5px; }
    .fu-page .fu-groups button { font-size: 16px; }
    .fu-page .fu-tabs button { font-size: 15px; } /* mockup body.pg-fu: tab 15px, h3 17px, label form/filter 14px */
    .fu-page .fu-btn.sm { font-size: 14px; }
    .fu-page select, .fu-page input[type=text], .fu-page input[type=tel], .fu-page input[type=date], .fu-page input[type=search], .fu-page input[type=file] { font-size: 15px; }
    .fu-page .frm h3 { font-size: 17px; }
    .fu-page .frow label, .fu-page .fl { font-size: 14px; }
    .fu-page .fu-card .n small { font-size: 13px; font-weight: 600; color: var(--muted); }
    .fu-page .fu-card .v b { color: #333a40; }
    .fu-tabhint.fu-grphint { margin-top: 0; }
    .fu-page .fu-tbl thead.kol-grp th { text-align: center; background: var(--row); border-bottom: 1px solid var(--line); }
    .fu-tbl tfoot td { font-weight: 700; background: var(--row); border-top: 2px solid var(--line); }
    .fu-kt-note { color: var(--muted); font-size: 13.5px; margin-left: 8px; }
    .fu-warn { color: var(--orange); margin: 6px 0 0; }
    /* Skeleton loading: kerangka abu bergerak saat card dan tabel dimuat ulang */
    .fu-skel { margin: 0 0 8px; }
    .fu-skel i, #fu-cards.fu-loading .fu-card .n, #fu-cards.fu-loading .fu-card .v { background: linear-gradient(90deg, #eceff2 25%, #f6f8fa 37%, #eceff2 63%); background-size: 400% 100%; animation: fu-shim 1.3s ease infinite; }
    .fu-skel i { display: block; height: 32px; margin: 6px 0; border-radius: 3px; }
    #fu-cards.fu-loading .fu-card .n, #fu-cards.fu-loading .fu-card .v { color: transparent; border-radius: 3px; }
    #fu-panel.fu-loading table { opacity: .35; }
    @keyframes fu-shim { 0% { background-position: 100% 50%; } 100% { background-position: 0 50%; } }
    @media (prefers-reduced-motion: reduce) { .fu-skel i, #fu-cards.fu-loading .fu-card .n, #fu-cards.fu-loading .fu-card .v { animation: none; } }
    /* Bukti terima (mockup MP-R-21): kotak seret / tempel + kartu pratinjau per file */
    .bukti-box { margin-top: 10px; color: var(--muted); font-size: 14px; }
    .bukti-drop { border: 2px dashed #c8d0d8; border-radius: 4px; padding: 16px 12px; text-align: center; }
    .bukti-drop .fu-btn { margin-left: 4px; }
    .fu-page .bukti-drop input[type=file] { display: none; } /* tema memberi display:block pada input file, mengalahkan atribut hidden */
    .bukti-open.drop .bukti-drop { border-color: var(--blue); background: rgba(29, 95, 147, .08); color: #333a40; }
    .bukti-list { list-style: none; padding: 0; margin: 8px 0 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(min(320px, 100%), 1fr)); gap: 8px; }
    .bukti-picked { display: flex; align-items: center; gap: 12px; min-width: 0; border: 1px solid var(--line); border-radius: 6px; padding: 8px; background: #fff; }
    .bukti-picked .th { flex: 0 0 56px; height: 56px; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid var(--line); border-radius: 4px; background: var(--row); font-size: 26px; color: var(--red); }
    .bukti-picked img { width: 100%; height: 100%; object-fit: cover; }
    .bukti-picked .info { flex: 1; min-width: 0; }
    .bukti-picked .nm { display: block; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; font-weight: 600; }
    .fu-page .bukti-picked .bukti-del { flex: none; padding: 4px 8px; color: var(--red); }
    #fu-modal .modal-title { font-weight: 700; }
    #fu-modal .modal-header .fu-btn { float: right; margin-top: -4px; }
    /* Fase 6: usulan jadwal FU berikutnya, peringatan bermasalah, baris disorot setelah simpan / buka */
    .fu-next { margin-top: 8px; padding: 8px 10px; border: 1px dashed var(--blue); border-radius: 3px; background: var(--row); max-width: 520px; }
    .fu-next label { display: flex; flex-direction: column; gap: 3px; margin: 0; font-weight: 400; color: var(--muted); max-width: 200px; }
    .fu-bmwarn { margin: 8px 0 0; padding: 8px 10px; border-left: 3px solid var(--red); background: rgba(180, 35, 24, .08); }
    .fu-pill.unsaved { background: var(--orange); }
    #fu-table tbody tr.fu-flash > td { animation: fu-flash 2.4s ease 1; }
    @keyframes fu-flash { 0%, 40% { background-color: rgba(250, 204, 21, .45); } 100% { background-color: transparent; } }
    @media (prefers-reduced-motion: reduce) { #fu-table tbody tr.fu-flash > td { animation: none; outline: 2px solid #ca8a04; } }
    .okmsg .linkbtn { margin-left: 8px; }
    .okmsg .x { float: right; border: 0; background: none; cursor: pointer; font-size: 16px; line-height: 1; color: inherit; }
</style>

<div class="fu-page">
<div id="fu-tour"></div>
<div class="fu-panel">
    <div class="fu-groups" id="fu-groups" role="group" aria-label="Kelompok tab">
        <button type="button" class="on" data-group="proses">Proses</button>
        <button type="button" data-group="monitoring">Monitoring</button>
        <button type="button" data-group="perhatian">Perlu Perhatian &amp; Selesai</button>
    </div>
    <p class="fu-tabhint fu-grphint" id="fu-ghint"></p>
    <div class="fu-cards" id="fu-cards"></div>
    <div class="fu-tabs" id="fu-tabs" role="tablist" aria-label="Tahap follow up"></div>
    <p class="fu-tabhint" id="fu-hint"><i class="fa fa-info-circle" aria-hidden="true"></i> <span></span></p>

    <div class="fu-filters">
        <label class="fl">Company<select id="f-company" multiple></select></label>
        <label class="fl">Customer<input type="text" id="f-customer" list="dl-customer" autocomplete="off" placeholder="Pilih dari daftar"></label><datalist id="dl-customer"></datalist>
        <label class="fl">Tahun Invoice
            <select id="f-inv-tahun"><option value="">Semua Tahun</option><?php foreach ($inv_years as $y) echo '<option' . ($y == date('Y') ? ' selected' : '') . '>' . $y . '</option>'; ?></select>
        </label>
        <label class="fl">Bulan Invoice
            <select id="f-inv-bulan"><option value="">Semua Bulan</option><?php foreach ($bulan as $i => $b) echo '<option value="' . ($i + 1) . '"' . (($i + 1) == date('n') ? ' selected' : '') . '>' . $b . '</option>'; ?></select>
        </label>
        <label class="fl" id="f-inv-dari-wrap">Tanggal Invoice dari<input type="date" id="f-inv-dari"></label>
        <label class="fl" id="f-inv-sampai-wrap">sampai<input type="date" id="f-inv-sampai"></label>
        <label class="fl" id="f-fu-wrap">Follow Up
            <select id="f-fu">
                <option value="">Semua</option><option value="none">Belum ada FU (belum diterima)</option>
                <option value="1">FU 1 berjalan</option><option value="2">FU 2 berjalan</option><option value="3">FU 3 berjalan</option>
                <option value="4">FU 4 berjalan</option><option value="tambahan">FU tambahan berjalan (FU 5 dst.)</option><option value="selesai">Semua FU selesai</option>
            </select>
        </label>
        <label class="fl" id="f-plan-bulan-wrap" style="display:none">Bulan Plan Bayar
            <select id="f-plan-bulan"><option value="">Semua Bulan</option><?php foreach ($bulan as $i => $b) echo '<option value="' . ($i + 1) . '">' . $b . '</option>'; ?></select>
        </label>
        <label class="fl" id="f-plan-tahun-wrap" style="display:none">Tahun
            <select id="f-plan-tahun"><option value="">Semua</option><?php foreach ($years as $y) echo '<option>' . $y . '</option>'; ?></select>
        </label>
        <label class="fl" id="f-close-wrap" style="display:none">Tahun Close
            <select id="f-close-tahun"><?php foreach ($years as $y) echo '<option' . ($y == date('Y') ? ' selected' : '') . '>' . $y . '</option>'; ?></select>
        </label>
        <div class="sp"></div>
        <label class="fl searchbox">Cari
            <input type="search" id="fu-search" placeholder="Cari: invoice, SPK, customer, dll." title="Mencari di no invoice, tanggal, customer, project, no SPK, company, via kirim, no resi, nominal, status, dan FU. Beberapa kata = semua kata harus cocok.">
        </label>
        <button type="button" class="fu-btn" id="btn-export"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Excel</button>
    </div>

    <div id="fu-msg" role="status" style="display:none"></div>
    <p style="margin:0 0 8px" id="fu-kt-bar" hidden><button type="button" class="fu-btn g sm" id="fu-kt-all">Tampilkan semua keterangan</button>
        <span class="fu-kt-note">Keterangan yang panjang diringkas; klik <b>selengkapnya</b> untuk membuka satu, atau tombol ini untuk membuka semua.</span></p>

    <div id="fu-skel" class="fu-skel" role="status" hidden><span class="sr-only">Memuat daftar invoice...</span><i></i><i></i><i></i><i></i><i></i><i></i></div>
    <div class="table-responsive" id="fu-panel" role="tabpanel">
        <table id="fu-table" class="fu-tbl"><thead></thead><tbody></tbody></table>
    </div>
</div>

<div class="modal fade" id="fu-modal" tabindex="-1" role="dialog" aria-labelledby="fu-modal-title"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><button type="button" class="fu-btn g" data-dismiss="modal">Tutup</button><h4 class="modal-title" id="fu-modal-title"></h4></div>
    <div class="modal-body">
        <div id="fu-modal-msg" class="fu-err" role="alert" style="display:none"></div>
        <div id="fu-modal-body"></div>
        <div id="fu-log"></div>
    </div>
    <div class="modal-footer fu-modal-bar">
        <button type="button" class="linkbtn" id="btn-history">Riwayat perubahan</button><span class="sp"></span>
        <button type="button" class="fu-btn g" data-dismiss="modal">Batal</button>
        <button type="button" class="fu-btn bl" id="btn-save">Simpan</button>
    </div>
</div></div></div>

<div class="modal fade" id="fu-close-modal" tabindex="-1" role="dialog" aria-labelledby="fu-close-title"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button><h4 class="modal-title" id="fu-close-title"></h4></div>
    <div class="modal-body">
        <p id="fu-close-info"></p>
        <p class="hint">Invoice sudah lunas. Close hanya bisa untuk invoice yang sudah lunas dan punya plan bayar.</p>
        <label class="fl">Keterangan close<textarea id="fu-close-ket" class="form-control" rows="3" placeholder="Mis. lunas sesuai penerimaan ..."></textarea></label>
        <div id="fu-close-err" class="fu-err" role="alert" style="margin-top:6px"></div>
        <p class="hint">Setelah close, invoice tidak lagi dihitung di Monitoring Piutang dan pindah ke tab Invoice Close.</p>
    </div>
    <div class="modal-footer"><button type="button" class="fu-btn g" data-dismiss="modal">Batal</button> <button type="button" class="fu-btn r" id="btn-do-close">Close Invoice</button></div>
</div></div></div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/js/company-multi.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js') ?>"></script>
<script src="<?= base_url('assets/js/piutang-kolom.js') . '?v=' . @filemtime(FCPATH . 'assets/js/piutang-kolom.js') ?>"></script>
<link rel="stylesheet" href="<?= base_url('assets/plugins/driver/driver.css') ?>">
<script src="<?= base_url('assets/plugins/driver/driver.js.iife.js') ?>"></script>
<script src="<?= base_url('assets/js/piutang-tutorial.js') ?>"></script>
<script>
(function () {
    var BASE = '<?= $base ?>', INVOICING = '<?= site_url('invoicing') ?>', BUKTI = '<?= site_url('monitoring_piutang/bukti') ?>', TODAY = '<?= date('Y-m-d') ?>', MANAGE = <?= $can_manage ? 'true' : 'false' ?>;
    // Petunjuk per sub-tab dan per tab utama: teks mockup (FU_TABS / FU_GROUPS), disesuaikan dengan bukti terima wajib.
    var HINTS = {
        all: 'Semua invoice yang belum close, lengkap seperti Visual Control: invoice, faktur, tanggal, nilai, pengiriman, PIC, FU 1 - 4 (plan, aktual, status, keterangan), plan bayar dan status. Geser tabel ke samping; kolom No dan Invoice · Customer tetap terlihat. Seret judul kolom untuk memindahkannya, atau tepi kanan judul untuk mengubah lebarnya.',
        '1': 'Invoice belum dikirim ke customer. Klik Update, isi tanggal kirim dan dikirim via (JNE / Driver).',
        '2': 'Invoice sudah dikirim (data kirim terkunci). Setelah customer konfirmasi menerima invoice, isi tanggal terima, no resi (hanya via JNE, wajib), bukti terima, dan cek no faktur.',
        '3': 'Invoice sudah diterima customer. Isi PIC penerima follow up, lakukan FU 1 - 4 sesuai jadwal sampai customer memberi rencana tanggal bayar. Bila perlu, tambah FU 5 dst. setelah FU 4.',
        '4': 'Customer sudah memberi rencana tanggal bayar, follow up berhenti dan data tidak bisa diubah lagi. Setelah pembayaran diterima, klik Close.',
        late: 'Follow up yang jadwalnya sudah lewat dan belum ada rencana bayar. Segera lakukan follow up.',
        bermasalah: 'Status FU terakhir INVOICE BERMASALAH (mis. batal, revisi invoice, customer menolak; alasannya di keterangan FU). Follow up berhenti dan piutang tetap dihitung di Monitoring sampai ada penyelesaian.',
        close: 'Invoice yang sudah close, per tahun close.'
    };
    var GROUP_HINTS = {
        proses: 'Tempat bekerja: kirim invoice, konfirmasi diterima, follow up customer, sampai tunggu bayar & close.',
        monitoring: 'Hanya untuk melihat seluruh invoice yang belum close dari awal sampai akhir. Untuk mengubah data, klik Buka di Proses.',
        perhatian: 'Yang tersendat (FU terlambat, invoice bermasalah) dan hasil akhirnya (invoice close).'
    };
    // 3 tab utama (MP-FR-08) dan card ringkasan per kelompok (MP-FR-22).
    var GROUPS = {proses: ['1', '2', '3', '4'], monitoring: ['all'], perhatian: ['late', 'bermasalah', 'close']};
    var TABLBL = {all: 'Semua', '1': '1. Kirim Invoice', '2': '2. Konfirmasi Diterima', '3': '3. Follow Up Customer', '4': '4. Tunggu Bayar & Close',
        late: 'FU Terlambat', bermasalah: 'Invoice Bermasalah', close: 'Invoice Close (Laporan)'};
    // [kunci, judul, tab, filter FU, warna, keterangan] — teks keterangan dari mockup fuCardsData.
    var CARDS = {
        proses: [['belum_kirim', 'Belum dikirim', '1', '', 'new', 'invoice belum dikirim ke customer'], ['belum_diterima', 'Belum diterima', '2', '', '', 'sudah dikirim, belum ada tanggal terima'],
            ['belum_fu', 'Belum di-follow up', '3', '1', 'ora', 'sudah diterima, FU 1 belum dilakukan'], ['tunggu_bayar', 'Tunggu bayar', '4', '', 'grn', 'ada plan bayar, menunggu pembayaran / close']],
        perhatian: [['terlambat', 'FU terlambat', 'late', '', 'red', 'jadwal FU sudah lewat, belum ada plan bayar'], ['bermasalah', 'Invoice bermasalah', 'bermasalah', '', 'red', 'follow up berhenti, lihat keterangan'],
            ['closed', 'Closed', 'close', '', 'grey', 'di-close pada periode filter']]
    };
    var KAT = {lancar: 'Lancar', potensi: 'Potensi Macet', macet: 'Macet'};
    var MON = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    var group = 'proses', WARN_FU = 'Isi status dan keterangan sebelum menyimpan; tanggal dilakukan tanpa keduanya akan ditolak saat Simpan.';
    var f = {tab: '1'}, cur = null, table = null, VIA = ['JNE', 'Driver'], picked = [], closeRow = null, lastCards = null;
    var lastSub = {}, flashId = null, openAfter = null, resolveTab = false, tourOpenFirst = false; // sub-tab terakhir per tab utama; baris yang disorot / dibuka setelah daftar dimuat
    var BM = 'INVOICE BERMASALAH';
    function groupOf(t) { return t === 'all' ? 'monitoring' : (t === 'late' || t === 'bermasalah' || t === 'close') ? 'perhatian' : 'proses'; }
    function addDays(d, n) { var x = new Date(d + 'T00:00:00Z'); x.setUTCDate(x.getUTCDate() + n); return x.toISOString().substr(0, 10); }

    function esc(s) { return $('<div>').text(s == null ? '' : String(s)).html().replace(/"/g, '&quot;').replace(/'/g, '&#39;'); } // dipakai juga di dalam atribut
    // Tampilan mockup: "Rp 1.234.567" (desimal hanya bila ada), tanggal "2 Nov 2026". Excel tetap angka/tanggal penuh dari server.
    function nf(v) { return 'Rp ' + Number(v || 0).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 2}); }
    function dmy(d) {
        var s = String(d || '');
        return /^\d{4}-\d{2}-\d{2}/.test(s) ? Number(s.substr(8, 2)) + ' ' + MON[Number(s.substr(5, 2)) - 1] + ' ' + s.substr(0, 4) : '';
    }
    function ico(n) { return '<i class="fa fa-' + n + '" aria-hidden="true"></i>'; }

    // Pesan di halaman dan di modal (menggantikan alert()).
    function say(text, kind) { $('#fu-msg').attr('class', 'okmsg' + (kind === 'danger' ? ' danger' : '')).text(text).show(); }
    // Pesan sukses dengan tombol pindah ke tab tujuan dan tombol tutup (mockup showMsg).
    function sayGo(text, tab, id) {
        var go = tab && tab !== f.tab ? '<button type="button" class="linkbtn" data-goto="' + esc(tab) + '" data-id="' + esc(id) + '">Lihat di tab ' + esc(TABLBL[tab]) + ' →</button>' : '';
        $('#fu-msg').attr('class', 'okmsg').html('<button type="button" class="x" aria-label="Tutup pesan">&times;</button>' + ico('check') + ' ' + esc(text) + go).show();
    }
    function mfail(text) { $('#fu-modal-msg').text(text).show(); $('#fu-modal').scrollTop(0); }

    function params() {
        return $.extend({}, f, {
            company: CompanyMulti.val('#f-company'), customer: $('#f-customer').val(), fu: f.fu || '',
            plan_bulan: $('#f-plan-bulan').val(), plan_tahun: $('#f-plan-tahun').val(), close_tahun: $('#f-close-tahun').val(),
            inv_tahun: $('#f-inv-tahun').val(), inv_bulan: $('#f-inv-bulan').val(), inv_dari: $('#f-inv-dari').val(), inv_sampai: $('#f-inv-sampai').val()
        });
    }

    var TAB_OF = {belum_kirim: '1', dikirim: '2', diterima: '3', plan_bayar: '4', close: 'close'};
    function tabOf(x) { return x.bermasalah && x.status !== 'close' ? 'bermasalah' : TAB_OF[x.status]; }

    function cardsHtml() {
        var list = CARDS[group] || [], c = lastCards || {};
        return list.map(function (d) {
            var v = c[d[0]] || {count: 0, cents: 0};
            return '<button type="button" class="fu-card ' + d[4] + (f.tab === d[2] && (f.fu || '') === d[3] ? ' on' : '') + '" data-tab="' + d[2] + '" data-fu="' + d[3] + '" title="Buka daftarnya">' +
                '<span class="t">' + esc(d[1]) + '</span><span class="n">' + v.count + ' <small>invoice</small></span>' +
                '<span class="v">' + (d[0] === 'closed' ? 'Nilai invoice' : 'Sisa piutang') + ' <b>' + nf(v.cents / 100) + '</b></span><span class="v k">' + esc(d[5]) + '</span></button>';
        }).join('');
    }
    function tabsHtml() {
        return (GROUPS[group] || []).map(function (t) {
            return '<button type="button" role="tab" aria-selected="' + (f.tab === t) + '"' + (f.tab === t ? ' class="on"' : '') + ' data-tab="' + t + '">' + esc(TABLBL[t]) + '</button>';
        }).join('');
    }
    function renderChrome() {
        $('#fu-groups button').removeClass('on').filter('[data-group=' + group + ']').addClass('on');
        $('#fu-ghint').text(GROUP_HINTS[group] || '');
        $('#fu-tabs').html(tabsHtml()).toggle(group !== 'monitoring');
        $('#fu-cards').html(cardsHtml()).toggle(!!CARDS[group]);
        $('#f-fu').val(f.fu || '');
    }
    // Pindah tab utama kembali ke sub-tab terakhir yang dibuka di kelompok itu (mockup fuLastSub).
    function setGroup(g) { group = g; f.tab = lastSub[g] || GROUPS[g][0]; f.fu = ''; renderChrome(); load(); }
    function goTab(t, id, open) {
        group = groupOf(t); f.tab = t; f.fu = ''; lastSub[group] = t; flashId = id || null; openAfter = open ? id : null;
        renderChrome(); load();
    }

    // Kolom dan filter mengikuti tab: kolom berbeda per tab (MP-R-28), tab Semua lebar dengan dua kolom pertama tetap terlihat.
    function applyTab() {
        var t = f.tab, t4 = t === '4', cl = t === 'close';
        $('#fu-hint span').text(HINTS[t] || '');
        $('#f-fu-wrap').toggle(!t4 && !cl); // mockup: juga tampil di tab Invoice Bermasalah
        $('#f-plan-bulan-wrap, #f-plan-tahun-wrap').toggle(t4);
        $('#f-close-wrap').toggle(cl);
        $('#f-inv-tahun, #f-inv-bulan, #f-inv-dari, #f-inv-sampai').closest('label').toggle(!cl); // tab Invoice Close memakai Tahun Close
        $('#fu-kt-bar').prop('hidden', t !== 'all');
        if (!table || table.__tab !== t) { initTable(); }
    }
    function load(keep) { applyTab(); table.ajax.reload(null, !keep); }

    function fuCell(x) {
        if (x.status === 'plan_bayar') { return '<span class="asof">Selesai (ada rencana bayar)</span>'; }
        if (x.bermasalah) { return '<span class="late">Berhenti (invoice bermasalah)</span>'; }
        if (x.status !== 'diterima') { return '<span class="asof">–</span>'; }
        if (x.fu_stage === 'selesai' || !x.fu_next_plan) { return '<span class="asof">Semua FU selesai</span>'; }
        var n = 'FU ' + (x.fu_next_ke || x.fu_stage) + (x.fu_stage === 'tambahan' ? ' (tambahan)' : '');
        return lastSt(x) + '<span class="' + (x.fu_late ? 'late' : '') + '"><b>' + n + '</b> · ' + dmy(x.fu_next_plan) + (x.fu_late ? '<span class="sub late">lewat jadwal</span>' : '') + '</span>';
    }

    function aksiCell(x) {
        var inv = esc(x.no_invoice), h = '';
        if (f.tab === 'all') { // Monitoring: hanya melihat; kerjakan di tab kerjanya (form langsung terbuka, kecuali invoice bermasalah)
            var tt = tabOf(x), gl = groupOf(tt) === 'perhatian' ? 'Perlu Perhatian & Selesai' : 'Proses';
            return '<button type="button" class="fu-btn sm g" data-act="buka" data-tab="' + tt + '" data-id="' + esc(x.id) + '" data-bm="' + (x.bermasalah ? 1 : 0) + '" title="Tab ' + esc(TABLBL[tt]) + '" aria-label="Buka invoice ' + inv + ' di ' + esc(gl) + '">Buka di ' + esc(gl) + '</button>';
        }
        var look = !x.can_update;
        h += '<button type="button" class="fu-btn sm ' + (look ? 'g' : 'bl') + '" data-act="update" data-id="' + esc(x.id) + '" aria-label="' + (look ? 'Lihat' : 'Update') + ' follow up invoice ' + inv + '">' + (look ? 'Lihat' : 'Update') + '</button> ';
        if (f.tab === '4' && MANAGE && x.status !== 'close') { // Close hanya satu jalur: tab 4 (MP-R-27)
            h += '<button type="button" class="fu-btn sm r" data-act="close" data-id="' + esc(x.id) + '" aria-label="Close invoice ' + inv + '"' +
                (x.can_close ? '' : ' disabled title="' + esc(x.close_block || 'Isi plan tanggal bayar dulu') + '"') + '>Close</button>' +
                (x.can_close ? '' : '<span class="sub">' + esc(x.close_block || 'Isi plan tanggal bayar dulu') + '</span>'); // alasan terlihat di layar sentuh/keyboard
        }
        return h;
    }

    // Keterangan panjang diringkas; per sel bisa dibuka, dan tombol "Tampilkan semua keterangan" membuka semuanya.
    function ketHtml(t) {
        if (!t) { return ''; }
        if (String(t).length <= 60) { return esc(t); }
        return '<span class="kt"><span class="kt-s">' + esc(String(t).substr(0, 60)) + '… </span><span class="kt-f">' + esc(t) + ' </span><button type="button" class="more" aria-expanded="false">selengkapnya ▾</button></span>';
    }
    function stPill(st) { return st ? '<span class="fu-pill ' + (st === 'INVOICE BERMASALAH' ? 'bm' : 'info') + '">' + esc(st) + '</span>' : ''; }
    function lbl(a, v) { return '<span class="fk"><b>' + a + '</b> ' + (v == null || v === '' ? '–' : v) + '</span>'; }
    function fuLabel(x, i) {
        var plan = x['fu' + i + '_plan'], akt = x['fu' + i + '_aktual'];
        if (!plan && !akt) { return '<span class="asof">–</span>'; }
        var late = plan && !akt && !x.plan_bayar && !x.bermasalah && plan < TODAY; // mockup: tidak ditandai bila sudah ada plan bayar / bermasalah
        return '<span class="fk"><b>Plan</b> <span class="' + (late ? 'late' : '') + '">' + (plan ? dmy(plan) : '–') + '</span>' + (late ? '<span class="sub late">lewat jadwal</span>' : '') +
            '<br><b>Aktual</b> ' + (akt ? dmy(akt) : '–') + '</span>';
    }
    // Pill status FU terakhir yang dilakukan (mockup lastStTag).
    function lastSt(x) { return x.fu_last_status ? '<span class="sub">' + stPill(x.fu_last_status) + ' status FU ' + x.fu_last_ke + '</span>' : ''; }
    function fuStatus(x, i) { return stPill(x['fu' + i + '_status']) + (x['fu' + i + '_ket'] ? '<span class="sub">' + ketHtml(x['fu' + i + '_ket']) + '</span>' : ''); }

    // Pustaka kolom: tiap tab memilih kolom yang dibutuhkan tugasnya.
    var COL = {
        no: ['No', {data: null, className: 'fu-pin fu-pinno', render: function (_, t, x, m) { return m.row + m.settings._iDisplayStart + 1; }}],
        inv: ['Invoice', {data: null, className: 'fu-pin fu-pin2 no', render: function (_, t, x) {
            return esc(x.no_invoice) + '<span class="sub">' + dmy(x.tgl_invoice) + '</span>' +
                (x.bermasalah ? '<span class="fu-pill bm">' + ico('exclamation-triangle') + ' Bermasalah</span>' : '') +
                (x.baru ? '<span class="fu-pill baru">' + ico('star') + ' Baru' + (x.baru_from ? ' · dari ' + esc(x.baru_from) : '') + '</span>' +
                    (x.status_changed_date ? '<span class="sub">pindah ' + dmy(x.status_changed_date) + '</span>' : '') : ''); }}],
        cust: ['Customer', {data: null, render: function (_, t, x) {
            return esc(x.customer) + '<span class="sub">' + esc(x.project) + ' · ' + esc(x.company_name) + '</span>' +
                ((x.no_spk || x.top_ke) ? '<span class="sub">SPK ' + esc(x.no_spk || '–') + ' · TOP ' + esc(x.top_ke || '–') + '</span>' : ''); }}],
        nilai: ['Nilai Invoice', {data: null, className: 'num', render: function (_, t, x) { return nf(x.nilai); }}],
        sisa: ['Sisa', {data: null, className: 'num', render: function (_, t, x) { return '<b>' + nf(x.saldo) + '</b><span class="sub">dari ' + nf(x.nilai) + '</span>'; }}],
        kirim: ['Dikirim', {data: null, render: function (_, t, x) {
            return x.tgl_kirim ? dmy(x.tgl_kirim) + (x.kirim_via ? '<span class="sub">' + esc(x.kirim_via) + '</span>' : '') : '<span class="asof">Belum</span>'; }}],
        resi: ['No Resi (JNE)', {data: null, render: function (_, t, x) {
            return x.kirim_via === 'JNE' ? (x.no_resi ? esc(x.no_resi) : '<span class="late">belum diisi</span>') : '<span class="asof">' + (x.kirim_via === 'Driver' ? 'tidak perlu (via Driver)' : '–') + '</span>'; }}],
        terima: ['Diterima / Umur', {data: null, render: function (_, t, x) {
            if (!x.tgl_terima) { return '<span class="asof">Belum diterima</span>'; }
            var k = x.kategori && Number(x.saldo) > 0 ? '<span class="sub"><span class="umur ' + x.kategori + '">' + x.umur + ' hari</span> <span class="fu-pill ' + x.kategori + '">' + (KAT[x.kategori] || x.kategori) + '</span></span>' : '';
            return dmy(x.tgl_terima) + k; }}],
        umur: ['Umur', {data: null, render: function (_, t, x) {
            return x.umur == null ? '<span class="asof">–</span>' : '<span class="umur ' + (x.kategori || '') + '">' + x.umur + ' hari</span>' +
                (x.kategori ? ' <span class="fu-pill ' + x.kategori + '">' + KAT[x.kategori] + '</span>' : ''); }}],
        pic: ['PIC', {data: null, render: function (_, t, x) { return x.fu_pic_nama ? esc(x.fu_pic_nama) + (x.fu_pic_telp ? '<span class="sub">' + esc(x.fu_pic_telp) + '</span>' : '') : '<span class="asof">–</span>'; }}],
        fubr: ['Follow Up Berikutnya', {data: null, render: function (_, t, x) { return fuCell(x); }}],
        futerjadwal: ['FU Terjadwal', {data: null, render: function (_, t, x) {
            var p = x.fu_next_plan, d = p ? Math.round((new Date(TODAY) - new Date(p)) / 86400000) : 0;
            return p ? '<b>' + (x.fu_stage === 'tambahan' ? 'FU tambahan' : 'FU ' + x.fu_stage) + '</b> · ' + dmy(p) + '<span class="sub late">terlambat ' + d + ' hari</span>' : '–'; }}],
        plan: ['Rencana Bayar', {data: null, render: function (_, t, x) {
            return x.plan_bayar ? '<b>' + dmy(x.plan_bayar) + '</b>' + (x.ket_plan_bayar ? '<span class="sub">' + esc(x.ket_plan_bayar) + '</span>' : '') : '<span class="asof">–</span>'; }}],
        bayar: ['Pembayaran', {data: null, render: function (_, t, x) {
            return Number(x.saldo) <= 0 ? '<span class="fu-pill st-plan_bayar">Lunas</span><span class="sub">siap di-close</span>'
                : '<span class="fu-pill warn">Sisa ' + nf(x.saldo) + '</span>' + (Number(x.terbayar) > 0 ? '<span class="sub">terbayar ' + nf(x.terbayar) + '</span>' : ''); }}],
        stfu: ['Status FU', {data: null, render: function (_, t, x) {
            if (!x.fu_last_ke) { return '<span class="asof">–</span>'; }
            var k = x.fu_last_ke, akt = k <= 4 ? x['fu' + k + '_aktual'] : '';
            return stPill(x.fu_last_status) + '<span class="sub">FU ' + k + (akt ? ' · ' + dmy(akt) : '') + '</span>'; }}],
        ket: ['Keterangan', {data: null, className: 'wrapt', render: function (_, t, x) { return ketHtml(x.ket_bermasalah) || '<span class="asof">–</span>'; }}],
        closeinfo: ['Close', {data: null, render: function (_, t, x) { return dmy(x.close_date) + '<span class="sub">' + esc(x.close_reason) + '</span>'; }}],
        aksi: ['<span class="sr-only">Aksi</span>', {data: null, className: 'no', render: function (_, t, x) { return aksiCell(x); }}]
    };
    var COLSETS = {
        '1': ['no', 'inv', 'cust', 'nilai', 'aksi'],
        '2': ['no', 'inv', 'cust', 'nilai', 'kirim', 'resi', 'aksi'],
        // MP-R-28: kolom dasar (Invoice, Customer, Nilai Invoice) + kolom tugas tab itu saja
        '3': ['no', 'inv', 'cust', 'nilai', 'sisa', 'terima', 'pic', 'fubr', 'aksi'],
        '4': ['no', 'inv', 'cust', 'nilai', 'plan', 'bayar', 'aksi'],
        late: ['no', 'inv', 'cust', 'nilai', 'sisa', 'pic', 'futerjadwal', 'aksi'],
        bermasalah: ['no', 'inv', 'cust', 'nilai', 'sisa', 'umur', 'stfu', 'ket', 'aksi'],
        close: ['no', 'inv', 'cust', 'nilai', 'plan', 'closeinfo', 'aksi']
    };
    // Tab Semua (MP-FR-19): satu baris per invoice, informasi digabung per kolom berlabel.
    // Kolom ketiga tiap entri = judul grup header (mockup renderVC: header 2 baris, baris grup dibuat PiutangKolom).
    function allColumns() {
        var dash = '<span class="asof">–</span>';
        var num = function (k, b) { return {data: null, className: 'num', render: function (_, t, x) { var v = nf(x[k]); return b ? '<b>' + v + '</b>' : (k === 'terbayar' && !Number(x[k]) ? '–' : v); }}; };
        var txt = function (k) { return {data: null, render: function (_, t, x) { return x[k] ? esc(x[k]) : dash; }}; };
        var c = [COL.no, ['Invoice · Customer<span class="sub">No SPK · TOP · Company</span>', {data: null, className: 'fu-pin fu-pin2', render: function (_, t, x) { return COL.inv[1].render(_, t, x) + '<div>' + COL.cust[1].render(_, t, x) + '</div>'; }}],
            ['No Faktur', txt('no_faktur'), 'Invoice & Pajak'], ['PO', txt('no_po'), 'Invoice & Pajak'], ['Konsultan', txt('konsultan'), 'Invoice & Pajak'],
            ['Tanggal', {data: null, render: function (_, t, x) {
                return lbl('Invoice', dmy(x.tgl_invoice)) + '<br>' + lbl('Kirim', dmy(x.tgl_kirim)) + '<br>' + lbl('Terima', dmy(x.tgl_terima)) + '<br>' + lbl('Plan bayar', dmy(x.plan_bayar)); }}],
            ['DPP', num('dpp'), 'Nilai'], ['PPN', num('ppn'), 'Nilai'], ['Total Invoice', num('nilai'), 'Nilai'], ['Terbayar', num('terbayar'), 'Nilai'], ['Sisa Piutang', num('saldo', true), 'Nilai'],
            ['Pengiriman', {data: null, render: function (_, t, x) {
                if (!x.tgl_kirim) { return '<span class="asof">Belum dikirim</span>'; }
                return lbl('Via', esc(x.kirim_via) + (x.ket_kirim ? ' · ' + esc(x.ket_kirim) : '')) + '<br>' +
                    (x.kirim_via === 'JNE' ? lbl('Resi', x.no_resi ? esc(x.no_resi) : '<span class="late">belum diisi</span>') + '<br>' : '') +
                    lbl('Penerima', x.tgl_terima ? esc(x.ket_terima || '–') : '<span class="asof">belum diterima</span>') +
                    (x.tgl_terima ? '<br>' + lbl('Bukti', x.bukti_jumlah ? ico('paperclip') + ' ' + x.bukti_jumlah + ' file' : '–') : ''); }}],
            ['Nama', txt('fu_pic_nama'), 'PIC Follow Up'], ['No WA / Telp', txt('fu_pic_telp'), 'PIC Follow Up']];
        [1, 2, 3, 4].forEach(function (i) {
            c.push(['Plan · Aktual', {data: null, render: function (_, t, x) { return fuLabel(x, i); }}, 'FU ' + i]);
            c.push(['Status · Keterangan', {data: null, className: 'wrapt', render: function (_, t, x) { return fuStatus(x, i) || dash; }}, 'FU ' + i]);
        });
        c.push(['FU 5 dst.', {data: null, className: 'wrapt', render: function (_, t, x) {
            return x.fu_extra_text ? x.fu_extra_text.split('; ').map(function (s) { return '<span class="sub">' + esc(s) + '</span>'; }).join('') : dash; }}]);
        c.push(['Ket Plan Bayar', {data: null, className: 'wrapt', render: function (_, t, x) { return x.ket_plan_bayar ? ketHtml(x.ket_plan_bayar) : dash; }}]);
        c.push(['Umur', {data: null, render: function (_, t, x) {
            return x.umur != null ? '<span class="umur ' + (x.kategori || '') + '">' + x.umur + ' hari</span><span class="sub">' + (KAT[x.kategori] || '') + '</span>' : '<span class="asof">Belum diterima</span>'; }}, 'Status']);
        c.push(['Status Invoice', {data: null, render: function (_, t, x) {
            return '<span class="fu-pill st-' + esc(x.status) + '">' + esc(x.status_label) + '</span>' + (x.bermasalah ? ' <span class="fu-pill bm">Bermasalah</span>' : ''); }}, 'Status']);
        c.push(COL.aksi);
        return c;
    }
    // Satu baris judul saja: kolom bisa dipindah (ColReorder), jadi grup dititipkan di data-grp dan digambar PiutangKolom.
    function headHtml(cols) {
        return '<tr>' + cols.map(function (c) {
            var cls = (c[1].className || '').split(' ').filter(function (k) { return k === 'num' || /pin/.test(k); }).join(' ');
            return '<th' + (cls ? ' class="' + cls + '"' : '') + (c[2] ? ' data-grp="' + esc(c[2]) + '"' : '') + '>' + c[0] + '</th>';
        }).join('') + '</tr>';
    }
    var TOT_COL = {'DPP': 'dpp', 'PPN': 'ppn', 'Total Invoice': 'nilai', 'Terbayar': 'terbayar', 'Sisa Piutang': 'saldo'};
    function footHtml(cols) {
        return '<tfoot><tr>' + cols.map(function (c, i) {
            var k = c[2] === 'Nilai' ? TOT_COL[c[0]] : '';
            var cls = ((i < 2 ? c[1].className : '') + (k ? ' num' : '')).trim(); // tfoot satu sel per kolom: ikut pindah bersama kolomnya
            return '<td' + (cls ? ' class="' + cls + '"' : '') + (k ? ' data-tot="' + k + '"' : (i === 1 ? ' id="fu-tot-label"' : '')) + '></td>';
        }).join('') + '</tr></tfoot>';
    }
    function columnsFor(t) { return t === 'all' ? allColumns() : COLSETS[t].map(function (k) { return COL[k]; }); }

    function initTable() {
        $.fn.dataTable.ext.errMode = 'none'; // error ajax ditampilkan lewat banner #fu-msg
        $('#fu-panel').next('.fu-foot').remove(); // footer lama yang dipindah keluar area geser (lihat di bawah)
        if (table) { table.destroy(); }
        var cols = columnsFor(f.tab);
        $('#fu-table').empty().toggleClass('wide', f.tab === 'all')
            .append('<thead>' + headHtml(cols) + '</thead><tbody></tbody>' + (f.tab === 'all' ? footHtml(cols) : ''));
        table = $('#fu-table').DataTable({
            serverSide: true, processing: false, ordering: false, autoWidth: false, pageLength: 100, lengthMenu: [25, 50, 100, 250],
            // R = ColReorder: seret judul untuk memindah kolom; No dan Invoice terkunci. Urutan tidak disimpan (sama dengan mockup).
            dom: 'Rrt<"fu-foot"<"dataTables_length_wrap"l><"dataTables_info_wrap"i>p>', searchDelay: 400,
            colReorder: {iFixedColumns: 2}, columnDefs: [{targets: '_all', orderable: false}], // urutan baris tetap dari server
            language: {lengthMenu: 'Tampilkan _MENU_ invoice', processing: 'Memuat daftar invoice...',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ invoice', infoEmpty: 'Tidak ada invoice', infoFiltered: '(dari _MAX_ di tab ini)',
                zeroRecords: 'Tidak ada invoice yang cocok dengan tab, filter, atau kata cari ini. Hapus kata cari, ubah filter, atau pilih tab lain.',
                emptyTable: 'Belum ada invoice pada tab ini.', paginate: {previous: '&laquo;', next: '&raquo;'}},
            ajax: {url: BASE + '/data', type: 'GET', data: function (d) { $.extend(d, params()); }},
            createdRow: function (tr, x) { if (x.baru) { $(tr).addClass('fu-baru'); } if (x.bermasalah) { $(tr).addClass('bm-row'); } },
            columns: cols.map(function (c) { return c[1]; })
        });
        table.__tab = f.tab;
        // Info + paginasi dipindah ke luar #fu-panel (area geser horizontal) supaya tetap diam saat tabel digeser.
        // DataTables memperbarui node ini lewat referensi, jadi tetap berfungsi setelah dipindah.
        $('#fu-panel').after($('#fu-table_wrapper > .fu-foot'));
        PiutangKolom.init('#fu-table');
        // Skeleton (MP-NFR-11): card dan tabel diganti kerangka saat dimuat ulang.
        table.on('preXhr.dt', function () { $('#fu-skel').prop('hidden', false); $('#fu-panel, #fu-cards').addClass('fu-loading'); });
        table.on('xhr.dt error.dt', function () { $('#fu-skel').prop('hidden', true); $('#fu-panel, #fu-cards').removeClass('fu-loading'); });
        table.on('error.dt', function () { say('Daftar invoice gagal dimuat. Periksa koneksi lalu ganti tab atau muat ulang halaman.', 'danger'); });
        // Setelah simpan / tombol Buka: sorot baris invoice itu, dan buka form Update bila diminta.
        table.on('draw.dt', function () {
            if (flashId) {
                var $tr = $('#fu-table tbody [data-id]').filter(function () { return String($(this).data('id')) === String(flashId); }).first().closest('tr');
                if ($tr.length) { $tr.addClass('fu-flash'); $tr[0].scrollIntoView({block: 'center'}); }
                flashId = null;
            }
            if (openAfter) { var id = openAfter; openAfter = null; openForm(id); }
            if (tourOpenFirst) { // tutorial: buka form invoice pertama di tab ini (hanya dilihat / diisi, tidak disimpan)
                tourOpenFirst = false;
                var $b = $('#fu-table tbody [data-act=update]').first();
                if ($b.length) { openForm($b.data('id')); }
            }
        });
        // jumlah per tab dan daftar company datang bersama respons tabel
        table.on('xhr.dt', function (e, settings, json) {
            if (!json) { return; }
            $('#fu-msg.danger').hide(); // pesan sukses (mis. setelah Close) tetap tampil
            if (json.error) { say(json.error, 'danger'); } // filter tidak valid: server membalas 200 + error
            if (json.customers && !$('#dl-customer option').length) { json.customers.sort().forEach(function (c) { $('#dl-customer').append($('<option>').val(c)); }); }
            if (json.cards) { lastCards = json.cards; $('#fu-cards').html(cardsHtml()); }
            if (json.totals) { // baris total tab Semua = seluruh hasil filter
                $('#fu-tot-label').text('Total ' + (json.recordsFiltered || 0) + ' invoice');
                $('#fu-table tfoot [data-tot]').each(function () { $(this).text(nf(json.totals[$(this).data('tot')])); });
            }
            if (json.companies && !$('#f-company option').length) {
                json.companies.forEach(function (c) { $('#f-company').append($('<option>').val(c.id).text(c.nm_company)); });
                CompanyMulti.init('#f-company');
            }
        });
    }

    // ---- Form Update (step by step), mengikuti mockup ----
    function inp(name, val, type, locked, extra) {
        return '<input type="' + (type || 'text') + '" name="' + name + '" value="' + esc(val) + '"' + (locked ? ' disabled' : '') + (extra || '') + '>';
    }
    function note(locked, canEdit) {
        return locked ? '<p class="hint">' + ico('lock') + ' Terkunci</p>' : (canEdit ? '<p class="hint">' + ico('pencil') + ' Masih bisa dikoreksi</p>' : '');
    }

    // Bukti terima: semua file tersimpan (kolom utama + bukti_extra) + input file multiple + paste Ctrl+V.
    // File yang dipilih/dipaste disimpan di array `picked` karena form dibangun ulang saat tanggal berubah.
    var BUKTI_MAKS = 20, BUKTI_BYTES = 5 * 1024 * 1024;
    function savedBukti(r) {
        var a = r.bukti_terima ? [{path: r.bukti_terima, nama: r.bukti_terima_nama}] : [];
        return a.concat(r.bukti_extra || []);
    }
    function fileUrl(f) { return f._u || (f._u = URL.createObjectURL(f)); }
    function clearPicked() { picked.forEach(function (f) { if (f._u) { URL.revokeObjectURL(f._u); } }); picked = []; }
    // Satu kartu per file: gambar tampil sebagai thumbnail, PDF sebagai ikon; klik pratinjau atau nama membuka file di tab baru.
    function buktiCard(url, nama, isImg, ket, aksi) {
        var a = '<a href="' + esc(url) + '" target="_blank" rel="noopener"';
        return '<li class="bukti-picked">' + a + ' class="th" tabindex="-1" aria-hidden="true">' + (isImg ? '<img src="' + esc(url) + '" alt="">' : ico('file-pdf-o')) + '</a>' +
            '<span class="info">' + a + ' class="nm" title="' + esc(nama) + '">' + esc(nama) + '</a><span class="sub">' + ket + '</span></span>' + (aksi || '') + '</li>';
    }
    // File tersimpan (tidak bisa dihapus) diikuti file baru yang belum disimpan (bisa dibuang).
    function pickedHtml() {
        return savedBukti(cur.row).map(function (b, i) {
            return buktiCard(BUKTI + '/' + encodeURIComponent(cur.row.id) + '/' + i, b.nama || 'file', /\.(jpe?g|png)$/i.test(b.path), 'tersimpan');
        }).concat(picked.map(function (f, i) {
            return buktiCard(fileUrl(f), f.name, /^image\//.test(f.type), Math.max(1, Math.round(f.size / 1024)) + ' KB · belum disimpan',
                '<button type="button" class="linkbtn bukti-del" data-i="' + i + '" aria-label="Hapus ' + esc(f.name) + '">' + ico('trash') + ' Hapus</button>');
        })).join('');
    }
    function buktiHtml(r, locked) {
        var h = '';
        if (!locked) {
            h += '<div class="bukti-drop">' + ico('paperclip') + ' Tekan <b>Ctrl+V</b> untuk menempel screenshot / gambar yang di-copy, seret file ke sini, atau ' +
                '<button type="button" class="fu-btn g sm" id="bukti-pick">Pilih file</button><input type="file" id="bukti-file" accept=".pdf,.jpg,.jpeg,.png" multiple hidden></div>';
        }
        h += '<ul class="bukti-list" id="bukti-picked">' + pickedHtml() + '</ul>';
        if (!locked) {
            h += '<p class="hint" style="margin:4px 0 0">Ctrl+V bisa di mana saja di form ini; paste teks tetap masuk ke kotak isian. File baru ditambahkan ke daftar, bukan mengganti.</p>';
        } else if (!savedBukti(r).length) {
            h += '<p class="hint" style="margin:4px 0 0">Tidak ada bukti terima.</p>';
        }
        return h;
    }
    // Tambah file ke `picked` setelah validasi tipe, ukuran, dan jumlah. Mengembalikan false bila ada yang ditolak.
    function addPicked(files) {
        var ok = true, saved = cur ? savedBukti(cur.row).length : 0;
        $.each(files, function (_, fl) {
            if (!/\.(pdf|jpe?g|png)$/i.test(fl.name)) { mfail('"' + fl.name + '" ditolak: bukti terima harus PDF, JPG, atau PNG.'); ok = false; return; }
            if (fl.size > BUKTI_BYTES) { mfail('"' + fl.name + '" ditolak: ukuran maksimal 5 MB.'); ok = false; return; }
            if (picked.length >= BUKTI_MAKS) { mfail('Maksimal ' + BUKTI_MAKS + ' file per simpan.'); ok = false; return; }
            picked.push(fl);
        });
        $('#bukti-picked').html(pickedHtml());
        if (ok) { $('#fu-modal-msg').hide(); }
        return ok;
    }
    function pasteName(fl, n) {
        // Screenshot dari clipboard bernama generik "image.png": beri nama tempel-YYYYMMDD-HHMM-n.ext (MP-FR-20)
        var d = new Date(), p = function (x) { return ('0' + x).slice(-2); }, ext = ((fl.type || '').split('/')[1] || 'png').replace('jpeg', 'jpg');
        return 'tempel-' + d.getFullYear() + p(d.getMonth() + 1) + p(d.getDate()) + '-' + p(d.getHours()) + p(d.getMinutes()) + '-' + n + '.' + ext;
    }

    // Jarak jadwal FU (sama dengan Piutang_aging::FU_OFFSET dan mockup): FU1 = terima + 3, FU2 = FU1 + 7, FU3 = FU2 + 14, FU4 = FU3 + 14, FU tambahan = +14.
    var GAP = [3, 7, 14, 14];
    function gapOf(ke) { return GAP[ke - 1] || 14; }
    function fuRule(ke) { return ke === 1 ? 'tanggal terima + ' + gapOf(1) + ' hari' : 'FU ' + (ke - 1) + ' dilakukan + ' + gapOf(ke) + ' hari'; }

    function fuFields(x, N, lkf, done) {
        var plan = x.plan || (cur.suggest && cur.suggest[x.ke]) || '';
        return '<div class="frow"><label>Jadwal follow up' + inp(N('plan'), plan, 'date', lkf || done) +
            (done ? '' : '<span class="hint" style="margin:0">Otomatis: ' + fuRule(x.ke) + '. Boleh diubah / diundur.</span>') + '</label>' +
            '<label>Tanggal dilakukan<span class="ingrp">' + inp(N('aktual'), x.aktual, 'date', lkf) +
            (lkf ? '' : '<button type="button" class="fu-btn g sm fu-today" data-name="' + N('aktual') + '">Hari ini</button>') + '</span></label>' +
            '<label>Keterangan / jawaban customer' + inp(N('ket'), x.ket, 'text', lkf) + '</label></div>' +
            '<div class="frow"><label><span>Status FU <span class="req">(wajib bila tanggal dilakukan diisi)</span></span><select name="' + N('status') + '"' + (lkf ? ' disabled' : '') + '><option value="">- Pilih -</option>' +
            ((cur.status_opts && cur.status_opts[x.ke]) || []).map(function (v) { return '<option' + (x.status === v ? ' selected' : '') + '>' + esc(v) + '</option>'; }).join('') + '</select></label>' +
            (x.status === BM ? '<span class="hint" style="margin:0">Follow up berhenti; jelaskan masalahnya di keterangan.</span>' : '') + '</div>' +
            (lkf ? '' : '<p class="fu-warn" hidden>' + WARN_FU + '</p>' + BMWARN + '<div class="fu-next" hidden></div>');
    }
    // Peringatan saat status INVOICE BERMASALAH dipilih (mockup stEffectHtml), sebelum disimpan.
    var BMWARN = '<p class="fu-bmwarn" hidden>' + ico('exclamation-triangle') + ' <b>INVOICE BERMASALAH</b>: setelah disimpan follow up berhenti, tidak ada jadwal FU berikutnya, ' +
        'plan bayar tidak bisa diisi, Close ditolak, dan invoice pindah ke tab Invoice Bermasalah. Jelaskan penyebabnya (batal, revisi invoice, customer menolak, dll.) di keterangan.</p>';

    // Blok FU tambahan baru (FU 5 dst.): jadwal otomatis +14 hari dari FU sebelumnya dilakukan; bisa dibatalkan sebelum disimpan.
    function extraBlock(ke, plan) {
        var i = ke - 5, n = function (k) { return 'extra_fu[' + i + '][' + k + ']'; };
        return '<div class="fuact fu-extra-new" data-ke="' + ke + '"><div class="fuact-t">FU ' + ke + ' <span class="fu-pill st-diterima">baru</span> ' +
            '<span class="fu-pill unsaved fu-unsaved" hidden>belum disimpan</span> <button type="button" class="linkbtn fu-extra-cancel">Batal tambah FU ' + ke + '</button></div>' +
            '<input type="hidden" name="' + n('ke') + '" value="' + ke + '">' +
            '<div class="frow"><label>Jadwal follow up' + inp(n('plan'), plan || '', 'date') + '<span class="hint" style="margin:0">Otomatis: FU ' + (ke - 1) + ' dilakukan + 14 hari. Boleh diubah.</span></label>' +
            '<label>Tanggal dilakukan<span class="ingrp">' + inp(n('aktual'), '', 'date') + '<button type="button" class="fu-btn g sm fu-today" data-name="' + n('aktual') + '">Hari ini</button></span></label>' +
            '<label>Keterangan / jawaban customer' + inp(n('ket'), '', 'text') + '</label></div>' +
            '<div class="frow"><label><span>Status FU <span class="req">(wajib bila tanggal dilakukan diisi)</span></span><select name="' + n('status') + '"><option value="">- Pilih -</option>' +
            ((cur.status_opts && cur.status_opts[ke]) || cur.extra_status_opts || []).map(function (v) { return '<option>' + esc(v) + '</option>'; }).join('') + '</select></label></div>' +
            '<p class="fu-warn" hidden>' + WARN_FU + '</p>' + BMWARN + '<div class="fu-next" hidden></div></div>';
    }

    function addExtraBtn(ke) {
        return '<div class="funext"><button type="button" class="fu-btn g sm" id="btn-add-fu" data-ke="' + ke + '">+ Tambah FU ' + ke + '</button>' +
            '<span class="hint" style="margin:0">Bila customer belum memberi rencana bayar. Jadwal otomatis 14 hari setelah FU ' + (ke - 1) + ' dilakukan.</span></div>';
    }

    // Reaksi form FU aktif: pengingat status/keterangan, peringatan bermasalah, pill "belum disimpan", dan usulan jadwal FU berikutnya
    // (FU 1-3: input jadwal FU n+1 yang ikut disimpan; FU 4 dst.: tombol tambah FU berikutnya). Mockup fuRender.
    function fuActUpdate($a) {
        var ke = Number($a.data('ke')), akt = $a.find('input[name*="aktual"]').val(), st = $a.find('select[name*="status"]').val(), ket = $.trim($a.find('input[name*="[ket]"], input[name$="_ket"]').val());
        var bm = st === BM, $n = $a.children('.fu-next');
        $a.find('.fu-warn').first().prop('hidden', !(akt && (!st || !ket)));
        $a.children('.fu-bmwarn').prop('hidden', !bm);
        $a.find('.fu-unsaved').first().prop('hidden', !akt);
        if (!akt || bm) { $n.prop('hidden', true).find('input').prop('disabled', true); return; }
        var next = ke + 1, sug = addDays(akt, gapOf(next));
        if (ke < 4) {
            if (!$n.find('input').length) {
                $n.html('<label>Jadwal FU ' + next + inp('fu' + next + '_plan', sug, 'date', false, ' data-auto="1"') + '<span class="hint" style="margin:0">Otomatis: FU ' + ke + ' dilakukan + ' + gapOf(next) + ' hari. Boleh diubah; FU ' + next + ' mulai berjalan setelah Simpan.</span></label>');
            }
            var $i = $n.find('input').prop('disabled', false);
            if ($i.attr('data-auto')) { $i.val(sug); } // belum diubah user: ikut tanggal dilakukan
        } else if (!$a.nextAll('.fuact[data-ke=' + next + ']').length) {
            $n.html('<button type="button" class="fu-btn g sm fu-add-next" data-ke="' + next + '" data-plan="' + sug + '">+ Tambah FU ' + next + '</button> ' +
                '<span class="hint" style="margin:0">Bila customer belum memberi rencana bayar. Jadwal otomatis ' + dmy(sug) + '.</span>');
        }
        $n.prop('hidden', false);
    }

    // Status invoice -> tahap modal (sama dengan tab 1-3; plan_bayar/close hanya dilihat).
    var STAGE = {belum_kirim: 1, dikirim: 2, diterima: 3, plan_bayar: 3, close: 3};

    function buildForm(d) {
        var r = d.row, L = d.locks, all = L.all, ro = all || !MANAGE;
        var h = '<form id="fu-form" class="frm"><input type="hidden" name="id" value="' + esc(r.id) + '">';
        h += '<p class="hint">' + esc(r.customer) + ' · sisa piutang <b>' + nf(r.saldo) + '</b> dari ' + nf(r.nilai) + '</p>';
        if (r.status === 'plan_bayar') {
            h += '<div class="okmsg">' + ico('lock') + ' Customer sudah memberi rencana bayar <b>' + dmy(r.plan_bayar) + '</b>. Data tidak bisa diubah lagi; klik <b>Close</b> di daftar setelah pembayaran diterima.</div>';
        } else if (r.status === 'close') {
            h += '<div class="okmsg">' + ico('lock') + ' Invoice sudah di-close. Data hanya bisa dilihat.</div>';
        } else if (!MANAGE) {
            h += '<div class="okmsg">' + ico('lock') + ' Anda hanya dapat melihat data ini.</div>';
        }
        if (r.bermasalah && r.status !== 'close') { // mockup: banner di atas form
            h += '<div class="okmsg danger">' + ico('exclamation-triangle') + ' <b>INVOICE BERMASALAH</b>' + (r.ket_bermasalah ? ': ' + esc(r.ket_bermasalah) : '') +
                '. Follow up berhenti dan plan bayar tidak bisa diisi; piutang tetap dihitung. Penyelesaiannya menunggu prosedur invoice batal / revisi.</div>';
        }
        // Modal hanya memuat bagian untuk tahap invoice (sama dengan tab tempat invoice berada): 1 kirim, 2 konfirmasi diterima, 3 = follow up + plan bayar.
        var stage = STAGE[r.status] || 3;
        d.stage = stage;
        if (!ro) { h += '<p class="hint">' + ico('info-circle') + ' Isi bagian tahap aktif, lalu klik Simpan. Bagian tahap sebelumnya tampil terkunci (hanya lihat).</p>'; }
        // Bagian tahap sebelumnya tetap tampil tetapi terkunci (input disabled tidak ikut terkirim, data tersimpan tidak berubah).

        if (stage >= 1) {
            var lk = ro || L.kirim || stage > 1;
            h += '<h3>1. Pengiriman Invoice</h3><div class="frow"><label>Tanggal kirim' + inp('tgl_kirim', r.tgl_kirim, 'date', lk) + '</label>' +
                '<label>Dikirim via<select name="kirim_via"' + (lk ? ' disabled' : '') + '><option value="">- Pilih -</option>' +
                VIA.map(function (v) { return '<option' + (r.kirim_via === v ? ' selected' : '') + '>' + v + '</option>'; }).join('') +
                (r.kirim_via && VIA.indexOf(r.kirim_via) < 0 ? '<option selected>' + esc(r.kirim_via) + '</option>' : '') + '</select></label>' +
                '<label id="ket-kirim-wrap"' + (r.kirim_via ? '' : ' hidden') + '><span id="ket-kirim-lbl">' + (r.kirim_via === 'Driver' ? 'Keterangan Driver (nama driver, dll)' : 'Keterangan JNE (layanan, dll)') + '</span>' + inp('ket_kirim', r.ket_kirim, 'text', lk) + '</label></div>' +
                (lk && r.tgl_kirim ? '<p class="hint" style="margin-top:6px">' + ico('lock') + ' Data pengiriman terkunci karena invoice sudah dikirim.</p>' :
                    (ro ? '' : '<p class="hint" style="margin-top:6px">' + ico('pencil') + ' Setelah disimpan, data pengiriman terkunci. No resi diisi di tahap berikutnya (Konfirmasi Diterima).</p>'));
        }
        // 2 diterima (termasuk no resi)
        if (stage >= 2) {
            var lt = ro || L.terima || stage > 2, jne = r.kirim_via === 'JNE';
            // Invoice lama yang sudah terima tapi belum punya bukti: bukti (wajib) tetap bisa dilengkapi walau data terima terkunci.
            var lb = lt && !(d.bukti_boleh && !ro);
            h += '<h3>2. Diterima Customer</h3><div class="frow"><label>Tanggal terima' + inp('tgl_terima', r.tgl_terima, 'date', lt) + '</label>' +
                '<label class="span2">Keterangan' + inp('ket_terima', r.ket_terima, 'text', lt, ' placeholder="Nama penerima"') + '</label></div>' +
                '<div class="frow" style="margin-top:8px">' + (jne ? '<label><span>No resi JNE' + (stage === 2 ? ' <b class="req">*</b>' : '') + '</span>' +
                inp('no_resi', r.no_resi, 'text', ro || L.resi || stage > 2, ' maxlength="100"') + '</label>' : '') +
                // No faktur bisa dikoreksi sampai FU 1 disimpan, juga di tahap 3 (MP-FR-18); server mengunci setelah FU 1.
                '<label><span>No faktur <b class="req">*</b></span>' + inp('no_faktur', r.no_faktur, 'text', ro || !!L.faktur, ' maxlength="100"') + '</label>' +
                '<p class="hint' + (jne ? '' : ' span2') + '" style="margin:0;align-self:end">' + (jne ? 'No resi wajib untuk pengiriman via JNE.' : 'Dikirim via ' + esc(r.kirim_via || '–') + ': tidak perlu no resi.') +
                (!ro && !L.faktur ? '<br>No faktur dari Invoicing; koreksi di sini sampai FU 1 disimpan ikut mengubah No faktur di Invoicing.' : '') + '</p></div>' +
                '<div class="bukti-box' + (lb ? '' : ' bukti-open') + '">Bukti terima <span class="asof">(JPG / PNG / PDF, maks 5 MB per file, boleh lebih dari 1, <span class="req">wajib minimal 1</span>)</span>' + buktiHtml(r, lb) +
                (lt && !lb ? '<p class="hint" style="margin:4px 0 0">' + ico('exclamation-triangle') + ' Invoice ini belum punya bukti terima. Lengkapi bukti agar data bisa disimpan.</p>' : '') + '</div>' +
                (lt && r.tgl_terima ? '<p class="hint" style="margin-top:6px">' + ico('lock') + ' Terkunci: tanggal terima' + (jne ? ', no resi' : '') + ', dan keterangan sudah disimpan' +
                    (L.faktur ? '; no faktur terkunci karena FU 1 sudah disimpan' : '') + '.</p>'
                    : '<p class="hint" style="margin-top:6px">' + ico('pencil') + ' Umur piutang dihitung dari tanggal terima. Tanpa tanggal terima, invoice belum masuk Monitoring Piutang. No resi dan tanggal terima terkunci setelah disimpan.</p>');
        }
        // 3 follow up + 4 plan bayar
        if (stage === 3) {
            h += '<h3>3. Follow Up</h3><div class="picbox"><div class="frow"><label><span>Nama PIC penerima follow up <span class="asof">(opsional)</span></span>' + inp('fu_pic_nama', r.fu_pic_nama, 'text', ro, ' placeholder="Nama orang yang dihubungi"') + '</label>' +
                '<label><span>No WA / Telepon PIC <span class="asof">(opsional)</span></span>' + inp('fu_pic_telp', r.fu_pic_telp, 'tel', ro, ' placeholder="0812xxxxxxxx"') + '</label></div>' +
                '<p class="hint" style="margin:6px 0 0">Tidak wajib. Berlaku untuk semua FU; nomor harus 8–20 digit (boleh +, spasi, tanda -). Bisa diubah atau dikosongkan sampai plan bayar disimpan.</p></div>' +
                '<p class="hint">Follow up berjalan satu per satu. Isi <b>tanggal dilakukan</b> setelah follow up benar-benar dilakukan, lalu FU berikutnya dijadwalkan otomatis.</p>';
            var doneHtml = '', activeHtml = '', shownCurrent = false, lastDone = -1;
            d.fu.forEach(function (x, i) { if (x.aktual) { lastDone = i; } });
            d.fu.forEach(function (x, i) {
                var done = !!x.aktual, isCur = !done && !shownCurrent;
                if (!done && !isCur) { return; }
                if (isCur) { shownCurrent = true; }
                var lkf = ro || !!L.fu[x.ke] || done; // FU yang sudah dilakukan langsung terkunci (hanya lihat)
                var N = function (k) { return x.ke <= 4 ? 'fu' + x.ke + '_' + k : 'extra_fu[' + (x.ke - 5) + '][' + k + ']'; };
                var extraKe = x.ke > 4 ? '<input type="hidden" name="' + N('ke') + '" value="' + x.ke + '">' : '';
                if (done) {
                    var open = i === lastDone;
                    doneHtml += '<li><button type="button" class="fuhead" data-fuopen aria-expanded="' + open + '">' + ico(open ? 'caret-down' : 'caret-right') + ' ' + ico('check') + ' <b>FU ' + x.ke + '</b> dilakukan ' + dmy(x.aktual) +
                        (x.status ? ' · ' + stPill(x.status) : '') + ' <span class="fu-pill st-close">' + ico('lock') + ' terkunci</span></button>' +
                        '<div class="fubody"' + (open ? '' : ' hidden') + '>' + extraKe + fuFields(x, N, lkf, true) + '</div></li>';
                } else {
                    // FU tambahan tersimpan yang belum dilakukan boleh dihapus (server menghapus FU tambahan yang tidak ikut terkirim).
                    var del = x.ke > 4 && !lkf ? ' <button type="button" class="linkbtn fu-extra-del" data-ke="' + x.ke + '">Hapus FU ' + x.ke + '</button>' : '';
                    activeHtml += '<div class="fuact" data-ke="' + x.ke + '"><div class="fuact-t">FU ' + x.ke + ' <span class="fu-pill st-diterima">berjalan</span> <span class="fu-pill unsaved fu-unsaved" hidden>belum disimpan</span>' + del + '</div>' + extraKe + fuFields(x, N, lkf, false) +
                        '<p class="hint" style="margin:6px 0 0">' + (x.ke < 4 ? 'Setelah FU ' + x.ke + ' disimpan sebagai dilakukan, FU ' + (x.ke + 1) + ' dijadwalkan otomatis ' + gapOf(x.ke + 1) + ' hari setelah tanggal dilakukan. Jadwalnya masih bisa diubah saat FU ' + (x.ke + 1) + ' berjalan.' :
                            (x.ke === 4 ? 'FU terakhir dari standar. Setelah FU 4 dilakukan, bisa tambah FU 5 dst. bila customer belum memberi rencana bayar. Jadwal boleh diundur sampai ada plan bayar.' : 'Follow up tambahan di luar standar FU 1 – 4. Jadwal boleh diundur sampai ada plan bayar.')) + '</p></div>';
                }
            });
            h += (doneHtml ? '<ul class="fudone">' + doneHtml + '</ul>' : '') + activeHtml;
            if (r.status === 'plan_bayar') {
                h += '<p class="hint">' + ico('stop') + ' Follow up berhenti karena customer sudah memberi rencana tanggal bayar.</p>';
            } else if (!activeHtml && ro) {
                h += '<p class="hint">' + ico('check') + ' Semua follow up sudah dilakukan.</p>';
            }
            if (!ro && d.can_add_extra) { h += addExtraBtn(d.next_extra_ke); }
            if (r.fu_stage !== 'bermasalah') // invoice bermasalah: banner di atas form, tanpa bagian plan bayar
            h += '<h3>4. Plan Bayar Customer</h3><div class="frow"><label>Plan tanggal bayar' + inp('plan_bayar', r.plan_bayar, 'date', ro) + '</label>' +
                '<label class="span2">Keterangan' + inp('ket_plan_bayar', r.ket_plan_bayar, 'text', ro) + '</label></div>' +
                (ro ? '' : '<p class="hint" style="margin-top:6px">' + ico('exclamation-triangle') + ' Setelah rencana bayar disimpan, follow up berhenti dan data invoice ini tidak bisa diubah lagi. Invoice pindah ke tab <b>4. Tunggu Bayar &amp; Close</b>, tinggal di-Close setelah pembayaran diterima.</p>');
        }
        // Langkah berikutnya (mockup #nextStep), hanya bila form bisa diisi.
        var NEXT = {1: 'Isi tanggal kirim dan dikirim via, lalu Simpan. Bagian Diterima Customer muncul setelah data pengiriman tersimpan.',
            2: 'Isi tanggal terima' + (r.kirim_via === 'JNE' ? ', no resi JNE' : '') + ', no faktur, dan bukti terima, lalu Simpan. Follow up muncul setelah tanggal terima tersimpan.',
            3: 'Isi FU yang berjalan (tanggal dilakukan, status, keterangan), atau isi plan bayar bila customer sudah memberi rencana tanggal bayar.'};
        if (!ro && !(stage === 3 && r.bermasalah)) { h += '<p class="hint" id="fu-nextstep" style="margin-top:12px">' + ico('info-circle') + ' ' + NEXT[stage] + '</p>'; }
        $('#fu-modal-body').html(h + '</form>');
        $('#btn-save').toggle(!ro);
    }

    function openForm(id) {
        $('#fu-msg').hide();
        $.getJSON(BASE + '/form/' + encodeURIComponent(id)).done(function (d) {
            if (d.status !== 'success') { say(d.msg || 'Form follow up tidak dapat dibuka.', 'danger'); return; }
            cur = d; clearPicked(); $('#fu-modal-msg').hide(); $('#fu-log').empty();
            $('#btn-history').text('Riwayat perubahan' + (d.log_count != null ? ' (' + d.log_count + ')' : ''));
            $('#fu-modal-title').text((d.locks.all || !MANAGE ? 'Follow Up ' : 'Update Follow Up ') + d.row.no_invoice); buildForm(d); $('#fu-modal').modal('show');
            if (resolveTab) { // tautan ?open= tanpa tab: tampilkan daftar di tab invoice itu di belakang form
                resolveTab = false;
                var t = tabOf(d.row);
                if (t === 'close' && d.row.close_date) { $('#f-close-tahun').val(String(d.row.close_date).substr(0, 4)); }
                if (t && t !== f.tab) { group = groupOf(t); f.tab = t; lastSub[group] = t; flashId = id; renderChrome(); load(); }
            }
        }).fail(function (x) { say((x.responseJSON || {}).msg || 'Form follow up gagal dimuat. Coba lagi; bila terulang, hubungi admin.', 'danger'); });
    }

    // Close invoice: modal dengan keterangan wajib (menggantikan prompt()).
    function openClose(x) {
        closeRow = x; $('#fu-close-err').text(''); $('#fu-close-ket').val('');
        $('#fu-close-title').text('Close Invoice ' + x.no_invoice);
        $('#fu-close-info').html(esc(x.customer) + ' · plan bayar <b>' + dmy(x.plan_bayar) + '</b>');
        $('#fu-close-modal').modal('show');
    }

    $('#fu-groups').on('click', 'button', function () { setGroup($(this).data('group')); });
    $('#fu-tabs').on('click', 'button', function () { f.tab = String($(this).data('tab')); f.fu = ''; lastSub[group] = f.tab; renderChrome(); load(); });
    $('#fu-msg').on('click', '.x', function () { $('#fu-msg').hide(); });
    $('#fu-msg').on('click', '[data-goto]', function () { $('#fu-msg').hide(); goTab(String($(this).data('goto')), $(this).data('id'), false); });
    $('#fu-cards').on('click', '.fu-card', function () {
        f.tab = String($(this).data('tab')); f.fu = String($(this).data('fu') || '');
        if (f.tab === 'close') { // card Closed dihitung per periode: tab Invoice Close memakai tahun periode itu (mockup)
            var y = $('#f-inv-tahun').val() || String($('#f-inv-dari').val() || $('#f-inv-sampai').val()).substr(0, 4);
            if (/^\d{4}$/.test(y) && $('#f-close-tahun option').filter(function () { return this.value === y; }).length) { $('#f-close-tahun').val(y); }
        }
        renderChrome(); load();
    });
    // Delegasi di #fu-table: initTable() mengganti thead/tbody tiap ganti tab, handler di tbody ikut terbuang.
    $('#fu-table').on('click', 'tbody [data-act=buka]', function () {
        // tab tujuan + sorot baris; form Update langsung terbuka kecuali invoice bermasalah / close (mockup monBtn)
        var t = String($(this).data('tab')), id = $(this).data('id');
        goTab(t, id, !$(this).data('bm') && t !== 'close');
    });
    $('#fu-table').on('click', '.more', function () {
        var open = $(this).closest('.kt').toggleClass('open').hasClass('open');
        $(this).text(open ? 'ringkas ▴' : 'selengkapnya ▾').attr('aria-expanded', open);
    });
    $('#fu-kt-all').on('click', function () { $('#fu-table').toggleClass('kt-all'); $(this).text($('#fu-table').hasClass('kt-all') ? 'Ringkas semua keterangan' : 'Tampilkan semua keterangan'); });
    $('#f-fu').on('change', function () { f.fu = $(this).val(); renderChrome(); });
    $('#f-company, #f-fu, #f-plan-bulan, #f-plan-tahun, #f-close-tahun, #f-inv-tahun, #f-inv-bulan').on('change', function () { load(); });
    // Mengisi tanggal dari/sampai mengosongkan tahun/bulan supaya tidak saling bertabrakan (default bulan berjalan).
    $('#f-inv-dari, #f-inv-sampai').on('change', function () { if ($(this).val()) { $('#f-inv-tahun, #f-inv-bulan').val(''); } load(); });
    $('#f-inv-tahun, #f-inv-bulan').on('change', function () { $('#f-inv-dari, #f-inv-sampai').val(''); });
    $('#f-customer').on('change', function () { load(); }); // nama harus persis: muat ulang saat pilihan dipastikan, bukan tiap ketikan
    $('#fu-search').on('input', function () { var v = this.value; clearTimeout(window.__fuS); window.__fuS = setTimeout(function () { table.search(v).draw(); }, 400); });
    $('#btn-export').on('click', function () { window.location = BASE + '/export?' + $.param($.extend(params(), {cari: table.search()})); });
    $('#fu-table').on('click', 'tbody [data-act=update]', function () { openForm($(this).data('id')); });
    $('#fu-table').on('click', 'tbody [data-act=close]', function () { openClose(table.row($(this).closest('tr')).data()); });
    $('#btn-do-close').on('click', function () {
        var why = $.trim($('#fu-close-ket').val());
        if (!why) { $('#fu-close-err').text('Mohon isi keterangan close invoice.'); return; }
        var $b = $(this).prop('disabled', true);
        $.post(BASE + '/close', {id: closeRow.id, reason: why}).done(function (r) {
            $('#fu-close-modal').modal('hide'); say(r.msg || 'Invoice di-close.', 'success'); load(true);
        }).fail(function (x) { $('#fu-close-err').text((x.responseJSON || {}).msg || 'Gagal close invoice.'); }).always(function () { $b.prop('disabled', false); });
    });
    $('#fu-modal-body').on('click', '.fu-today', function () { $('[name="' + $(this).data('name') + '"]').val(TODAY); });
    $('#fu-modal-body').on('click', '[data-fuopen]', function () {
        var $b = $(this), open = $b.attr('aria-expanded') !== 'true';
        $b.attr('aria-expanded', open).next('.fubody').prop('hidden', !open);
        $b.find('.fa').first().attr('class', 'fa fa-' + (open ? 'caret-down' : 'caret-right'));
    });
    $('#fu-modal-body').on('input change', '.fuact', function (e) {
        if ($(e.target).is('.fu-next input')) { $(e.target).removeAttr('data-auto'); return; } // jadwal FU berikutnya diubah user: tidak ditimpa lagi
        fuActUpdate($(this));
    });
    // Tambah FU 5 dst. (semua FU tersimpan sudah dilakukan): jadwal = FU terakhir dilakukan + 14 hari.
    $('#fu-modal-body').on('click', '#btn-add-fu', function () {
        var ke = Number($(this).data('ke')), last = '';
        (cur.fu || []).forEach(function (x) { if (x.aktual) { last = x.aktual; } });
        $(this).closest('.funext').replaceWith(extraBlock(ke, last ? addDays(last, 14) : ''));
    });
    // Tambah FU berikutnya setelah FU 4 / FU tambahan diisi di sesi yang sama.
    $('#fu-modal-body').on('click', '.fu-add-next', function () {
        var $a = $(this).closest('.fuact');
        $a.after(extraBlock(Number($(this).data('ke')), String($(this).data('plan'))));
        $(this).closest('.fu-next').prop('hidden', true).empty();
    });
    $('#fu-modal-body').on('click', '.fu-extra-cancel', function () {
        var $a = $(this).closest('.fuact'), $prev = $a.prevAll('.fuact').first();
        $a.nextAll('.fu-extra-new').remove(); $a.remove(); // FU sesudahnya bergantung pada FU ini
        if ($prev.length) { fuActUpdate($prev); } else if (cur.can_add_extra) { $(this).closest('#fu-form').find('.fudone').after(addExtraBtn(cur.next_extra_ke)); }
    });
    $('#fu-modal-body').on('click', '.fu-extra-del', function () {
        var ke = $(this).data('ke');
        if (!confirm('Hapus FU ' + ke + '? FU ini belum dilakukan; penghapusan berlaku setelah Simpan.')) { return; }
        $(this).closest('.fuact').replaceWith('<p class="hint">' + ico('trash') + ' FU ' + ke + ' akan dihapus saat Simpan. Klik Batal untuk membatalkan.</p>');
    });
    $('#fu-modal-body').on('change', '[name=kirim_via]', function () {
        var v = $(this).val();
        $('#ket-kirim-wrap').prop('hidden', !v);
        $('#ket-kirim-lbl').text(v === 'Driver' ? 'Keterangan Driver (nama driver, dll)' : 'Keterangan JNE (layanan, dll)');
    });
    $('#fu-modal-body').on('click', '#bukti-pick', function () { $('#bukti-file').trigger('click'); });
    $('#fu-modal-body').on('change', '#bukti-file', function () {
        addPicked(this.files);
        this.value = ''; // pilihan berikutnya ditambahkan; daftar ada di `picked`
    });
    // Seret file ke kotak bukti (hanya saat kotak terbuka); drop di luar kotak tidak diproses.
    $('#fu-modal-body').on('dragenter dragover', '.bukti-open', function (e) { e.preventDefault(); $(this).addClass('drop'); });
    $('#fu-modal-body').on('dragleave', '.bukti-open', function () { $(this).removeClass('drop'); });
    $('#fu-modal-body').on('drop', '.bukti-open', function (e) {
        e.preventDefault(); $(this).removeClass('drop');
        var dt = (e.originalEvent || e).dataTransfer;
        if (dt && dt.files && dt.files.length) { addPicked(dt.files); }
    });
    $('#fu-modal-body').on('click', '.bukti-del', function () {
        var gone = picked.splice(parseInt($(this).data('i'), 10), 1)[0];
        if (gone && gone._u) { URL.revokeObjectURL(gone._u); }
        $('#bukti-picked').html(pickedHtml());
    });
    // Ctrl+V: hanya bereaksi bila clipboard berisi file dan input bukti sedang tampil (tahap 2 belum terkunci). Paste teks tidak diganggu.
    $('#fu-modal').on('paste', function (e) {
        var cd = (e.originalEvent || e).clipboardData;
        if (!$('#bukti-file').length || !cd || !cd.files || !cd.files.length) { return; }
        e.preventDefault();
        var n = 0, files = $.map(cd.files, function (fl) {
            n++;
            return /^image\//.test(fl.type) && /^image\.\w+$/i.test(fl.name) ? new File([fl], pasteName(fl, picked.length + n), {type: fl.type}) : fl;
        });
        addPicked(files);
    });
    $('#btn-save').on('click', function () {
        $('#fu-modal-msg').hide();
        // wajib: tanggal terima terisi harus punya file (yang sudah tersimpan atau yang baru dipilih); server memeriksa lagi
        if ($('[name=tgl_terima]').val() && !$.trim($('[name=no_faktur]').val())) { mfail('No faktur wajib diisi.'); return; }
        if ($('[name=tgl_terima]').val() && !picked.length && !savedBukti(cur.row).length) { mfail('Bukti terima (file) wajib diunggah.'); return; }
        if (cur.stage === 2 && cur.row.kirim_via === 'JNE' && $('[name=tgl_terima]').val() && !$.trim($('[name=no_resi]').val())) { mfail('No resi wajib diisi bila dikirim via JNE.'); return; }
        // PIC tidak wajib, tetapi nomor yang diisi harus valid (sama dengan Piutang_aging::valid_phone)
        var telp = $.trim($('[name=fu_pic_telp]').val() || ''), digits = telp.replace(/\D/g, '').length;
        if (telp && (!/^\+?[0-9 \-]+$/.test(telp) || digits < 8 || digits > 20)) { mfail('Nomor WA / telepon PIC harus 8-20 digit (boleh +, spasi, tanda -).'); return; }
        // plan bayar mengunci seluruh data dan tidak bisa diubah: minta konfirmasi saat pertama kali diisi
        if ($('[name=plan_bayar]').val() && !cur.row.plan_bayar && !confirm('Plan bayar akan MENGUNCI seluruh data invoice ini dan tidak bisa diubah. Lanjut simpan?')) { return; }
        var $b = $(this).prop('disabled', true), fd = new FormData($('#fu-form')[0]), no = cur.row.no_invoice;
        fd.delete('bukti_terima');
        picked.forEach(function (fl) { fd.append('bukti_terima[]', fl, fl.name); });
        $.ajax({url: BASE + '/save', type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json'})
            .done(function (res) {
                var id = cur.row.id, tab = (res && res.tab) || '';
                $('#fu-modal').modal('hide');
                sayGo(tab && tab !== f.tab ? 'Tersimpan. Invoice ' + no + ' pindah ke tab ' + TABLBL[tab] + '.' : 'Data follow up ' + no + ' tersimpan.', tab, id);
                flashId = tab === f.tab ? id : null; load(true);
            })
            .fail(function (x) { mfail((x.responseJSON || {}).msg || 'Gagal menyimpan. Coba lagi; bila terulang, hubungi admin.'); }).always(function () { $b.prop('disabled', false); });
    });
    // Riwayat tampil di dalam modal sebagai tabel (isi data_json diringkas), bukan alert().
    // Riwayat (mockup): tiap simpan menyimpan snapshot isi form; yang ditampilkan hanya bagian yang berubah dibanding simpan sebelumnya,
    // dengan label yang mudah dibaca (mis. "Tgl terima 2 Nov 2026 · FU1 ✓ SUDAH DI TERIMA DI KEUANGAN · Plan bayar 30 Nov 2026").
    function logObj(j) { try { var o = JSON.parse(j); return o && typeof o === 'object' ? o : {}; } catch (e) { return {}; } }
    function ringkas(o, prev) {
        var ch = function (k) { return o[k] != null && o[k] !== '' && (!prev || o[k] !== prev[k]); }, out = [];
        if (o.close_reason) { return 'Close · ' + esc(o.close_reason); }
        if (ch('tgl_kirim') || ch('kirim_via')) { out.push('Kirim ' + dmy(o.tgl_kirim) + (o.kirim_via ? ' via ' + esc(o.kirim_via) : '')); }
        if (ch('no_resi')) { out.push('Resi ' + esc(o.no_resi)); }
        if (ch('tgl_terima')) { out.push('Tgl terima ' + dmy(o.tgl_terima)); }
        if (ch('no_faktur')) { out.push((prev && prev.no_faktur ? 'koreksi faktur ' : 'No faktur ') + esc(o.no_faktur)); }
        if (ch('bukti_terima') || (o.bukti_baru && o.bukti_baru.length)) { out.push('bukti terima'); }
        if (ch('fu_pic_nama') || ch('fu_pic_telp')) { out.push('PIC ' + esc(o.fu_pic_nama || '') + (o.fu_pic_telp ? ' (' + esc(o.fu_pic_telp) + ')' : '')); }
        for (var i = 1; i <= 4; i++) {
            if (ch('fu' + i + '_aktual')) { out.push('FU' + i + ' ✓ ' + dmy(o['fu' + i + '_aktual']) + (o['fu' + i + '_status'] ? ' ' + esc(o['fu' + i + '_status']) : '')); }
            else if (ch('fu' + i + '_plan')) { out.push('FU' + i + ' dijadwalkan ' + dmy(o['fu' + i + '_plan'])); }
        }
        (o.extra_fu || []).forEach(function (e) {
            var p = ((prev && prev.extra_fu) || []).filter(function (x) { return String(x.ke) === String(e.ke); })[0] || {};
            if (e.aktual && e.aktual !== p.aktual) { out.push('FU' + e.ke + ' ✓ ' + dmy(e.aktual) + (e.status ? ' ' + esc(e.status) : '')); }
            else if (e.plan && e.plan !== p.plan) { out.push('FU' + e.ke + ' dijadwalkan ' + dmy(e.plan)); }
        });
        if (ch('plan_bayar')) { out.push('Plan bayar ' + dmy(o.plan_bayar) + (o.ket_plan_bayar ? ' (' + esc(o.ket_plan_bayar) + ')' : '')); }
        return out.join(' · ') || '<span class="asof">tanpa perubahan data</span>';
    }
    $('#btn-history').on('click', function () {
        $.getJSON(BASE + '/history/' + encodeURIComponent(cur.row.id)).done(function (r) {
            $('#btn-history').text('Riwayat perubahan (' + r.rows.length + ')');
            if (!r.rows.length) { $('#fu-log').html('<p class="hint" style="margin-top:10px">Belum ada riwayat.</p>'); return; }
            var objs = r.rows.map(function (x) { return logObj(x.data_json); }); // urutan terbaru dulu; pembanding = simpan sebelumnya (baris berikutnya)
            $('#fu-log').html('<div class="table-responsive" style="margin-top:10px"><table class="fu-tbl" style="min-width:0"><thead><tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Isi</th></tr></thead><tbody>' +
                r.rows.map(function (x, i) {
                    return '<tr><td class="no">' + dmy(x.created_date) + ' ' + esc(String(x.created_date || '').substr(11, 5)) + '</td><td>' + esc(x.created_by_name || x.created_by) + '</td><td>' + esc(x.aksi) + '</td><td>' +
                        ringkas(objs[i], x.aksi === 'close' ? null : objs.slice(i + 1).filter(function (_, k) { return r.rows[i + 1 + k].aksi !== 'close'; })[0]) + '</td></tr>';
                }).join('') + '</tbody></table></div>');
        }).fail(function () { mfail('Riwayat perubahan gagal dimuat. Coba lagi.'); });
    });
    // Tautan dari Monitoring / Invoicing: ?tab=<tab>&open=<id invoice>. Tab dibuka, filter periode dikosongkan supaya invoice
    // terlihat di daftar, baris disorot, dan form Update dibuka.
    (function () {
        var q = {}; location.search.replace(/^\?/, '').split('&').forEach(function (p) { var kv = p.split('='); if (kv[0]) { q[decodeURIComponent(kv[0])] = decodeURIComponent((kv[1] || '').replace(/\+/g, ' ')); } });
        if (q.tab && TABLBL[q.tab]) { group = groupOf(q.tab); f.tab = q.tab; lastSub[group] = q.tab; }
        if (q.open) {
            $('#f-inv-tahun, #f-inv-bulan').val('');
            if (q.tab === 'close' && /^\d{4}$/.test(q.close_tahun || '')) { $('#f-close-tahun').val(q.close_tahun); }
            if (q.tab && TABLBL[q.tab]) { flashId = q.open; openAfter = q.open; } else { resolveTab = true; openForm(q.open); } // tanpa tab (dari Invoicing): tab ditentukan dari data invoice
        }
    })();
    // Tutorial (mockup TOUR_CFG, alur "Invoice sampai Close" langkah 1-19). Langkah Invoicing hanya penjelasan (menu lain);
    // langkah Follow Up membuka tab / form otomatis dengan periode Semua supaya contoh tampil. Tidak ada yang disimpan.
    function tourTab(t) { return function () { $('#fu-modal').modal('hide'); $('#f-inv-tahun, #f-inv-bulan, #f-inv-dari, #f-inv-sampai').val(''); goTab(t, null, false); }; }
    function tourForm(t) { return function () { tourTab(t)(); tourOpenFirst = true; }; }
    var FU_STEPS = [
        {pg: 'Follow Up Piutang', go: tourTab('1'), el: '#fu-groups', t: '8. Tiga tab utama', d: '<b>Proses</b> (bekerja, tab 1 - 4), <b>Monitoring</b> (hanya melihat), <b>Perlu Perhatian &amp; Selesai</b> (FU terlambat, bermasalah, close). Tutorial memilih periode Semua supaya contoh tampil.', wait: 900},
        {el: '#fu-cards', t: '9. Card ringkasan', d: 'Jumlah invoice dan nominal per tahap. Klik card untuk membuka daftarnya.'},
        {el: '#fu-table tbody [data-act=update]', t: '10. Update', d: 'Klik <b>Update</b> untuk mengisi tahap invoice.'},
        {pg: 'Proses → 1. Kirim Invoice', go: tourForm('1'), wait: 1600, el: '#fu-form', t: '11. Kirim invoice', d: 'Isi tanggal kirim dan dikirim via (JNE / Driver). Data kirim terkunci setelah disimpan, lalu invoice pindah ke tab 2.'},
        {el: '#btn-save', t: '12. Simpan', d: 'Simpan di setiap tahap. Tutorial tidak menyimpan apa pun.'},
        {pg: 'Proses → 2. Konfirmasi Diterima', go: tourForm('2'), wait: 1600, el: '#fu-form', t: '13. Konfirmasi diterima', d: 'Isi tanggal terima, no resi (wajib bila via JNE), no faktur, dan bukti terima (bisa Ctrl+V screenshot). Lalu pindah ke tab 3.'},
        {pg: 'Proses → 3. Follow Up Customer', go: tourForm('3'), wait: 1600, el: '#fu-form', t: '14. Follow up customer', d: 'FU 1 - 4 terjadwal otomatis. Isi status, keterangan, dan tanggal dilakukan; FU yang disimpan terkunci. Status INVOICE BERMASALAH menghentikan follow up. Plan bayar memindahkan invoice ke tab 4.'},
        {pg: 'Proses → 4. Tunggu Bayar & Close', go: tourTab('4'), wait: 900, el: '#fu-panel', t: '15. Tunggu bayar & close', d: 'Satu-satunya tempat <b>Close</b>: aktif bila sudah ada plan bayar dan sudah lunas. Keterangan close wajib.'},
        {pg: 'Perlu Perhatian & Selesai → FU Terlambat', go: tourTab('late'), wait: 900, el: '#fu-cards', t: '16. Perlu Perhatian & Selesai', d: 'Card FU terlambat, Invoice bermasalah, Closed. Tab FU Terlambat berisi follow up yang jadwalnya sudah lewat.'},
        {pg: 'Perlu Perhatian & Selesai → Invoice Bermasalah', go: tourTab('bermasalah'), wait: 900, el: '#fu-panel', t: '17. Invoice bermasalah', d: 'Follow up berhenti; keterangan menjelaskan kenapa (batal, revisi, dll.). Penyelesaiannya menunggu prosedur.'},
        {pg: 'Perlu Perhatian & Selesai → Invoice Close', go: tourTab('close'), wait: 900, el: '#fu-panel', t: '18. Invoice close', d: 'Invoice yang sudah close, per tahun close.'},
        {pg: 'Monitoring', go: tourTab('all'), wait: 900, el: '#fu-panel', t: '19. Monitoring', d: 'Semua invoice yang belum close, lengkap seperti Visual Control. Tombol <b>Buka di Proses</b> membuka invoice di tab kerjanya.'}
    ];
    var INV_STEPS = [
        {pg: 'Penerimaan → Invoicing', t: '1. Invoicing', d: 'Tab Konsultasi berisi termin SPK yang siap ditagih; tab Invoiced berisi invoice yang sudah dibuat. Menu: <a href="' + INVOICING + '">Invoicing</a>.'},
        {t: '2. Buat invoice', d: 'Klik tombol Buat Invoice di termin yang mau ditagih.'},
        {pg: 'Form Add Invoicing', t: '3. Nomor PO', d: 'Wajib diisi.'},
        {t: '4. Nomor Faktur', d: 'Wajib diisi. Nomor yang sama muncul (dan bisa dikoreksi sampai FU 1) di Follow Up → Konfirmasi Diterima.'},
        {t: '5. COA Penjualan', d: 'Pilih COA Penjualan di tabel jurnal (wajib).'},
        {t: '6. Save', d: 'Invoice masuk tab Invoiced dan langsung muncul di Follow Up Piutang → Proses → 1. Kirim Invoice.'},
        {pg: 'Invoicing · Invoiced', t: '7. Invoiced', d: 'Klik nomor invoice untuk membukanya di Follow Up Piutang.'}
    ];
    if (window.PiutangTour) {
        PiutangTour.mount(document.getElementById('fu-tour'), {title: 'Follow Up Piutang', page: FU_STEPS,
            flows: [{title: 'Invoice sampai Close', desc: 'Invoicing → Follow Up Piutang (Proses, Perlu Perhatian &amp; Selesai, Monitoring)', steps: INV_STEPS.concat(FU_STEPS)}], cleanup: function () { tourOpenFirst = false; }});
    }
    renderChrome(); applyTab();
})();
</script>
