(function ($) {
    'use strict';
    $(function () {
        var modal = $('#AssetImportModal');
        if (!modal.length) { return; }
        var token = null, busy = false, completed = false;
        var file = $('#asset-import-file'), save = $('#asset-import-save'), preview = $('#asset-import-preview');
        function message(text, ok) {
            $('#asset-import-message').empty().append($('<div>').addClass('alert ' + (ok ? 'alert-success' : 'alert-danger')).text(text));
        }
        function money(value) { return 'Rp' + Number(value || 0).toLocaleString('id-ID'); }
        function errors(items) {
            var area = $('#asset-import-errors').empty();
            if (!items || !items.length) { return; }
            var list = $('<ul>').addClass('alert alert-danger');
            items.forEach(function (item) {
                $('<li>').text((item.baris ? 'Baris ' + item.baris : 'File') + ' — ' + item.kolom + ': ' + item.pesan).appendTo(list);
            });
            area.append(list);
        }
        function controls() {
            preview.prop('disabled', busy); file.prop('disabled', busy);
            save.prop('disabled', busy || !token || completed);
            modal.find('[data-dismiss="modal"]').prop('disabled', busy);
        }
        function render(data) {
            message(data.pesan || 'Respons tidak dapat dibaca.', data.status === 1);
            errors(data.kesalahan);
            $('#asset-import-summary').empty();
            var body = $('#asset-import-rows tbody').empty();
            $('#asset-import-rows').toggle(!!(data.preview && data.preview.length));
            if (data.ringkasan) {
                $('<p>').text(data.ringkasan.baris + ' baris • ' + data.ringkasan.unit + ' unit asset • Total nilai perolehan ' + money(data.ringkasan.total)).appendTo('#asset-import-summary');
            }
            (data.preview || []).forEach(function (row) {
                var tr = $('<tr>').toggleClass('danger', !row.valid);
                [row.line, row.nm_asset, row.category_name, row.company_name, row.department_name || '—', row.date || 'Tidak valid', row.qty, money(row.amount), row.years + ' tahun', money(row.monthly), row.valid ? 'Valid' : 'Perlu diperbaiki'].forEach(function (value) {
                    $('<td>').text(value).appendTo(tr);
                });
                body.append(tr);
            });
        }
        file.on('change', function () {
            token = null; completed = false; controls();
            $('#asset-import-message,#asset-import-summary,#asset-import-errors').empty();
            $('#asset-import-rows').hide().find('tbody').empty();
        });
        modal.on('hide.bs.modal', function (event) { if (busy) { event.preventDefault(); } });
        preview.on('click', function () {
            if (busy) { return; }
            var selected = file[0].files[0];
            token = null; completed = false; controls();
            if (!selected || !/\.xlsx$/i.test(selected.name) || selected.size > 5 * 1024 * 1024) {
                message('Pilih file .xlsx maksimal 5 MB.', false); return;
            }
            var form = new FormData(); form.append('file', selected); form.append('nonce', modal.attr('data-nonce'));
            busy = true; controls(); message('Sedang memeriksa file…', true);
            $.ajax({url: modal.attr('data-preview-url'), type: 'POST', data: form, processData: false, contentType: false, dataType: 'json'})
                .done(function (data) { render(data); token = data.status === 1 && !(data.kesalahan || []).length ? data.token : null; })
                .fail(function () { message('File gagal diperiksa. Periksa koneksi/session dan batas upload server.', false); })
                .always(function () { busy = false; controls(); });
        });
        save.on('click', function () {
            if (busy || !token || completed) { return; }
            busy = true; controls(); message('Sedang menyimpan seluruh asset…', true);
            // Preview rows and monetary values never travel back to the save endpoint.
            $.ajax({url: modal.attr('data-save-url'), type: 'POST', data: {token: token, nonce: modal.attr('data-nonce')}, dataType: 'json'})
                .done(function (data) {
                    message(data.pesan, data.status === 1); errors(data.kesalahan);
                    if (data.status === 1) {
                        completed = true;
                        if ($.fn.dataTable.isDataTable('#example1')) { $('#example1').DataTable().ajax.reload(null, false); }
                    } else if ((data.kesalahan || []).length) { token = null; }
                })
                .fail(function () { message('Konfirmasi belum diterima. Klik Simpan lagi; token yang sama tidak menggandakan asset.', false); })
                .always(function () { busy = false; controls(); });
        });
    });
})(jQuery);
