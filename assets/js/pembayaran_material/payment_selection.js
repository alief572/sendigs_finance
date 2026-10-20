(function ($) {
    'use strict';

    var pending = false;
    var loading = true;
    var tableElement = $('#table_list_req_payment');
    var jenisPayment = tableElement.attr('data-jenis-payment');
    var table;

    function message(text) {
        swal({title: 'Perhatian', text: text, type: 'warning'});
    }

    function updateControls() {
        $('.clear_choosed_payment, .proses_payment').prop('disabled', pending || loading);
        $('.check_payment').each(function () {
            var checkbox = $(this);
            checkbox.prop('disabled', pending || loading || checkbox.attr('data-selection-disabled') === '1');
        });
    }

    function reloadSelection() {
        loading = true;
        updateControls();
        table.ajax.reload(null, false);
    }

    tableElement.on('preXhr.dt', function () {
        loading = true;
        updateControls();
    }).on('draw.dt', function () {
        loading = false;
        updateControls();
    }).on('xhr.dt', function (event, settings, json) {
        if (json === null) {
            loading = false;
            updateControls();
            message('Daftar pembayaran gagal dimuat. Silakan coba lagi.');
            return true;
        }
    });

    table = tableElement.DataTable({
        serverSide: true,
        processing: true,
        paging: true,
        stateSave: true,
        ajax: {
            type: 'POST',
            url: siteurl + active_controller + 'get_list_req_payment',
            dataType: 'json',
            data: function (data) { data.jenis_payment = jenisPayment; },
            dataSrc: function (data) {
                if (data.selection_error) { message(data.selection_error); }
                return data.data;
            }
        },
        columns: [
            {data: 'no'}, {data: 'no_dokumen'}, {data: 'tgl'},
            {data: 'company'}, {data: 'keperluan'}, {data: 'currency'},
            {data: 'total_invoice'}, {data: 'requestor'}, {data: 'option'}
        ]
    });

    $(document).on('click', '.check_payment', function () {
        if (pending || loading) { return; }
        var checkbox = $(this);
        var checked = checkbox.prop('checked');
        pending = true;
        updateControls();
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'check_payment',
            data: {id: checkbox.val(), checked: checked ? 1 : 0},
            cache: false,
            dataType: 'json'
        }).done(function (data) {
            if (!data || data.status !== 1) {
                checkbox.prop('checked', !checked);
                message(data && data.pesan ? data.pesan : 'Pilihan pembayaran gagal disimpan.');
            }
        }).fail(function () {
            checkbox.prop('checked', !checked);
            message('Pilihan pembayaran gagal disimpan. Silakan coba lagi.');
        }).always(function () {
            pending = false;
            reloadSelection();
        });
    });

    $(document).on('click', '.clear_choosed_payment', function () {
        if (pending || loading) { return; }
        pending = true;
        updateControls();
        swal({
            title: 'Kosongkan pilihan?',
            text: 'Seluruh pilihan pembayaran Anda akan dikosongkan.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, kosongkan',
            cancelButtonText: 'Batal',
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (confirmed) {
            if (!confirmed) {
                pending = false;
                updateControls();
                return;
            }
            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + 'clear_choosed_payment',
                cache: false,
                dataType: 'json'
            }).done(function (data) {
                if (!data || data.status !== 1) { message(data && data.pesan ? data.pesan : 'Pilihan pembayaran gagal dikosongkan.'); }
            }).fail(function () {
                message('Pilihan pembayaran gagal dikosongkan. Silakan coba lagi.');
            }).always(function () {
                pending = false;
                reloadSelection();
            });
        });
    });

    $(document).on('click', '.proses_payment', function () {
        if (pending || loading) { return; }
        pending = true;
        updateControls();
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'proses_payment',
            cache: false,
            dataType: 'json'
        }).done(function (data) {
            if (!data || data.status !== 1) {
                message(data && data.pesan ? data.pesan : 'Pilihan pembayaran gagal diperiksa.');
                return;
            }
            if (data.count_choosed_payment < 1) {
                message('Pilih minimal satu transaksi pembayaran.');
                return;
            }
            var bearer = data.has_admin_fee ? 'recipient' : 'company';
            window.location.href = siteurl + active_controller + 'form_payment_new/?id_payment=' + encodeURIComponent(data.arr_choosed_payment)
                + '&admin_charge_bearer=' + bearer + '&jenis_payment=' + encodeURIComponent(jenisPayment);
        }).fail(function () {
            message('Proses gagal. Silakan coba lagi.');
        }).always(function () {
            pending = false;
            updateControls();
        });
    });
    updateControls();
})(jQuery);
