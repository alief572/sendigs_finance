<div class="row" style="margin-bottom: 15px;">
    <div class="col-md-5">
        <label>Filter Kategori</label>
        <select id="filter_history_category" class="form-control select2" style="width:100%;">
            <option value="">-- Semua Kategori --</option>
            <?php if(!empty($categories)): foreach($categories as $c): ?>
                <option value="<?= $c->id ?>"><?= strtoupper($c->nm_category) ?></option>
            <?php endforeach; endif; ?>
        </select>
    </div>
    <div class="col-md-5">
        <label>Filter Barang Stok</label>
        <select id="filter_history_barang" class="form-control select2" style="width:100%;">
            <option value="">-- Semua Barang Stok --</option>
            <?php if(!empty($items)): foreach($items as $it): ?>
                <option value="<?= $it->id ?>"><?= strtoupper($it->id_stock ? $it->id_stock . ' - ' : '') . strtoupper($it->stock_name) ?></option>
            <?php endforeach; endif; ?>
        </select>
    </div>
    <div class="col-md-2">
        <label>&nbsp;</label><br>
        <button type="button" class="btn btn-primary btn-block" id="btn-load-history"><i class="fa fa-search"></i> Filter</button>
    </div>
</div>

<div class="table-responsive">
    <table id="table-history-data" class="table table-bordered table-striped table-hover" width="100%">
        <thead>
            <tr class="bg-purple">
                <th class="text-center" width="4%">#</th>
                <th class="text-center" width="10%">Tanggal</th>
                <th class="text-center" width="12%">No. Dokumen</th>
                <th class="text-center" width="18%">Nama Barang</th>
                <th class="text-center" width="10%">Kategori</th>
                <th class="text-center" width="11%">Harga Before (IDR)</th>
                <th class="text-center" width="13%">Harga New (IDR)</th>
                <th class="text-center" width="8%">Status</th>
                <th class="text-center" width="14%">Audit Info</th>
            </tr>
        </thead>
        <tbody id="tbody-history">
            <tr>
                <td colspan="9" class="text-center text-muted">Memuat data riwayat...</td>
            </tr>
        </tbody>
    </table>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });
        fetch_history();
    });

    $('#btn-load-history').on('click', function() {
        fetch_history();
    });

    $('#filter_history_category, #filter_history_barang').on('change', function() {
        fetch_history();
    });

    function fetch_history() {
        var id_category = $('#filter_history_category').val();
        var id_barang = $('#filter_history_barang').val();

        $('#tbody-history').html('<tr><td colspan="9" class="text-center"><i class="fa fa-spinner fa-spin"></i> Memuat data riwayat...</td></tr>');

        $.ajax({
            url: siteurl + 'price_sup_barang_stok/get_history_data',
            type: 'POST',
            dataType: 'json',
            data: {
                id_category: id_category,
                id_barang: id_barang
            },
            success: function(data) {
                if (!data || data.length === 0) {
                    $('#tbody-history').html('<tr><td colspan="9" class="text-center text-muted">Tidak ada data riwayat harga yang ditemukan.</td></tr>');
                    return;
                }

                var html = '';
                var no = 0;
                for (var i = 0; i < data.length; i++) {
                    no++;
                    var item = data[i];

                    var status_badge = '<span class="badge bg-yellow">Waiting</span>';
                    if (item.status === '1') {
                        status_badge = '<span class="badge bg-green">Approved</span>';
                    } else if (item.status === '2') {
                        status_badge = '<span class="badge bg-red">Rejected</span>';
                    }

                    var before_str = 'Low: ' + format_num(item.price_ref_before) + '<br>High: ' + format_num(item.price_ref_high_before);
                    var after_str = '<b class="text-primary">Low: ' + format_num(item.price_ref_new) + '</b><br><b class="text-primary">High: ' + format_num(item.price_ref_high_new) + '</b>';

                    var audit = '<small><b>Pengaju:</b> ' + (item.creator_name ? item.creator_name : '-') + '<br>';
                    if (item.status === '1') {
                        audit += '<b>Approve:</b> ' + (item.approver_name ? item.approver_name : '-') + '<br>(' + (item.approved_date ? item.approved_date : '') + ')';
                    } else if (item.status === '2') {
                        audit += '<b class="text-danger">Alasan:</b> ' + (item.rejected_reason ? item.rejected_reason : '-');
                    }
                    audit += '</small>';

                    html += '<tr>' +
                        '<td class="text-center">' + no + '</td>' +
                        '<td class="text-center">' + (item.tanggal_doc ? item.tanggal_doc : '-') + '</td>' +
                        '<td class="text-center"><b>' + item.no_doc + '</b></td>' +
                        '<td><b>' + (item.stock_name ? item.stock_name : '-') + '</b><br><small class="text-muted">' + (item.spec ? item.spec : '') + '</small></td>' +
                        '<td class="text-center">' + (item.nm_category ? item.nm_category : '-') + '</td>' +
                        '<td class="text-right">' + before_str + '</td>' +
                        '<td class="text-right">' + after_str + '</td>' +
                        '<td class="text-center">' + status_badge + '</td>' +
                        '<td>' + audit + '</td>' +
                        '</tr>';
                }
                $('#tbody-history').html(html);
            },
            error: function() {
                $('#tbody-history').html('<tr><td colspan="9" class="text-center text-danger">Gagal memuat data riwayat.</td></tr>');
            }
        });
    }

    function format_num(num) {
        if (!num) return '0';
        return parseFloat(num).toLocaleString('id-ID');
    }
</script>
