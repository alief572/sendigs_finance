<?php

/**
 * Pelaporan Petty Cash - Konfirmasi sebelum Ajukan (mockup specs/docs/petty-cash).
 * Ringkasan, Kontrol Saldo, Detail Pencatatan (item + preview jurnal per pencatatan), tombol Ajukan.
 *
 * PHP vars:
 *   $pelaporan, $budget_info, $pencatatan_details, $coa_names, $journal_preview,
 *   $saldo_seharusnya, $has_add, $has_manage
 */
require_once __DIR__ . '/../partials/pc_review.php';

$header           = $pelaporan->header;
$pencatatan_list  = $pelaporan->pencatatan_list;
$saldo_seharusnya = isset($saldo_seharusnya) ? $saldo_seharusnya : null;
$bisa_ajukan      = ($header->status === 'draft' && ($has_add || $has_manage));

$books_by_id = [];
foreach ((isset($journal_preview) ? $journal_preview : []) as $jp) {
    $books_by_id[$jp['pencatatan']->id] = $jp['books'];
}

pc_style();

$tour_title = 'Konfirmasi Pelaporan';
$tour_steps = [];
if ($bisa_ajukan) {
    $tour_steps[] = ['el' => '#box-kontrol-saldo', 't' => 'Kontrol Saldo Petty Cash', 'd' => 'Saldo seharusnya = Total Budget &minus; pengeluaran yang belum di-refill, dibandingkan dengan hitung fisik uang.'];
}
$tour_steps[] = ['el' => '#box-detail-pencatatan', 't' => 'Detail & jurnal', 'd' => 'Preview jurnal per pencatatan dengan tanggal pencatatan; dibuat saat approve.'];
include __DIR__ . '/../_tutorial.php';

pc_summary($header, count($pencatatan_list), isset($budget_info->sisa_budget) ? $budget_info->sisa_budget : 0);
if ($bisa_ajukan) {
    pc_kontrol($budget_info, $header->grand_total, $saldo_seharusnya, ['editable' => true]);
}
?>

<!-- Detail Per Pencatatan -->
<div class="box box-solid" id="box-detail-pencatatan">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-list-alt"></i>&nbsp;Detail Pencatatan</h3>
    </div>
    <div class="box-body">
        <?php foreach (array_values($pencatatan_list) as $i => $item) : ?>
            <?php
            $detail_data = isset($pencatatan_details[$item->id]) ? $pencatatan_details[$item->id] : null;
            pc_panel($i + 1, $item, ($detail_data && isset($detail_data->details)) ? $detail_data->details : [], $coa_names, [
                'jurnal' => ['books' => isset($books_by_id[$item->id]) ? $books_by_id[$item->id] : false],
            ]);
            ?>
        <?php endforeach; ?>
        <?php if (empty($pencatatan_list)) : ?>
            <div class="callout callout-warning">
                <h4><i class="fa fa-info-circle"></i> Tidak Ada Data</h4>
                <p>Tidak ada pencatatan dalam pelaporan ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="text-center" style="margin:10px 0 20px">
    <?php if ($bisa_ajukan) : ?>
        <button type="button" class="btn btn-success btn-cf" id="btn-ajukan">
            <i class="fa fa-paper-plane"></i>&nbsp;Ajukan
        </button>
        &nbsp;&nbsp;
    <?php endif; ?>
    <a href="<?= site_url('expense_petty_cash/pelaporan') ?>" class="btn btn-default btn-cf">
        <i class="fa fa-arrow-left"></i>&nbsp;Kembali
    </a>
</div>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (function() {
        var BASE_URL = '<?= site_url('expense_petty_cash/') ?>';
        var PELAPORAN_ID = <?= json_encode($header->id) ?>;

        // Kontrol saldo: saldo seharusnya dari server (hanya untuk tampilan; server menghitung ulang saat Ajukan)
        var SALDO_SEHARUSNYA = <?= json_encode($saldo_seharusnya) ?>;
        var $saldoInput = $('#saldo_petty_cash');

        function parseRupiah(str) {
            var clean = String(str || '').replace(/[.,\s]/g, '');
            return /^\d+$/.test(clean) ? parseInt(clean, 10) : null;
        }

        function formatRupiah(num) {
            return String(num).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function hitungSelisih() {
            var saldo = parseRupiah($saldoInput.val());
            var $teks = $('#selisih-saldo-text');
            var $warn = $('#warning-selisih');
            if (saldo === null || SALDO_SEHARUSNYA === null) {
                $teks.text('-');
                $warn.hide();
                return null;
            }
            var selisih = saldo - SALDO_SEHARUSNYA;
            var angka = (selisih < 0 ? '-' : '') + formatRupiah(Math.abs(selisih));
            $teks.html(selisih === 0 ?
                '<span class="sel-ok"><i class="fa fa-check"></i> 0 (sesuai)</span>' :
                '<span class="sel-no"><i class="fa fa-exclamation-triangle"></i> ' + angka + '</span>');
            $warn.html('<i class="fa fa-exclamation-triangle"></i> <b>Ada selisih saldo Rp ' + angka + '</b> (' +
                (selisih < 0 ? 'uang fisik kurang' : 'uang fisik lebih') +
                ' dari saldo seharusnya). Pelaporan tetap bisa diajukan, tetapi selisih ini akan ditandai untuk dicek Dept Head Finance saat approval.')
                .toggle(selisih !== 0);
            return selisih;
        }

        $saldoInput.on('input', hitungSelisih).on('blur', function() {
            var saldo = parseRupiah($saldoInput.val());
            if (saldo !== null) {
                $saldoInput.val(formatRupiah(saldo));
            }
        });

        // Ajukan button
        var btnAjukan = document.getElementById('btn-ajukan');
        if (btnAjukan) {
            btnAjukan.addEventListener('click', function() {
                var saldoPetty = parseRupiah($saldoInput.val());
                if (saldoPetty === null) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Saldo Petty Cash wajib diisi',
                        text: 'Isi saldo hasil hitung fisik uang sebelum mengajukan pelaporan.'
                    }).then(function() {
                        $saldoInput.focus();
                    });
                    return;
                }
                var selisihAjukan = hitungSelisih();
                var peringatanSelisih = (selisihAjukan !== null && selisihAjukan !== 0) ?
                    '<div class="alert alert-warning" style="text-align:left; margin-top:10px;"><i class="fa fa-warning"></i> Ada selisih saldo <strong>' +
                    (selisihAjukan < 0 ? '-' : '+') + 'Rp ' + formatRupiah(Math.abs(selisihAjukan)) +
                    '</strong>. Pelaporan tetap dapat diajukan; Dept Head Finance akan mengecek selisih ini.</div>' : '';
                Swal.fire({
                    icon: 'question',
                    title: 'Ajukan Pelaporan?',
                    html: '<p>Status akan berubah menjadi <strong>"Waiting Approval"</strong> dan pencatatan terkait tidak dapat diedit.</p>' + peringatanSelisih,
                    showCancelButton: true,
                    confirmButtonColor: '#00a65a',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: '<i class="fa fa-paper-plane"></i> Ya, Ajukan!',
                    cancelButtonText: 'Batal',
                }).then(function(result) {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Memproses...',
                            allowOutsideClick: false,
                            didOpen: function() {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: BASE_URL + 'submit_pelaporan/' + PELAPORAN_ID,
                            type: 'POST',
                            data: {
                                saldo_petty_cash: saldoPetty
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status == true || response.status == '1') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message || 'Pelaporan berhasil diajukan.',
                                        timer: 2500,
                                        showConfirmButton: false,
                                    }).then(function() {
                                        window.location.href = BASE_URL + 'pelaporan';
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: response.message || 'Gagal mengajukan pelaporan.',
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Terjadi kesalahan. Silakan coba lagi.',
                                });
                            }
                        });
                    }
                });
            });
        }
    })();
</script>