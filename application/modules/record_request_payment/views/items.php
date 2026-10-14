<div class="rec-toolbar">
    <div>Tampilkan <select id="rec_items_entries" class="form-control input-sm" style="display:inline-block;width:auto;">
        <option value="10">10</option><option value="25" selected>25</option><option value="50">50</option>
    </select> entri per halaman</div>
    <input type="text" id="rec_items_search" placeholder="Cari dokumen, pemohon, pengajuan...">
</div>
<div style="overflow-x:auto;border:1px solid #ddd;">
    <table id="table_record_items" class="table table-bordered table-striped" style="width:100%;">
        <thead><tr>
            <th>NO. DOKUMEN / KATEGORI</th><th>NO. PENGAJUAN</th><th>COMPANY</th><th>REQUEST BY</th>
            <th>TANGGAL PENGAJUAN</th><th>TANGGAL APPROVAL</th><th>APPROVER</th><th>KEPERLUAN</th>
            <th>DPP (RP)</th><th>PPN</th><th>PPH 23</th><th>PPH 21</th><th>ADMIN</th><th>DIBAYARKAN</th><th>ACTION</th>
        </tr></thead><tbody></tbody>
    </table>
</div>
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
					return '<span class="rec-kategori-badge">' + escapeText(row.kategori) + '</span><br><span class="rec-docnum">' + escapeText(value) + '</span>';
				}},
				{data:'no_pengajuan', render:$.fn.dataTable.render.text()},
				{data:'company_nama', defaultContent:'', render:$.fn.dataTable.render.text()},
				{data:'request_by', defaultContent:'', render:$.fn.dataTable.render.text()},
				{data:'submitted_at', render:$.fn.dataTable.render.text()}
			];

			columns.push({data:'decided_at', render:$.fn.dataTable.render.text()}, {data:'approver', defaultContent:'', render:$.fn.dataTable.render.text()});

			columns.push(
				{data:'keperluan', defaultContent:'', render:$.fn.dataTable.render.text()}, moneyColumn('dpp'), moneyColumn('nilai_ppn'),
				{data:'nilai_pph', className:'text-right', render:function(value, type, row) { var n = row.flag_pph23 ? value : 0; return type === 'display' ? amount(n) : n; }},
				{data:'nilai_pph', className:'text-right', render:function(value, type, row) { var n = row.flag_pph21 ? value : 0; return type === 'display' ? amount(n) : n; }},
				moneyColumn('admin'), moneyColumn('dibayarkan'),
				{data:'id_pengajuan', orderable:false, className:'text-center', render:function(id, type, row) {
					if (type !== 'display') return id;

					var html = '<a class="mini-btn view" href="<?= site_url('record_request_payment/detail'); ?>/' + Number(id) + '?from=items">View</a>';
					if (row.print_url && /^https?:\/\//i.test(row.print_url)) {
						html += ' <a class="mini-btn print" target="_blank" rel="noopener" href="' + escapeText(row.print_url).replace(/"/g, '&quot;').replace(/'/g, '&#39;') + '"><i class="fa fa-print"></i> Cetak Dokumen</a>';
					}
					html += ' <a class="mini-btn print" href="<?= site_url('record_request_payment/export_excel'); ?>/' + Number(id) + '"><i class="fa fa-file-excel-o"></i> Excel Pengajuan</a>';
					return '<span class="rec-item-actions">' + html + '</span>';

				}}
			);
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
