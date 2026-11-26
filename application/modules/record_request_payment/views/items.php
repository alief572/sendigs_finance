<div class="rec-toolbar">
    <div class="text-muted">Satu baris per dokumen yang sudah disetujui.</div>
    <form action="<?= site_url('record_request_payment/export_items_excel'); ?>" method="get" class="rec-items-export">
        <input type="text" id="rec_items_search" name="search" placeholder="Cari dokumen, pemohon, pengajuan..." aria-label="Cari transaksi approved">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Cetak ke Excel</button>
    </form>
</div>
<div style="overflow-x:auto;border:1px solid #ddd;">
    <table id="table_record_items" class="table table-bordered table-striped" style="width:100%;">
        <thead><tr>
            <th>NO. DOKUMEN / KATEGORI</th><th>NO. PENGAJUAN</th><th>TRANSFER</th><th>COMPANY</th><th>REQUEST BY</th><th>TANGGAL PENGAJUAN</th><th>TANGGAL APPROVAL</th><th>APPROVER</th><th>KEPERLUAN</th><th>DPP (RP)</th><th>PPN</th><th>PPH 23</th><th>PPH 21</th><th>DIBAYARKAN</th><th>ACTION</th>
        </tr></thead><tbody></tbody>
    </table>
</div>
<div style="margin-top:8px">Tampilkan <select id="rec_items_entries" class="form-control input-sm" style="display:inline-block;width:auto;">
        <option value="10">10</option><option value="25" selected>25</option><option value="50">50</option>
    </select> entri per halaman</div>
<script>
	$(function() {
		var kind = 'items';
		var panel = $('#tab_' + kind);
		var table = null;
		function escapeText(value) { return $('<div>').text(value == null ? '' : value).html(); }
		function amount(value) { return Number(value || 0).toLocaleString('id-ID'); }
		function moneyColumn(key) {
			return {data:key, className:'text-right', render:function(value, type) { return type === 'display' ? amount(value) : value; }};
		}
		function loadTable() {
			if (table) { table.columns.adjust(); table.ajax.reload(null, false); return; }
			var columns = [
				{data:'no_dokumen', render:function(value, type, row) {
					if (type !== 'display') return value;
					var files = Number(row.transfer_file_count) || 0;
					return '<span class="rec-kategori-badge">' + escapeText(row.kategori) + '</span><br><span class="rec-docnum">' + escapeText(value) + '</span>' +
						(files ? '<br><small class="text-muted"><i class="fa fa-paperclip"></i> ' + files + ' lampiran tambahan</small>' : '');
				}},
				{data:'no_pengajuan', defaultContent:'', render:$.fn.dataTable.render.text()},
				{data:'transfer_number', defaultContent:'', className:'text-center', render:function(value, type) {
					if (type !== 'display') return value;
					return value ? '<span class="rec-trf-tag">Transfer ' + Number(value) + '</span>' : '-';
				}},
				{data:'company_nama', defaultContent:'', render:$.fn.dataTable.render.text()},
				{data:'request_by', defaultContent:'', render:$.fn.dataTable.render.text()},
				{data:'submitted_at', defaultContent:'', render:$.fn.dataTable.render.text()},
				{data:'decided_at', defaultContent:'', render:$.fn.dataTable.render.text()},
				{data:'approver', defaultContent:'', render:$.fn.dataTable.render.text()},
				{data:'keperluan', defaultContent:'', className:'rec-keperluan', render:function(value, type, row) {
					return type === 'display' ? escapeText(row.keterangan_finance || value) +
						(row.keterangan_finance ? '<div class="text-muted">Keterangan asli: ' + escapeText(value) + '</div>' : '') : row.keterangan_finance || value;
				}},
				moneyColumn('dpp'), moneyColumn('nilai_ppn'),
				{data:'nilai_pph', className:'text-right', render:function(value, type, row) { var n = row.flag_pph23 ? value : 0; return type === 'display' ? amount(n) : n; }},
				{data:'nilai_pph', className:'text-right', render:function(value, type, row) { var n = row.flag_pph21 ? value : 0; return type === 'display' ? amount(n) : n; }},
				moneyColumn('dibayarkan'),
				{data:'id_pengajuan', orderable:false, className:'text-center', render:function(id, type, row) {
					if (type !== 'display') return id;

					var html = '<a class="mini-btn view" href="<?= site_url('record_request_payment/detail'); ?>/' + Number(id) + '?from=items">View</a>';
					if ((row.has_transfer_file && row.transfer_detail_id) || row.print_url) {
						var attribute = function(value) { return escapeText(value).replace(/"/g, '&quot;'); };
						html += ' <button type="button" class="mini-btn print" data-pengajuan="' + Number(id) + '" data-pdoc="' + attribute(row.no_dokumen) + '">' +
							'<i class="fa ' + (row.has_transfer_file ? 'fa-paperclip' : 'fa-print') + '"></i> Cetak Dokumen</button>';
					}
					return '<span class="rec-item-actions">' + html + '</span>';

				}}
			];
			table = $('#table_record_' + kind).DataTable({
				processing:true, serverSide:true, searching:false, ordering:false, pageLength:25, lengthChange:false, dom:'rtip',
				ajax:{url:<?= json_encode(site_url('record_request_payment/get_data_record_items')); ?>, type:'POST',
					data:function(d) { d.search = {value:$('#rec_' + kind + '_search').val() || ''}; },
					error:function() { alert('Gagal memuat data. Silakan coba lagi.'); }},
				columns:columns,
				language:{processing:'<i class="fa fa-spinner fa-spin"></i> Memproses...',
					emptyTable:<?= json_encode('Belum ada dokumen yang disetujui.'); ?>,
					zeroRecords:'Tidak ada dokumen yang cocok.', info:'Menampilkan _START_ - _END_ dari _TOTAL_ entri', infoEmpty:'Menampilkan 0 - 0 dari 0 entri',
					paginate:{first:'Pertama', last:'Terakhir', next:'Selanjutnya', previous:'Sebelumnya'}}
			});
		}
		$('#record_tabs a[href="#tab_items"]').on('shown.bs.tab', loadTable);
		$('#rec_' + kind + '_search').on('keyup', function() { if (table) table.draw(); });
		$('#rec_' + kind + '_entries').on('change', function() { if (table) table.page.len(Number($(this).val())).draw(); });
		if (panel.hasClass('active')) loadTable();
	});
</script>
