<?php
$ENABLE_ADD     = has_permission('Invoicing.Add');
$ENABLE_MANAGE  = has_permission('Invoicing.Manage');
$ENABLE_VIEW    = has_permission('Invoicing.View');
$ENABLE_DELETE  = has_permission('Invoicing.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="<?= base_url('assets/css/invoicing.css?v=' . (file_exists(FCPATH . 'assets/css/invoicing.css') ? filemtime(FCPATH . 'assets/css/invoicing.css') : '1')) ?>">

<div class="box invoice-workspace" id="invoicing-workspace">
    <div class="invoice-heading">
        <div>
            <h2>List Penawaran Non Konsultasi</h2>
            <p>Pilih penawaran non konsultasi untuk diterbitkan invoice atau kelola status penawaran.</p>
        </div>
        <div class="invoice-heading-actions">
            <a href="<?= base_url('invoicing') ?>" class="btn btn-default"><i class="fa fa-arrow-left" aria-hidden="true"></i> Kembali ke Invoice</a>
        </div>
    </div>
    <div class="box-body">
        <div class="invoice-table-scroll" role="region" aria-label="Daftar penawaran non konsultasi, geser untuk melihat seluruh kolom" tabindex="0">
            <table id="table_list_penawaran_non_kons" class="table table-striped invoice-list-table">
                <thead>
                    <tr>
                        <th scope="col" width="4%" class="text-center">No.</th>
                        <th scope="col" width="12%">Company</th>
                        <th scope="col" width="22%">No. Penawaran</th>
                        <th scope="col" width="16%" class="text-right">Penjualan</th>
                        <th scope="col" width="16%">PIC</th>
                        <th scope="col" width="10%" class="text-center">Invoice Created</th>
                        <th scope="col" width="10%" class="text-center">Status</th>
                        <th scope="col" width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 0;
                    if (!empty($list_penawaran_non_kons)) {
                        foreach ($list_penawaran_non_kons as $item) {
                            $no++;

                            // Count of invoice created (from pre-aggregated controller field or fallback)
                            $jum_invoice = isset($item->total_invoice) ? (int) $item->total_invoice : 0;
                            if (!isset($item->total_invoice) && isset($this->db)) {
                                $jum_invoice = (int) $this->db->get_where('tr_invoicing', ['id_penawaran' => $item->id_penawaran])->num_rows();
                            }

                            $has_invoice = ($jum_invoice > 0);

                            if ($has_invoice) {
                                $status_badge = '<span class="badge bg-green">Invoice Created</span>';
                            } else {
                                $status_badge = '<span class="badge bg-yellow">Draft</span>';
                            }

                            $buttons = [];
                            if ($ENABLE_ADD) {
                                $buttons[] = '<a href="' . base_url('invoicing/create_invoice_non_konsultasi/' . html_escape($item->id_penawaran)) . '" class="btn btn-sm btn-primary" title="Buat Invoice"><i class="fa fa-plus" aria-hidden="true"></i> Buat Invoice</a>';
                            }
                            if ($ENABLE_MANAGE && $has_invoice) {
                                $buttons[] = '<button type="button" class="btn btn-sm btn-danger close_penawaran" data-id_penawaran="' . html_escape($item->id_penawaran) . '" title="Close Penawaran"><i class="fa fa-times-circle" aria-hidden="true"></i> Tutup Penawaran</button>';
                            }

                            $action_html = !empty($buttons) ? implode(' ', $buttons) : '-';
                            ?>
                            <tr>
                                <td class="text-center"><?= $no ?></td>
                                <td class="text-left"><span class="invoice-cell-primary"><?= html_escape($item->nm_company ?: '-') ?></span></td>
                                <td class="text-left"><span class="invoice-cell-primary"><?= html_escape($item->id_penawaran ?: '-') ?></span></td>
                                <td class="text-right" data-total><?= number_format((float) ($item->grand_total ?? 0)) ?></td>
                                <td class="text-left"><?= html_escape($item->pic ?: '-') ?></td>
                                <td class="text-center font-weight-bold" data-total><?= $jum_invoice ?></td>
                                <td class="text-center"><?= $status_badge ?></td>
                                <td class="text-center"><?= $action_html ?></td>
                            </tr>
                        <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('assets/js/invoicing/action-menu.js?v=' . (file_exists(FCPATH . 'assets/js/invoicing/action-menu.js') ? filemtime(FCPATH . 'assets/js/invoicing/action-menu.js') : '1')) ?>"></script>
<script>
    $(document).ready(function() {
        function enhanceActionCells() {
            $('#table_list_penawaran_non_kons tbody td:last-child').each(function() {
                $(this).find('a[title],button[title]').each(function() {
                    var title = $(this).attr('title');
                    $(this).attr('aria-label', title).find('i').attr('aria-hidden', 'true');
                });
                if (window.InvoiceActionMenu) {
                    window.InvoiceActionMenu.enhance(this);
                }
            });
        }

        var table = $('#table_list_penawaran_non_kons').DataTable({
            language: {
                search: 'Cari:',
                searchPlaceholder: 'Nomor, company, atau PIC',
                lengthMenu: 'Tampilkan _MENU_ baris',
                info: '_START_–_END_ dari _TOTAL_ baris',
                infoEmpty: 'Belum ada baris ditampilkan',
                infoFiltered: '(disaring dari _MAX_ baris)',
                emptyTable: 'Belum ada penawaran non konsultasi yang tersedia.',
                zeroRecords: 'Tidak ada penawaran yang sesuai dengan pencarian.',
                paginate: {
                    first: 'Pertama',
                    previous: 'Sebelumnya',
                    next: 'Berikutnya',
                    last: 'Terakhir'
                }
            },
            order: [],
            autoWidth: false,
            paging: true,
            pageLength: 10,
            drawCallback: function() {
                enhanceActionCells();
            }
        });

        $(document).on('preDraw.dt', '#table_list_penawaran_non_kons', function() {
            if (window.InvoiceActionMenu) window.InvoiceActionMenu.close();
        });

        enhanceActionCells();
    });

    $(document).on('click', '.close_penawaran', function() {
        var id_penawaran = $(this).data('id_penawaran');

        Swal.fire({
            title: 'Konfirmasi Penutupan',
            text: 'Penawaran ini akan ditutup secara permanen dan tidak dapat membuat invoice kembali. Lanjutkan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2876a3',
            cancelButtonColor: '#9c2d2d',
            confirmButtonText: 'Ya, Tutup Penawaran!',
            cancelButtonText: 'Batal',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sedang memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'close_penawaran_non_kons',
                    data: {
                        'id_penawaran': id_penawaran
                    },
                    cache: false,
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.msg,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        var errorMsg = 'Terjadi kesalahan sistem.';
                        try {
                            var response = JSON.parse(xhr.responseText);
                            errorMsg = response.msg || errorMsg;
                        } catch (e) {
                            errorMsg = 'Gagal memproses permintaan (Error: ' + xhr.status + ')';
                        }

                        Swal.fire({
                            title: 'Oops!',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonText: 'Tutup'
                        });
                    }
                });
            }
        });
    });
</script>
