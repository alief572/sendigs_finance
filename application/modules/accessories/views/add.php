<?php
$ENABLE_ADD     = has_permission('Master_Indirect.Add');
$ENABLE_MANAGE  = has_permission('Master_Indirect.Manage');
$ENABLE_VIEW    = has_permission('Master_Indirect.View');
$ENABLE_DELETE  = has_permission('Master_Indirect.Delete');

$id             = (!empty($header)) ? $header[0]->id : '';
$id_stock       = (!empty($header)) ? $header[0]->id_stock : '';
$id_category    = (!empty($header)) ? $header[0]->id_category : '';
$stock_name     = (!empty($header)) ? $header[0]->stock_name : '';
$trade_name     = (!empty($header)) ? $header[0]->trade_name : '';
$brand          = (!empty($header)) ? $header[0]->brand : '';
$spec           = (!empty($header)) ? $header[0]->spec : '';
$id_unit_gudang = (!empty($header)) ? $header[0]->id_unit_gudang : '';
$konversi       = (!empty($header)) ? $header[0]->konversi : '1';
$id_unit        = (!empty($header)) ? $header[0]->id_unit : '';
$min_order      = (!empty($header)) ? $header[0]->min_order : 0;
$coa            = (!empty($header)) ? $header[0]->no_coa : '';
$is_view        = (!empty($results['tanda']) && $results['tanda'] == 'view');
$is_edit        = (!empty($id) && !$is_view);
$is_add         = empty($id);

$status_val = 1;
if (!empty($id)) {
    $status_val = isset($header[0]->status) ? $header[0]->status : 1;
}

// Resolving category name
$category_name = '-';
if (!empty($results['category'])) {
    foreach ($results['category'] as $c) {
        if ($c->id == $id_category) {
            $category_name = ucwords(strtolower($c->nm_category));
            break;
        }
    }
}

// Resolving satuan names
$packing_name = '-';
if (!empty($results['satuan_packing'])) {
    foreach ($results['satuan_packing'] as $p) {
        if ($p->id == $id_unit_gudang) {
            $packing_name = strtoupper($p->code);
            break;
        }
    }
}

$unit_name = '-';
if (!empty($results['satuan'])) {
    foreach ($results['satuan'] as $u) {
        if ($u->id == $id_unit) {
            $unit_name = strtoupper($u->code);
            break;
        }
    }
}

// Resolving COA name
$coa_name = '-';
if (!empty($list_coa)) {
    foreach ($list_coa as $item) {
        if ($item->no_perkiraan == $coa) {
            $coa_name = $item->no_perkiraan . ' - ' . $item->nama;
            break;
        }
    }
}

$page_heading = 'Tambah Barang Stok Baru';
$page_desc    = 'Lengkapi data katalog barang stok, spesifikasi teknis, dan rasio konversi satuan.';
if ($is_view) {
    $page_heading = 'Detail Barang Stok';
    $page_desc    = 'Informasi lengkap spesifikasi material, konversi satuan, dan ketentuan inventaris.';
} elseif ($is_edit) {
    $page_heading = 'Edit Barang Stok';
    $page_desc    = 'Perbarui spesifikasi material, konversi satuan, atau status operasional barang.';
}
?>
<link rel="stylesheet" href="<?= base_url('assets/css/accessories-style.css?v=' . (defined('FCPATH') && file_exists(FCPATH . 'assets/css/accessories-style.css') ? filemtime(FCPATH . 'assets/css/accessories-style.css') : '1.0.1')) ?>">

<main class="accessories-page" id="accessories-form-page">
    <header class="accessories-page-header">
        <div>
            <h1><?= $page_heading; ?></h1>
            <p><?= $page_desc; ?></p>
        </div>
        <div class="accessories-actions-group">
            <a href="<?= base_url('accessories'); ?>" class="accessories-button accessories-button-secondary">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                <span>Kembali</span>
            </a>
            <?php if ($is_view && $ENABLE_MANAGE) : ?>
                <a href="<?= base_url('accessories/add/' . $id); ?>" class="accessories-button accessories-button-primary">
                    <i class="fa fa-pencil" aria-hidden="true"></i>
                    <span>Edit barang ini</span>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <div class="accessories-form-card">
        <div class="accessories-form-header">
            <h2>
                <i class="fa <?= $is_view ? 'fa-cubes' : ($is_edit ? 'fa-pencil-square-o' : 'fa-plus-circle'); ?>" aria-hidden="true"></i>
                <span><?= $is_view ? html_escape(strtoupper(strtolower($stock_name))) : $page_heading; ?></span>
            </h2>
            <?php if (!empty($id)) : ?>
                <span class="status-badge <?= ($status_val == '1' ? 'active' : 'inactive'); ?>" style="font-size: 11.5px; padding: 4px 12px;">
                    <?= ($status_val == '1' ? 'Status: Aktif' : 'Status: Non-Aktif'); ?>
                </span>
            <?php endif; ?>
        </div>

        <form id="data-form" method="post" autocomplete="off">
            <input type="hidden" id="id" name="id" value="<?= html_escape($id); ?>">

            <div class="accessories-form-body">
                <!-- Informasi Utama Barang -->
                <div class="accessories-section-header">
                    <span class="accessories-section-icon" aria-hidden="true"><i class="fa fa-info"></i></span>
                    <span>Informasi Utama Barang</span>
                </div>

                <div class="accessories-grid-2">
                    <div class="accessories-field">
                        <label for="id_category">Kategori Stok <?php if (!$is_view): ?><span class="req-star">*</span><?php endif; ?></label>
                        <?php if ($is_view): ?>
                            <div class="accessories-view-value"><?= html_escape($category_name); ?></div>
                        <?php else: ?>
                            <select id="id_category" name="id_category" class="form-control chosen-select" required>
                                <option value="0">- Pilih Kategori -</option>
                                <?php foreach ($results['category'] as $kel) : ?>
                                    <option value="<?= $kel->id; ?>" <?= ($kel->id == $id_category) ? 'selected' : ''; ?>>
                                        <?= html_escape(strtoupper(strtolower($kel->nm_category))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <div class="accessories-field">
                        <label for="stock_name">Nama Stok (Stock Name) <?php if (!$is_view): ?><span class="req-star">*</span><?php endif; ?></label>
                        <?php if ($is_view): ?>
                            <div class="accessories-view-value" style="font-weight: 600; color: #176454;">
                                <?= html_escape($stock_name); ?>
                            </div>
                        <?php else: ?>
                            <input type="text" id="stock_name" name="stock_name" class="form-control" required
                                   placeholder="Contoh: Lakban Bening 2 Inch" value="<?= html_escape($stock_name); ?>">
                        <?php endif; ?>
                    </div>

                    <div class="accessories-field">
                        <label for="id_stock">Item Code / Kode Barang</label>
                        <?php if ($is_view): ?>
                            <div class="accessories-view-value is-code">
                                <?= !empty($id_stock) ? html_escape($id_stock) : '<span class="accessories-view-empty">Belum ada kode</span>'; ?>
                            </div>
                        <?php else: ?>
                            <input type="text" id="id_stock" name="id_stock" class="form-control"
                                   placeholder="Contoh: ACC-001" value="<?= html_escape($id_stock); ?>">
                        <?php endif; ?>
                    </div>

                    <div class="accessories-field">
                        <label for="trade_name">Trade Name / Nama Dagang</label>
                        <?php if ($is_view): ?>
                            <div class="accessories-view-value">
                                <?= !empty($trade_name) ? html_escape($trade_name) : '<span class="accessories-view-empty">Tidak ada nama dagang</span>'; ?>
                            </div>
                        <?php else: ?>
                            <input type="text" id="trade_name" name="trade_name" class="form-control"
                                   placeholder="Nama dagang atau nama komersial" value="<?= html_escape($trade_name); ?>">
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Spesifikasi & Identitas Merk -->
                <div class="accessories-section-divider"></div>
                <div class="accessories-section-header">
                    <span class="accessories-section-icon" aria-hidden="true"><i class="fa fa-tag"></i></span>
                    <span>Spesifikasi & Identitas Merk</span>
                </div>

                <div class="accessories-grid-2">
                    <div class="accessories-field">
                        <label for="brand">Brand / Merk</label>
                        <?php if ($is_view): ?>
                            <div class="accessories-view-value">
                                <?= !empty($brand) ? html_escape(ucwords($brand)) : '<span class="accessories-view-empty">Tanpa merk spesifik</span>'; ?>
                            </div>
                        <?php else: ?>
                            <input type="text" id="brand" name="brand" class="form-control"
                                   placeholder="Contoh: 3M, Daimaru, dll" value="<?= html_escape($brand); ?>">
                        <?php endif; ?>
                    </div>

                    <div class="accessories-field">
                        <label for="spec">Spesifikasi Detail</label>
                        <?php if ($is_view): ?>
                            <div class="accessories-view-value">
                                <?= !empty($spec) ? html_escape($spec) : '<span class="accessories-view-empty">Tidak ada spesifikasi detail</span>'; ?>
                            </div>
                        <?php else: ?>
                            <input type="text" id="spec" name="spec" class="form-control"
                                   placeholder="Dimensi, ketebalan, material, grade, dll" value="<?= html_escape($spec); ?>">
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Satuan & Konversi Kemasan -->
                <div class="accessories-section-divider"></div>
                <div class="accessories-section-header">
                    <span class="accessories-section-icon" aria-hidden="true"><i class="fa fa-calculator"></i></span>
                    <span>Satuan & Konversi Kemasan</span>
                </div>

                <div class="accessories-grid-3">
                    <div class="accessories-field">
                        <label for="id_unit_gudang">Satuan Packing (Gudang)</label>
                        <?php if ($is_view): ?>
                            <div class="accessories-view-value is-code"><?= html_escape($packing_name); ?></div>
                        <?php else: ?>
                            <select id="id_unit_gudang" name="id_unit_gudang" class="form-control chosen-select">
                                <option value="0">- Pilih Satuan Packing -</option>
                                <?php foreach ($results['satuan_packing'] as $satuan) : ?>
                                    <option value="<?= $satuan->id; ?>" <?= ($satuan->id == $id_unit_gudang) ? 'selected' : ''; ?>>
                                        <?= html_escape(strtoupper($satuan->code)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <div class="accessories-field">
                        <label for="konversi">Nilai Konversi</label>
                        <?php if ($is_view): ?>
                            <div class="accessories-view-value" style="font-weight: 600;">
                                <?= number_format((float)$konversi, 2); ?>
                            </div>
                        <?php else: ?>
                            <div class="input-group" style="width: 100%;">
                                <input type="text" id="konversi" name="konversi" class="form-control autoNumeric text-right"
                                       placeholder="1" value="<?= html_escape($konversi); ?>">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="accessories-field">
                        <label for="id_unit">Unit Measurement (Satuan Terkecil)</label>
                        <?php if ($is_view): ?>
                            <div class="accessories-view-value is-code"><?= html_escape($unit_name); ?></div>
                        <?php else: ?>
                            <select id="id_unit" name="id_unit" class="form-control chosen-select">
                                <option value="0">- Pilih Satuan Terkecil -</option>
                                <?php foreach ($results['satuan'] as $satuan) : ?>
                                    <option value="<?= $satuan->id; ?>" <?= ($satuan->id == $id_unit) ? 'selected' : ''; ?>>
                                        <?= html_escape(strtoupper($satuan->code)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="conversion-callout">
                    <i class="fa fa-exchange" aria-hidden="true"></i>
                    <span>Rasio Konversi: <strong>1 <?= html_escape($packing_name != '-' ? $packing_name : 'Satuan Packing'); ?></strong> setara dengan <code><?= number_format((float)$konversi, 2); ?> <?= html_escape($unit_name != '-' ? $unit_name : 'Satuan Penggunaan'); ?></code></span>
                </div>

                <!-- Ketentuan Akuntansi & Pengadaan -->
                <div class="accessories-section-divider"></div>
                <div class="accessories-section-header">
                    <span class="accessories-section-icon" aria-hidden="true"><i class="fa fa-book"></i></span>
                    <span>Ketentuan Akuntansi & Pengadaan</span>
                </div>

                <div class="accessories-grid-2">
                    <div class="accessories-field">
                        <label for="min_order">Minimum Order Qty</label>
                        <?php if ($is_view): ?>
                            <div class="accessories-view-value">
                                <?= number_format((float)$min_order); ?>
                            </div>
                        <?php else: ?>
                            <input type="text" name="min_order" id="min_order" class="form-control autoNumeric text-right"
                                   placeholder="0" value="<?= html_escape($min_order); ?>">
                        <?php endif; ?>
                    </div>

                    <div class="accessories-field">
                        <label for="coa">Chart of Account (COA)</label>
                        <?php if ($is_view): ?>
                            <div class="accessories-view-value">
                                <?= !empty($coa) ? html_escape($coa_name) : '<span class="accessories-view-empty">Belum ditentukan</span>'; ?>
                            </div>
                        <?php else: ?>
                            <select class="form-control chosen-select" name="coa" id="coa">
                                <option value="">- Pilih Akun COA -</option>
                                <?php foreach ($list_coa as $item) : ?>
                                    <option value="<?= $item->no_perkiraan; ?>" <?= ($item->no_perkiraan == $coa) ? 'selected' : ''; ?>>
                                        <?= html_escape($item->no_perkiraan . ' - ' . $item->nama); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Status Operasional -->
                <?php if (!empty($id)) : ?>
                    <div class="accessories-section-divider"></div>
                    <div class="accessories-section-header">
                        <span class="accessories-section-icon" aria-hidden="true"><i class="fa fa-toggle-on"></i></span>
                        <span>Status Operasional</span>
                    </div>

                    <div class="accessories-field">
                        <label style="margin-bottom: 6px;">Status Aktif Master:</label>
                        <?php if ($is_view): ?>
                            <div>
                                <span class="status-badge <?= ($status_val == '1' ? 'active' : 'inactive'); ?>" style="font-size: 12px; padding: 5px 14px;">
                                    <i class="fa <?= ($status_val == '1' ? 'fa-check-circle' : 'fa-times-circle'); ?>" aria-hidden="true"></i>
                                    <?= ($status_val == '1' ? 'Aktif' : 'Non-Aktif'); ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="status-pill-group">
                                <label class="status-pill-option <?= ($status_val == '1' ? 'active-pill' : ''); ?>">
                                    <input type="radio" name="status" value="1" <?= ($status_val == '1' ? 'checked' : ''); ?>>
                                    <i class="fa fa-check" aria-hidden="true"></i> Aktif
                                </label>
                                <label class="status-pill-option <?= ($status_val == '0' ? 'inactive-pill' : ''); ?>">
                                    <input type="radio" name="status" value="0" <?= ($status_val == '0' ? 'checked' : ''); ?>>
                                    <i class="fa fa-times" aria-hidden="true"></i> Non-Aktif
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Footer Actions -->
            <div class="accessories-form-footer">
                <a href="<?= base_url('accessories'); ?>" class="accessories-button accessories-button-secondary">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    <span><?= $is_view ? 'Kembali ke Daftar' : 'Batal'; ?></span>
                </a>
                <?php if ($is_view && $ENABLE_MANAGE): ?>
                    <a href="<?= base_url('accessories/add/' . $id); ?>" class="accessories-button accessories-button-primary">
                        <i class="fa fa-pencil" aria-hidden="true"></i>
                        <span>Edit barang stok</span>
                    </a>
                <?php elseif (!$is_view): ?>
                    <button type="submit" class="accessories-button accessories-button-primary" id="save">
                        <i class="fa fa-check" aria-hidden="true"></i>
                        <span>Simpan data barang</span>
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</main>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

<script type="text/javascript">
    var base_url = '<?php echo base_url(); ?>';
    var active_controller = '<?php echo ($this->uri->segment(1)); ?>';

    $(document).ready(function() {
        if ($.fn.select2) {
            $('.chosen-select').select2({ width: '100%' });
        }
        if ($.fn.autoNumeric) {
            $('.autoNumeric').autoNumeric();
        }

        // GSAP page entrance animation
        if (window.gsap && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            window.gsap.fromTo('.accessories-page-header, .accessories-form-card', 
                { y: 14, opacity: 0.5 }, 
                { y: 0, opacity: 1, duration: 0.45, stagger: 0.08, ease: 'power2.out', clearProps: 'all' }
            );
        }

        // Interactive Status Pill Toggle
        $('input[name="status"]').on('change', function() {
            $('.status-pill-option').removeClass('active-pill inactive-pill');
            if ($(this).val() == '1') {
                $(this).closest('.status-pill-option').addClass('active-pill');
            } else {
                $(this).closest('.status-pill-option').addClass('inactive-pill');
            }
        });

        // Form Submit
        $('#data-form').on('submit', function(e) {
            e.preventDefault();

            var id_category = $('#id_category').val();
            var stock_name = $.trim($('#stock_name').val());

            if (id_category == '0' || id_category == '') {
                swal({
                    title: "Perhatian",
                    text: "Silakan pilih Kategori Stok terlebih dahulu!",
                    type: "warning"
                });
                return false;
            }
            if (!stock_name) {
                swal({
                    title: "Perhatian",
                    text: "Nama Stok wajib diisi!",
                    type: "warning"
                });
                $('#stock_name').focus();
                return false;
            }

            swal({
                title: "Konfirmasi Simpan",
                text: "Apakah data barang stok yang diisi sudah sesuai?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-primary",
                confirmButtonText: "Ya, Simpan!",
                cancelButtonText: "Batal",
                closeOnConfirm: false
            }, function(isConfirm) {
                if (isConfirm) {
                    var btn = $('#save');
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Menyimpan…');

                    var formData = $('#data-form').serialize();
                    var baseurl = siteurl + active_controller + '/add';
                    $.ajax({
                        url: baseurl,
                        type: "POST",
                        data: formData,
                        cache: false,
                        dataType: 'json',
                        success: function(data) {
                            if (data.status == 1 || data.status == '1') {
                                swal({
                                    title: "Berhasil!",
                                    text: data.pesan || "Data barang berhasil disimpan.",
                                    type: "success",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                setTimeout(function() {
                                    window.location.href = base_url + active_controller;
                                }, 1200);
                            } else {
                                btn.prop('disabled', false).html('<i class="fa fa-check" aria-hidden="true"></i> Simpan data barang');
                                swal({
                                    title: "Gagal Simpan",
                                    text: data.pesan || "Gagal menyimpan data barang.",
                                    type: "warning"
                                });
                            }
                        },
                        error: function() {
                            btn.prop('disabled', false).html('<i class="fa fa-check" aria-hidden="true"></i> Simpan data barang');
                            swal({
                                title: "Error!",
                                text: "Terjadi kesalahan saat memproses data ke server.",
                                type: "error"
                            });
                        }
                    });
                }
            });
        });
    });
</script>