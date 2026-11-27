<?php
// Tombol "Tutorial halaman ini" (mockup specs/docs/petty-cash). Isi $tour_title dan $tour_steps
// ([['el' => selector, 't' => judul, 'd' => deskripsi]]) sebelum include. Tidak mengubah data.
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/driver/driver.css') ?>">
<script src="<?= base_url('assets/plugins/driver/driver.js.iife.js') ?>"></script>
<script src="<?= base_url('assets/js/piutang-tutorial.js') ?>"></script>
<div id="petty-cash-tour"></div>
<script>
    if (window.PiutangTour) {
        PiutangTour.mount(document.getElementById('petty-cash-tour'), <?= json_encode(['title' => $tour_title, 'page' => $tour_steps], JSON_HEX_TAG) ?>);
    }
</script>
