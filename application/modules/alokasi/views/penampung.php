<link rel="stylesheet" href="<?= base_url('assets/datatables/css/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/alokasi-penampung.css') ?>">
<script src="<?= base_url('assets/datatables/js/jquery.dataTables.js') ?>"></script>
<script src="<?= base_url('assets/datatables/js/dataTables.bootstrap.js') ?>"></script>
<div class="allocation-workspace">
    <section class="allocation-filters" aria-labelledby="allocation-filter-title">
        <div class="allocation-filter-heading">
            <h2 id="allocation-filter-title">Filter alokasi</h2>
            <p>Telusuri alokasi berdasarkan tanggal transaksi dan rekening bank.</p>
        </div>
        <form id="allocation-filters" class="allocation-filter-grid">
            <div class="allocation-field">
                <label for="allocation-start">Dari tanggal</label>
                <input type="date" id="allocation-start" class="form-control">
            </div>
            <div class="allocation-field">
                <label for="allocation-end">Sampai tanggal</label>
                <input type="date" id="allocation-end" class="form-control">
            </div>
            <div class="allocation-field allocation-bank-field">
                <label for="allocation-bank">Rekening bank</label>
                <select id="allocation-bank" class="form-control">
                    <option value="">Semua rekening bank</option>
                    <?php foreach ($data_bank as $bank) : ?>
                        <option value="<?= html_escape($bank['id']) ?>"><?= html_escape($bank['nama_bank'] . ' - ' . $bank['rekening'] . ' - ' . $bank['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="allocation-filter-actions">
                <button type="submit" class="btn allocation-apply">Terapkan filter</button>
                <button type="reset" class="btn allocation-reset">Reset</button>
            </div>
        </form>
        <p id="allocation-error" class="text-danger" role="alert" hidden></p>
    </section>
    <section id="allocation-data" class="allocation-data" aria-labelledby="allocation-table-title" aria-busy="true">
        <div class="allocation-table-heading">
            <div>
                <h2 id="allocation-table-title">Daftar alokasi</h2>
                <p>Nominal sesuai bagian transaksi yang dialokasikan.</p>
            </div>
            <p id="allocation-count" class="allocation-count" role="status" aria-live="polite">Memuat alokasi...</p>
        </div>
            <table class="table allocation-table" id="allocation-list" width="100%">
                <caption class="sr-only">Daftar alokasi berdasarkan tanggal transaksi, referensi, bank, keterangan, dan nominal.</caption>
                <thead><tr>
                    <th scope="col">No</th><th scope="col">Tanggal transaksi</th><th scope="col">Referensi</th>
                    <th scope="col">Bank / rekening</th><th scope="col">Keterangan</th><th scope="col" class="text-right">Nominal alokasi</th>
                </tr></thead>
                <tbody></tbody>
            </table>
    </section>
</div>
<script>
$(function () {
    var filters = {};
    var error = $('#allocation-error');
    var count = $('#allocation-count');
    var dataArea = $('#allocation-data');
    var latestRequest = 0;
    function textCell(value) { return $('<div>').text(value == null ? '' : value).html(); }
    var table = $('#allocation-list').DataTable({
        serverSide: true,
        processing: true,
        ordering: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        searchDelay: 350,
        dom: '<"allocation-table-tools"lf><"allocation-table-scroll"tr><"allocation-table-footer"ip>',
        language: {
            search: 'Cari alokasi',
            searchPlaceholder: 'Referensi, bank, keterangan, nominal',
            lengthMenu: 'Tampilkan _MENU_ baris',
            info: 'Baris _START_–_END_ dari _TOTAL_ alokasi',
            infoEmpty: 'Tidak ada baris ditampilkan',
            infoFiltered: '(dari _MAX_ alokasi)',
            emptyTable: 'Belum ada data untuk jenis alokasi ini. Tentukan tujuan transaksi melalui menu Alokasi.',
            zeroRecords: 'Tidak ada hasil sesuai filter atau pencarian. Ubah kriteria atau tekan Reset.',
            processing: 'Memuat daftar alokasi...',
            loadingRecords: 'Memuat daftar alokasi...',
            paginate: {previous: 'Sebelumnya', next: 'Berikutnya'}
        },
        initComplete: function () {
            $('#allocation-list_wrapper .allocation-table-scroll').attr('tabindex', '0').attr('role', 'region').attr('aria-label', 'Daftar alokasi. Geser horizontal untuk melihat seluruh kolom.');
            $('#allocation-list_filter input').attr('aria-label', 'Cari alokasi berdasarkan referensi, bank, keterangan, atau nominal');
        },
        ajax: function (data, callback) {
            var requestId = ++latestRequest;
            dataArea.attr('aria-busy', 'true');
            error.text('').prop('hidden', true);
            count.text('Memuat alokasi...');
            $.ajax({
                url: <?= json_encode(site_url($listing_route . '/get_alokasi'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                type: 'POST',
                dataType: 'json',
                data: $.extend({}, data, filters)
            }).done(function (json) {
                if (requestId !== latestRequest) return;
                error.text(json.error || '').prop('hidden', !json.error);
                count.text(json.error ? 'Data belum dimuat' : Number(json.recordsFiltered).toLocaleString('id-ID') + ' alokasi');
                callback(json);
                dataArea.attr('aria-busy', 'false');
            }).fail(function (xhr) {
                if (requestId !== latestRequest) return;
                var json = xhr.responseJSON;
                error.text(json && json.error ? json.error : 'Gagal memuat data alokasi. Silakan coba lagi.').prop('hidden', false);
                callback({draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: []});
                $('#allocation-list .dataTables_empty').text('Data belum dapat ditampilkan. Tekan Terapkan filter untuk mencoba lagi.');
                count.text('Data belum dimuat');
                dataArea.attr('aria-busy', 'false');
            });
        },
        columns: [
            {data: 'no', className: 'allocation-number', render: textCell},
            {data: 'tanggal_transaksi', className: 'allocation-date', render: textCell},
            {data: 'reference_no', className: 'allocation-reference', render: textCell},
            {data: 'bank', className: 'allocation-bank', render: textCell},
            {data: 'keterangan', render: textCell},
            {data: 'nominal', className: 'allocation-amount text-right', render: textCell}
        ]
    });
    $('#allocation-filters').on('submit', function (event) {
        event.preventDefault();
        filters = {startDate: $('#allocation-start').val(), endDate: $('#allocation-end').val(), bank: $('#allocation-bank').val()};
        table.ajax.reload();
    }).on('reset', function () {
        filters = {};
        table.search('').ajax.reload();
    });
});
</script>
