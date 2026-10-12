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
        el.html(message).toggleClass('unit-notice-error', !!error).prop('hidden', false);
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
        notice('#unit-form-feedback', '', false);
    }

    function setSaving(busy) {
        saving = busy;
        var btn = $('#unit-save');
        btn.prop('disabled', busy).html(busy
            ? '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Menyimpan…'
            : '<i class="fa fa-check" aria-hidden="true"></i> Simpan satuan');
        modal.find('[data-dismiss="modal"]').prop('disabled', busy);
        form.find('input').prop('disabled', busy);
    }

    function validateForm() {
        var code = $.trim($('#code').val() || '');
        var nama = $.trim($('#nama').val() || '');

        if (!code) {
            notice('#unit-form-feedback', 'Masukkan kode satuan (contoh: PCS, KG, LTR).', true);
            $('#code').trigger('focus');
            return false;
        }
        if (!nama) {
            notice('#unit-form-feedback', 'Masukkan nama lengkap satuan (contoh: Pieces, Kilogram).', true);
            $('#nama').trigger('focus');
            return false;
        }

        notice('#unit-form-feedback', '', false);
        return true;
    }

    function openAddModal() {
        if (saving) { return; }
        resetForm();
        $('#head_title').text('Tambah Satuan');
        $('#unit-form-description').text('Lengkapi kode singkatan dan nama satuan pengukuran.');
        modal.modal('show');
    }

    function openEditModal(btn) {
        if (saving) { return; }
        resetForm();
        var id = btn.data('id');
        var code = btn.data('code');
        var name = btn.data('name');

        $('#id').val(id);
        $('#code').val(code);
        $('#nama').val(name);

        $('#head_title').text('Edit Satuan');
        $('#unit-form-description').text('Perbarui kode singkatan atau nama lengkap satuan pengukuran.');
        modal.modal('show');
    }

    function deleteUnit(btn) {
        if (deleting || saving) { return; }
        var id = btn.data('id');
        var code = btn.data('code') || '';
        var name = btn.data('name') || '';
        var label = (code ? code : '') + (name ? ' (' + name + ')' : '');

        confirmAction('Hapus satuan?', 'Satuan ' + label + ' akan dihapus dari daftar satuan aktif.', 'Ya, Hapus!', function (confirmed) {
            if (!confirmed || deleting) { return; }
            deleting = true;

            $.ajax({
                type: 'POST',
                url: endpoint('delete'),
                data: { id: id },
                dataType: 'json'
            }).done(function (res) {
                if (res.status == '1') {
                    swal({
                        title: 'Berhasil',
                        text: res.pesan || 'Data satuan berhasil dihapus.',
                        type: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }, function () {
                        window.location.reload(true);
                    });
                } else {
                    swal({
                        title: 'Gagal',
                        text: res.pesan || 'Data satuan gagal dihapus.',
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
        page = $('#master-unit-page');
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
            dom: '<"unit-table-tools"l>rt<"unit-table-footer"ip>',
            language: {
                emptyTable: 'Belum ada data satuan pengukuran.',
                zeroRecords: 'Tidak ada data satuan yang sesuai dengan pencarian.',
                lengthMenu: 'Tampilkan _MENU_ baris',
                info: '_START_–_END_ dari _TOTAL_ satuan',
                infoEmpty: '0 satuan',
                infoFiltered: '(disaring dari _MAX_ total)',
                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya'
                }
            },
            columnDefs: [
                { orderable: false, targets: [0, 3] },
                { searchable: false, targets: [0, 3] }
            ]
        });

        // Search debouncing
        $('#unit-search').on('input', function () {
            var val = this.value;
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                table.search(val).draw();
            }, 250);
        });

        // Refresh table
        $('#unit-refresh').on('click', function () {
            notice('#unit-feedback', '', false);
            $('#unit-search').val('');
            table.search('').draw();
            notice('#unit-feedback', 'Data satuan berhasil dimuat ulang.', false);
            setTimeout(function () {
                notice('#unit-feedback', '', false);
            }, 3000);
        });

        // Open Add
        $('#unit-add').on('click', function () {
            openAddModal();
        });

        // Open Edit
        $(document).on('click', '.unit-btn-edit', function (e) {
            e.preventDefault();
            openEditModal($(this));
        });

        // Delete
        $(document).on('click', '.unit-btn-delete', function (e) {
            e.preventDefault();
            deleteUnit($(this));
        });

        // Form Submit
        form.on('submit', function (e) {
            e.preventDefault();
            if (saving || !validateForm()) { return; }

            confirmAction('Simpan satuan?', 'Pastikan kode dan nama satuan sudah benar.', 'Ya, Simpan!', function (confirmed) {
                if (!confirmed || saving) { return; }
                setSaving(true);

                var formData = form.serialize();

                $.ajax({
                    type: 'POST',
                    url: endpoint('save'),
                    data: formData,
                    dataType: 'json'
                }).done(function (res) {
                    if (res.status == '1') {
                        modal.modal('hide');
                        swal({
                            title: 'Berhasil',
                            text: res.pesan || 'Data satuan berhasil disimpan.',
                            type: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }, function () {
                            window.location.reload(true);
                        });
                    } else {
                        notice('#unit-form-feedback', res.pesan || 'Gagal menyimpan data satuan.', true);
                        swal.close();
                    }
                }).fail(function () {
                    notice('#unit-form-feedback', 'Terjadi kesalahan sistem saat menyimpan. Coba lagi.', true);
                    swal.close();
                }).always(function () {
                    setSaving(false);
                });
            });
        });

        modal.on('shown.bs.modal', function () {
            $('#code').trigger('focus');
            if (window.gsap && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                window.gsap.fromTo('.unit-modal .unit-form-grid', { y: 10, opacity: 0.6 }, { y: 0, opacity: 1, duration: 0.25, clearProps: 'all' });
            }
        });

        // GSAP page entrance animation
        if (window.gsap && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            window.gsap.fromTo('.unit-page-header, .unit-panel, .unit-footnote', 
                { y: 14, opacity: 0.5 }, 
                { y: 0, opacity: 1, duration: 0.45, stagger: 0.08, ease: 'power2.out', clearProps: 'all' }
            );
        }
    });
})(jQuery);
