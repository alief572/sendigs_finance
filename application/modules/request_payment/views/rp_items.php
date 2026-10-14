<?php
$is_approval = $rp_items_kind === 'approval';
$list_title = $is_approval ? 'Menunggu Approval' : 'Record Request Payment';
$list_module = $is_approval ? 'approval_request_payment' : 'record_request_payment';
$list_endpoint = $is_approval ? 'get_data_pending_items' : 'get_data_record_items';
?>
<div class="box">
	<div class="box-header with-border">
		<ol class="breadcrumb" style="background:none;padding:0;margin-bottom:5px;font-size:12px;">
			<li><a href="javascript:void(0);">Finance</a></li>
			<li class="active"><?= $is_approval ? 'Approval Request Payment' : 'Record Request Payment'; ?></li>
		</ol>
		<h3 class="box-title" style="font-size:22px;font-weight:600;"><?= $list_title; ?></h3>
	</div>
	<div class="box-body">
		<div class="rp-toolbar">
			<div class="entries-control">
				Tampilkan
				<select id="rp_<?= $rp_items_kind; ?>_entries" class="rp-entries">
					<option value="10">10</option><option value="25" selected>25</option><option value="50">50</option>
				</select>
				entri per halaman
			</div>
			<div><input type="text" id="rp_<?= $rp_items_kind; ?>_search" class="rp-search" placeholder="Cari dokumen, pemohon, pengajuan..."></div>
		</div>
		<div class="rp-table-wrapper">
			<table id="table_rp_<?= $rp_items_kind; ?>" class="table table-bordered table-striped" style="width:100%;">
				<thead><tr>
					<th>NO. DOKUMEN / KATEGORI</th><th>NO. PENGAJUAN</th><th>COMPANY</th><th>REQUEST BY</th>
					<th>TANGGAL PENGAJUAN</th>
					<?php if (!$is_approval): ?><th>TANGGAL APPROVAL</th><th>APPROVER</th><?php endif; ?>
					<th>KEPERLUAN</th><th>DPP (RP)</th><th>PPN</th><th>PPH 23</th><th>PPH 21</th><th>ADMIN</th><th>DIBAYARKAN</th><th>ACTION</th>
				</tr></thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>
<style>
	#table_rp_<?= $rp_items_kind; ?> { margin-bottom:0 !important; }
	#table_rp_<?= $rp_items_kind; ?> thead th {
		position:sticky; top:0; z-index:10; background-color:#3c8dbc !important; color:#fff; font-size:12px;
		text-align:center; vertical-align:middle; white-space:nowrap;
		box-shadow:inset 0 -2px 0 #2a6b92, inset 0 1px 0 #3c8dbc;
	}
	#table_rp_<?= $rp_items_kind; ?> tbody td { font-size:12.5px; vertical-align:middle; }
	#table_rp_<?= $rp_items_kind; ?> .rp-item-actions {
		display:inline-flex; align-items:center; white-space:nowrap;
	}
	#table_rp_<?= $rp_items_kind; ?> .mini-btn {
		border:1px solid #ccc; background:#fff; padding:4px 10px; border-radius:4px;
		font-size:11.5px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; margin:2px;
	}
	#table_rp_<?= $rp_items_kind; ?> .mini-btn.view { color:#3D6690; border-color:#BFD6EA; }
	#table_rp_<?= $rp_items_kind; ?> .mini-btn.print { color:#3A4656; border-color:#C9D3DC; }
</style>
<script>
	$(function() {
		var kind = <?= json_encode($rp_items_kind); ?>;
		var panel = $('#rp_tab_' + kind);
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
					return '<span class="rp-kategori-badge">' + escapeText(row.kategori) + '</span><br><span class="rp-docnum">' + escapeText(value) + '</span>';
				}},
				{data:'no_pengajuan', render:$.fn.dataTable.render.text()},
				{data:'company_nama', defaultContent:'', render:$.fn.dataTable.render.text()},
				{data:'request_by', defaultContent:'', render:$.fn.dataTable.render.text()},
				{data:'submitted_at', render:$.fn.dataTable.render.text()}
			];
			<?php if (!$is_approval): ?>
			columns.push({data:'decided_at', render:$.fn.dataTable.render.text()}, {data:'approver', defaultContent:'', render:$.fn.dataTable.render.text()});
			<?php endif; ?>
			columns.push(
				{data:'keperluan', defaultContent:'', render:$.fn.dataTable.render.text()}, moneyColumn('dpp'), moneyColumn('nilai_ppn'),
				{data:'nilai_pph', className:'text-right', render:function(value, type, row) { var n = row.flag_pph23 ? value : 0; return type === 'display' ? amount(n) : n; }},
				{data:'nilai_pph', className:'text-right', render:function(value, type, row) { var n = row.flag_pph21 ? value : 0; return type === 'display' ? amount(n) : n; }},
				moneyColumn('admin'), moneyColumn('dibayarkan'),
				{data:'id_pengajuan', orderable:false, className:'text-center', render:function(id, type, row) {
					if (type !== 'display') return id;
					<?php if ($is_approval): ?>
					if (!row.print_url || !/^https?:\/\//i.test(row.print_url)) return '-';
					return '<span class="rp-item-actions"><a class="mini-btn print" target="_blank" rel="noopener" href="' + escapeText(row.print_url).replace(/"/g, '&quot;').replace(/'/g, '&#39;') + '"><i class="fa fa-print"></i> Print</a></span>';
					<?php else: ?>
					var html = '<a class="mini-btn view" href="<?= site_url($list_module . '/detail'); ?>/' + Number(id) + '?from=request_payment">View</a>';
					if (row.print_url && /^https?:\/\//i.test(row.print_url)) {
						html += ' <a class="mini-btn print" target="_blank" rel="noopener" href="' + escapeText(row.print_url).replace(/"/g, '&quot;').replace(/'/g, '&#39;') + '"><i class="fa fa-print"></i> Cetak Dokumen</a>';
					}
					html += ' <a class="mini-btn print" href="<?= site_url('record_request_payment/export_excel'); ?>/' + Number(id) + '"><i class="fa fa-file-excel-o"></i> Excel Pengajuan</a>';
					return '<span class="rp-item-actions">' + html + '</span>';
					<?php endif; ?>
				}}
			);
			table = $('#table_rp_' + kind).DataTable({
				processing:true, serverSide:true, searching:false, ordering:false, pageLength:25, lengthChange:false, dom:'rtip',
				ajax:{url:<?= json_encode(site_url($list_module . '/' . $list_endpoint)); ?>, type:'POST',
					data:function(d) { d.search = {value:$('#rp_' + kind + '_search').val() || ''}; },
					error:function() { alert('Gagal memuat data. Silakan coba lagi.'); }},
				columns:columns,
				language:{processing:'<i class="fa fa-spinner fa-spin"></i> Memproses...',
					emptyTable:<?= json_encode($is_approval ? 'Belum ada dokumen yang menunggu approval.' : 'Belum ada dokumen yang disetujui.'); ?>,
					zeroRecords:'Tidak ada dokumen yang cocok.', info:'Menampilkan _START_ - _END_ dari _TOTAL_ entri', infoEmpty:'Menampilkan 0 - 0 dari 0 entri',
					paginate:{first:'Pertama', last:'Terakhir', next:'Selanjutnya', previous:'Sebelumnya'}}
			});
		}
		$('#rp_module_tabs a[data-rp-tab="' + kind + '"]').on('shown.bs.tab', loadTable);
		$('#rp_' + kind + '_search').on('keyup', function() { if (table) table.draw(); });
		$('#rp_' + kind + '_entries').on('change', function() { if (table) table.page.len(Number($(this).val())).draw(); });
		if (panel.hasClass('active')) loadTable();
	});
</script>
