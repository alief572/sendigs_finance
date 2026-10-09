(function ($) {
    'use strict';

    var table, saving = false, deleting = false, searchTimer;
    var page, form, modal;

    function notice(target, message, error) {
        var el = $(target);
        if (!message) {
            el.text('').prop('hidden', true);
            return;
        }
        el.html(message).toggleClass('category-notice-error', !!error).prop('hidden', false);
    }

    function endpoint(action) {
        return page.attr('data-' + action + '-url');
    }

    function confirmAction(title, text, confirmBtn, callback) {
        swal({
            title: title,
            text: text,
            type: 'warning',
            showCancelButton: true,
            confirmButtonClass: 'btn-info',
            confirmButtonText: confirmBtn || 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            closeOnConfirm: false
        }, callback);
    }

    function resetForm() {
        form[0].reset();
        $('#id').val('');
        notice('#category-form-feedback', '', false);
    }

    function setSaving(busy) {
        saving = busy;
        var btn = $('#category-save');
        btn.prop('disabled', busy).html(busy
            ? '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Menyimpan…'
            : '<i class="fa fa-check" aria-hidden="true"></i> Simpan kategori');
        modal.find('[data-dismiss="modal"]').prop('disabled', busy);
        form.find('input, textarea').prop('disabled', busy);
    }

    function validateForm() {
        var name = $.trim($('#nm_category').val() || '');

        if (!name) {
            notice('#category-form-feedback', 'Masukkan nama kategori stok.', true);
            $('#nm_category').trigger('focus');
            return false;
        }

        notice('#category-form-feedback', '', false);
        return true;
    }

    function openAddModal() {
        if (saving) { return; }
        resetForm();
        $('#head_title').text('Tambah Kategori Stok');
        $('#category-form-description').text('Lengkapi nama kategori dan deskripsi kategori stok.');
        modal.modal('show');
    }

    function openEditModal(btn) {
        if (saving) { return; }
        resetForm();
        var id = btn.data('id');
        var name = btn.data('name');
        var desc = btn.data('description');

        $('#id').val(id);
        $('#nm_category').val(name);
        $('#description').val(desc);

        $('#head_title').text('Edit Kategori Stok');
        $('#category-form-description').text('Perbarui nama kategori atau deskripsi kategori stok.');
        modal.modal('show');
    }

    function deleteCategory(btn) {
        if (deleting || saving) { return; }
        var id = btn.data('id');
        var name = btn.data('name') || '';

        confirmAction('Hapus kategori?', 'Kategori ' + (name ? '"' + name + '"' : '') + ' akan dihapus dari daftar kategori stok.', 'Ya, Hapus!', function (confirmed) {
            if (!confirmed || deleting) { return; }
            deleting = true;

            $.ajax({
                type: 'POST',
                url: endpoint('delete'),
                data: { id: id },
                dataType: 'json'
            }).done(function (res) {
                if (res.status == '1' || res.status === 1) {
                    swal({
                        title: 'Berhasil',
                        text: res.pesan || 'Data kategori berhasil dihapus.',
                        type: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }, function () {
                        window.location.reload(true);
                    });
                } else {
                    swal({
                        title: 'Gagal',
                        text: res.pesan || 'Data kategori gagal dihapus.',
                        type: 'error'
                    });
                }
            }).fail(function () {
                swal({
                    title: 'Terjadi Kesalahan',
                    text: 'Tidak dapat memproses penghapusan. Silakan coba lagi.',
                    type: 'error'
                });
            }).always(function () {
                deleting = false;
            });
        });
    }

    $(function () {
        page = $('#accessories-category-page');
        form = $('#data_form');
        modal = $('#dialog-popup');

        // DataTables init
        if ($.fn.DataTable.isDataTable('#example1')) {
            $('#example1').DataTable().destroy();
        }

        table = $('#example1').DataTable({
            paging: true,
            lengthChange: true,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            pageLength: 10,
            dom: '<"category-table-tools"l>rt<"category-table-footer"ip>',
            language: {
                emptyTable: 'Belum ada data kategori stok.',
                zeroRecords: 'Tidak ada data kategori yang sesuai dengan pencarian.',
                lengthMenu: 'Tampilkan _MENU_ baris',
                info: '_START_–_END_ dari _TOTAL_ kategori',
                infoEmpty: '0 kategori',
                infoFiltered: '(disaring dari _MAX_ total)',
                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya'
                }
            },
            columnDefs: [
                { orderable: false, targets: [0, 4] },
                { searchable: false, targets: [0, 4] }
            ]
        });

        // Search debouncing
        $('#category-search').on('input', function () {
            var val = this.value;
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                table.search(val).draw();
            }, 250);
        });

        // Refresh table
        $('#category-refresh').on('click', function () {
            notice('#category-feedback', '', false);
            $('#category-search').val('');
            table.search('').draw();
            notice('#category-feedback', 'Data kategori stok berhasil dimuat ulang.', false);
            setTimeout(function () {
                notice('#category-feedback', '', false);
            }, 3000);
        });

        // Open Add
        $('#category-add').on('click', function () {
            openAddModal();
        });

        // Open Edit
        $(document).on('click', '.category-btn-edit', function (e) {
            e.preventDefault();
            openEditModal($(this));
        });

        // Delete
        $(document).on('click', '.category-btn-delete', function (e) {
            e.preventDefault();
            deleteCategory($(this));
        });

        // Form Submit
        form.on('submit', function (e) {
            e.preventDefault();
            if (saving || !validateForm()) { return; }

            confirmAction('Simpan kategori?', 'Pastikan nama dan deskripsi kategori sudah benar.', 'Ya, Simpan!', function (confirmed) {
                if (!confirmed || saving) { return; }
                setSaving(true);

                var formData = form.serialize();

                $.ajax({
                    type: 'POST',
                    url: endpoint('save'),
                    data: formData,
                    dataType: 'json'
                }).done(function (res) {
                    if (res.status == '1' || res.status === 1) {
                        modal.modal('hide');
                        swal({
                            title: 'Berhasil',
                            text: res.pesan || 'Data kategori berhasil disimpan.',
                            type: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }, function () {
                            window.location.reload(true);
                        });
                    } else {
                        notice('#category-form-feedback', res.pesan || 'Gagal menyimpan data kategori.', true);
                        swal.close();
                    }
                }).fail(function () {
                    notice('#category-form-feedback', 'Terjadi kesalahan sistem saat menyimpan. Coba lagi.', true);
                    swal.close();
                }).always(function () {
                    setSaving(false);
                });
            });
        });

        modal.on('shown.bs.modal', function () {
            $('#nm_category').trigger('focus');
            if (window.gsap && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                window.gsap.fromTo('.category-modal .category-form-grid', { y: 10, opacity: 0.6 }, { y: 0, opacity: 1, duration: 0.25, clearProps: 'all' });
            }
        });

        // GSAP page entrance animation
        if (window.gsap && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            window.gsap.fromTo('.category-page-header, .category-panel, .category-footnote', 
                { y: 14, opacity: 0.5 }, 
                { y: 0, opacity: 1, duration: 0.45, stagger: 0.08, ease: 'power2.out', clearProps: 'all' }
            );
        }
    });
})(jQuery);
