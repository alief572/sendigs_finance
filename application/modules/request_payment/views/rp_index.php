<?php
$ENABLE_VIEW   = has_permission('Request_Payment.View');
$ENABLE_MANAGE = has_permission('Request_Payment.Manage');
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.11/css/dataTables.bootstrap.min.css">

<style>
	.rp-toolbar {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 12px;
		flex-wrap: wrap;
		gap: 10px;
	}

	.rp-toolbar .entries-control {
		font-size: 13px;
		color: #555;
	}

	.rp-toolbar select.rp-entries {
		display: inline-block;
		width: auto;
		padding: 4px 8px;
		font-size: 13px;
		border: 1px solid #ccc;
		border-radius: 3px;
	}

	.rp-toolbar input.rp-search {
		padding: 6px 12px;
		font-size: 13px;
		border: 1px solid #ccc;
		border-radius: 3px;
		width: 260px;
	}

	#table_rp thead th {
		background-color: #3c8dbc;
		color: #fff;
		font-size: 12px;
		text-align: center;
		vertical-align: middle;
		white-space: nowrap;
	}

	#table_rp tbody td {
		font-size: 12.5px;
		vertical-align: middle;
	}

	.rp-kategori-badge {
		display: inline-block;
		background: #3A4656;
		color: #fff;
		font-size: 10.5px;
		font-weight: 600;
		padding: 3px 9px;
		border-radius: 10px;
		white-space: nowrap;
		margin-bottom: 4px;
	}

	.rp-docnum {
		font-weight: 700;
		color: #2B3440;
	}

	.rp-reject-note {
		margin-top: 5px;
		font-size: 11px;
		color: #C4453B;
		background: #FBEBE9;
		border-left: 2px solid #C4453B;
		padding: 4px 7px;
		border-radius: 2px;
	}

	.rp-reject-note a {
		color: #C4453B;
		text-decoration: underline;
		cursor: pointer;
	}

	.rp-nilai {
		text-align: right;
		white-space: nowrap;
		font-weight: 600;
	}

	.rp-dibayar {
		text-align: right;
		white-space: nowrap;
		font-weight: 700;
		color: #3FA772;
	}

	.rp-tax-wrap {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 2px;
	}

	.rp-tax-amt {
		font-size: 9.5px;
		color: #7C8A99;
		font-weight: 700;
		white-space: nowrap;
	}

	.rp-admin-select {
		font-size: 11px;
		padding: 3px 2px;
		border: 1px solid #E1E5EA;
		border-radius: 4px;
		width: 70px;
	}

	.rp-company-muted {
		color: #7C8A99;
		font-size: 11px;
	}

	/* bulk bar */
	.rp-bulk-bar {
		position: sticky;
		bottom: 0;
		margin-top: 14px;
		background: #26313E;
		color: #fff;
		padding: 12px 18px;
		border-radius: 8px;
		display: none;
		align-items: center;
		justify-content: space-between;
		gap: 16px;
		z-index: 20;
	}

	.rp-bulk-bar.show {
		display: flex;
	}

	.rp-bulk-bar .info b {
		color: #8FC1E8;
	}

	.rp-bulk-bar .actions {
		display: flex;
		align-items: center;
		gap: 10px;
	}

	.rp-table-wrapper {
		overflow-x: auto;
		border: 1px solid #ddd;
	}

	tr.rp-locked td {
		background: #f5f5f5 !important;
		opacity: 0.6;
	}

	/* filter panel */
	.rp-filter-panel {
		background: #f7f9fb;
		border: 1px solid #e3e8ee;
		border-radius: 6px;
		padding: 14px 16px 16px;
		margin-bottom: 16px;
	}

	.rp-filter-label {
		display: block;
		font-size: 11px;
		font-weight: 700;
		color: #8895a7;
		letter-spacing: .3px;
		margin-bottom: 4px;
	}

	.rp-filter-chip {
		display: inline-block;
		margin-top: 12px;
		background: #e8f5e9;
		border: 1px solid #c8e6c9;
		color: #2e7d32;
		font-size: 12px;
		padding: 4px 12px;
		border-radius: 4px;
	}

	/* cards */
	.rp-cards {
		display: flex;
		flex-wrap: wrap;
		margin-left: -7px;
		margin-right: -7px;
		margin-bottom: 10px;
	}

	.rp-cards .rp-card-col {
		width: 25%;
		padding: 7px;
		box-sizing: border-box;
	}

	.rp-card {
		background: #fff;
		border: 1px solid #e3e8ee;
		border-left-width: 4px;
		border-left-color: #cfd8e3;
		border-radius: 6px;
		padding: 14px 16px;
		height: 100%;
		box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
	}

	.rp-card-label {
		font-size: 11px;
		font-weight: 700;
		color: #9aa7b5;
		letter-spacing: .4px;
		margin-bottom: 8px;
		text-transform: uppercase;
	}

	.rp-card-count {
		font-size: 26px;
		font-weight: 700;
		color: #2b3440;
		line-height: 1;
	}

	.rp-card-sub {
		font-size: 11px;
		color: #9aa7b5;
		margin-top: 4px;
	}

	.rp-card-val {
		font-size: 19px;
		font-weight: 700;
		line-height: 1.15;
		word-break: break-word;
	}

	/* responsif */
	@media (max-width: 1199px) {
		.rp-cards .rp-card-col { width: 33.3333%; }
	}
	@media (max-width: 767px) {
		.rp-cards .rp-card-col { width: 50%; }
	}
	@media (max-width: 480px) {
		.rp-cards .rp-card-col { width: 100%; }
	}
	</style>

<div class="box">
	<div class="box-header with-border">
		<div class="pull-right" style="margin-top:2px;">
			<button type="button" id="rp_btn_export" class="btn btn-success btn-sm">
				<i class="fa fa-file-excel-o"></i> Export Excel
			</button>
			<button type="button" id="rp_btn_reset" class="btn btn-default btn-sm">
				<i class="fa fa-refresh"></i> Reset Filter
			</button>
		</div>
		<ol class="breadcrumb" style="background:none;padding:0;margin-bottom:5px;font-size:12px;">
			<li><a href="javascript:void(0);">Finance</a></li>
			<li class="active">Request Payment</li>
		</ol>
		<h3 class="box-title" style="font-size: 22px; font-weight: 600;">
			Dokumen Menunggu Diajukan Approval
			<span class="badge" style="background:#E28B34;" id="rp_count_belum">0</span>
		</h3>
	</div>
	<div class="box-body">

		<!-- ===== FILTER BAR ===== -->
		<div class="rp-filter-panel">
			<div class="row">
				<div class="col-md-3 col-sm-6">
					<label class="rp-filter-label">COMPANY</label>
					<select id="rp_f_company" class="form-control input-sm">
						<option value="">Semua Entitas</option>
						<?php if (!empty($companies)) : foreach ($companies as $c) : ?>
							<option value="<?= $c->id; ?>"><?= $c->nama; ?></option>
						<?php endforeach;
						endif; ?>
					</select>
				</div>
				<div class="col-md-4 col-sm-6">
					<label class="rp-filter-label">PERIODE</label>
					<div class="input-group">
						<input type="date" id="rp_f_from" class="form-control input-sm">
						<span class="input-group-addon">&mdash;</span>
						<input type="date" id="rp_f_to" class="form-control input-sm">
					</div>
				</div>
				<div class="col-md-3 col-sm-8">
					<label class="rp-filter-label">KATEGORI</label>
					<select id="rp_f_kategori" class="form-control input-sm">
						<option value="">Semua</option>
						<option value="Cash">Cash</option>
						<option value="Kasbon">Kasbon</option>
						<option value="Expense">Expense</option>
						<option value="Periodik">Periodik</option>
						<option value="Transport">Transport</option>
						<option value="Petty Cash">Petty Cash</option>
						<option value="Direct Payment">Direct Payment</option>
					</select>
				</div>
				<div class="col-md-2 col-sm-4">
					<label class="rp-filter-label">&nbsp;</label>
					<button type="button" id="rp_btn_filter" class="btn btn-primary btn-sm btn-block">
						<i class="fa fa-filter"></i> Filter
					</button>
				</div>
			</div>
			<div class="rp-filter-chip" id="rp_filter_chip">
				<i class="fa fa-info-circle"></i> <span id="rp_filter_chip_text">Semua Data</span>
			</div>
		</div>

		<!-- ===== SUMMARY CARDS ===== -->
		<div class="rp-cards" id="rp_cards">
			<div class="rp-card-col">
				<div class="rp-card" style="border-left-color:#607d8b;">
					<div class="rp-card-label">Total Pengajuan</div>
					<div class="rp-card-count" id="rp_card_count">0</div>
					<div class="rp-card-sub">item</div>
				</div>
			</div>
			<div class="rp-card-col">
				<div class="rp-card" style="border-left-color:#3c8dbc;">
					<div class="rp-card-label">Total Nilai</div>
					<div class="rp-card-val" style="color:#3c8dbc;">Rp <span id="rp_card_total">0</span></div>
				</div>
			</div>
			<div class="rp-card-col">
				<div class="rp-card" style="border-left-color:#3c8dbc;">
					<div class="rp-card-label">Cash</div>
					<div class="rp-card-val" style="color:#3c8dbc;">Rp <span id="rp_card_Cash">0</span></div>
				</div>
			</div>
			<div class="rp-card-col">
				<div class="rp-card" style="border-left-color:#f39c12;">
					<div class="rp-card-label">Kasbon</div>
					<div class="rp-card-val" style="color:#f39c12;">Rp <span id="rp_card_Kasbon">0</span></div>
				</div>
			</div>
			<div class="rp-card-col">
				<div class="rp-card" style="border-left-color:#dd4b39;">
					<div class="rp-card-label">Expense</div>
					<div class="rp-card-val" style="color:#dd4b39;">Rp <span id="rp_card_Expense">0</span></div>
				</div>
			</div>
			<div class="rp-card-col">
				<div class="rp-card" style="border-left-color:#8e44ad;">
					<div class="rp-card-label">Periodik</div>
					<div class="rp-card-val" style="color:#8e44ad;">Rp <span id="rp_card_Periodik">0</span></div>
				</div>
			</div>
			<div class="rp-card-col">
				<div class="rp-card" style="border-left-color:#00a65a;">
					<div class="rp-card-label">Transport</div>
					<div class="rp-card-val" style="color:#00a65a;">Rp <span id="rp_card_Transport">0</span></div>
				</div>
			</div>
			<div class="rp-card-col">
				<div class="rp-card" style="border-left-color:#00c0ef;">
					<div class="rp-card-label">Petty Cash</div>
					<div class="rp-card-val" style="color:#00c0ef;">Rp <span id="rp_card_PettyCash">0</span></div>
				</div>
			</div>
			<div class="rp-card-col">
				<div class="rp-card" style="border-left-color:#605ca8;">
					<div class="rp-card-label">Direct Payment</div>
					<div class="rp-card-val" style="color:#605ca8;">Rp <span id="rp_card_DirectPayment">0</span></div>
				</div>
			</div>
		</div>

		<div class="rp-toolbar">
			<div class="entries-control">
				Tampilkan
				<select id="rp_entries" class="rp-entries">
					<option value="10">10</option>
					<option value="25" selected>25</option>
					<option value="50">50</option>
				</select>
				entri per halaman
			</div>
			<div>
				<input type="text" id="rp_search" class="rp-search" placeholder="Cari dokumen, pemohon...">
			</div>
		</div>

		<div class="rp-table-wrapper">
			<table id="table_rp" class="table table-bordered table-striped" style="width:100%;">
				<thead>
					<tr>
						<th style="width:30px;"><input type="checkbox" id="rp_check_all"></th>
						<th style="width:36px;">NO</th>
						<th>NO. DOKUMEN / KATEGORI</th>
						<th>REQUEST BY</th>
						<th style="width:90px;">TANGGAL</th>
						<th>KEPERLUAN</th>
						<th style="width:100px;">DPP (RP)</th>
						<th style="width:60px;">PPN</th>
						<th style="width:60px;">PPH 23</th>
						<th style="width:60px;">PPH 21</th>
						<th style="width:80px;">ADMIN</th>
						<th style="width:110px;">DIBAYARKAN</th>
						<th style="width:60px;">AKSI</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>

		<div class="rp-bulk-bar" id="rp_bulk_bar">
			<div class="info">
				<b id="rp_bulk_count">0</b> dokumen dipilih<span id="rp_bulk_company"></span> &mdash; siap diajukan untuk approval
			</div>
			<div class="actions">
				<button type="button" class="btn btn-default btn-sm" onclick="rpClearSelection();">Batal</button>
				<?php if ($ENABLE_MANAGE): ?>
					<button type="button" class="btn btn-primary btn-sm" onclick="rpSubmitForApproval();">
						<i class="fa fa-paper-plane"></i> Ajukan untuk Approval
					</button>
				<?php endif; ?>
			</div>
		</div>

	</div>
</div>

<!-- Modal Histori Reject -->
<div class="modal fade" id="rpRejectModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Histori Reject — <span id="rp_reject_doc"></span></h4>
			</div>
			<div class="modal-body" id="rp_reject_body">
				<p class="text-muted">Memuat...</p>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.11/js/dataTables.bootstrap.min.js"></script>

<script>
	var rpTable = null;

	// state seleksi + flag pajak per dokumen (persist antar redraw & paging)
	// key: no_dokumen -> { selected, ppn, pph23, pph21, admin, dpp, company_id, company_nama }
	var rpState = {};

	function rpFmt(n) {
		n = Math.round(Number(n) || 0);
		return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
	}

	// baca nilai filter aktif dari form
	function rpGetFilters() {
		return {
			company_id: $('#rp_f_company').val() || '',
			date_from: $('#rp_f_from').val() || '',
			date_to: $('#rp_f_to').val() || '',
			kategori: $('#rp_f_kategori').val() || ''
		};
	}

	// update chip ringkasan filter aktif
	function rpUpdateFilterChip() {
		var f = rpGetFilters();
		var parts = [];
		var compTxt = $('#rp_f_company option:selected').text();
		parts.push(compTxt || 'Semua Entitas');
		if (f.date_from || f.date_to) {
			parts.push('Periode: ' + (f.date_from || '...') + ' s/d ' + (f.date_to || '...'));
		}
		if (f.kategori) {
			parts.push('Kategori: ' + f.kategori);
		}
		$('#rp_filter_chip_text').text(parts.join(' | '));
	}

	// muat kartu ringkasan (hormati filter yang sama dengan tabel)
	function rpLoadSummary() {
		$.ajax({
			url: '<?= site_url("request_payment/get_summary_request"); ?>',
			type: 'POST',
			data: rpGetFilters(),
			dataType: 'json',
			success: function(res) {
				$('#rp_card_count').text(res.total_pengajuan || 0);
				$('#rp_card_total').text(rpFmt(res.total_nilai || 0));
				var t = res.per_tipe || {};
				$('#rp_card_Cash').text(rpFmt(t['Cash'] || 0));
				$('#rp_card_Kasbon').text(rpFmt(t['Kasbon'] || 0));
				$('#rp_card_Expense').text(rpFmt(t['Expense'] || 0));
				$('#rp_card_Periodik').text(rpFmt(t['Periodik'] || 0));
				$('#rp_card_Transport').text(rpFmt(t['Transport'] || 0));
				$('#rp_card_PettyCash').text(rpFmt(t['Petty Cash'] || 0));
				$('#rp_card_DirectPayment').text(rpFmt(t['Direct Payment'] || 0));
			}
		});
	}

	// ceiling calc (mirror server)
	function rpCalc(st) {
		var dpp = Number(st.dpp) || 0;
		var ppn = st.ppn ? Math.ceil(dpp * 0.11) : 0;
		var pph = 0;
		if (st.pph21) pph = Math.ceil(dpp * 0.025);
		else if (st.pph23) pph = Math.ceil(dpp * 0.02);
		var admin = Number(st.admin) || 0;
		return {
			ppn: ppn,
			pph: pph,
			admin: admin,
			dibayarkan: (dpp + ppn) - pph - admin
		};
	}

	function rpGetState(no) {
		if (!rpState[no]) {
			rpState[no] = {
				selected: false,
				ppn: false,
				pph23: false,
				pph21: false,
				admin: 0,
				dpp: 0,
				company_id: null,
				company_nama: ''
			};
		}
		return rpState[no];
	}

	// company yang lagi dikunci oleh seleksi saat ini (null bila belum ada company non-kosong terpilih)
	function rpGetLockedCompany() {
		for (var no in rpState) {
			if (rpState[no].selected && rpState[no].company_id) {
				return {
					id: rpState[no].company_id,
					nama: rpState[no].company_nama
				};
			}
		}
		return null;
	}

	function rpSelectedList() {
		var arr = [];
		for (var no in rpState) {
			if (rpState[no].selected) arr.push(no);
		}
		return arr;
	}

	function rpUpdateBulkBar() {
		var sel = rpSelectedList();
		var bar = document.getElementById('rp_bulk_bar');
		if (sel.length > 0) {
			bar.classList.add('show');
			document.getElementById('rp_bulk_count').textContent = sel.length;
			var locked = rpGetLockedCompany();
			document.getElementById('rp_bulk_company').textContent = locked ? (' (Company: ' + locked.nama + ')') : '';
		} else {
			bar.classList.remove('show');
		}
	}

	// refresh 1 baris (recalc + amounts) tanpa redraw seluruh tabel
	function rpRefreshRow(no) {
		var st = rpGetState(no);
		var c = rpCalc(st);
		$('#rp_ppn_amt_' + no).text(st.ppn ? rpFmt(c.ppn) : '');
		$('#rp_pph23_amt_' + no).text(st.pph23 ? rpFmt(c.pph) : '');
		$('#rp_pph21_amt_' + no).text(st.pph21 ? rpFmt(c.pph) : '');
		$('#rp_dibayar_' + no).text(rpFmt(c.dibayarkan));
	}

	function rpApplyLockUI() {
		var locked = rpGetLockedCompany();
		$('#table_rp tbody tr').each(function() {
			var no = $(this).data('no');
			if (!no) return;
			var st = rpGetState(no);
			var chk = $(this).find('.rp-rowchk');
			var lockedOut = locked && st.company_id && st.company_id !== locked.id && !st.selected;
			if (lockedOut) {
				chk.prop('disabled', true).attr('title', 'Hanya bisa gabung dokumen dari company ' + locked.nama);
				$(this).addClass('rp-locked');
			} else {
				chk.prop('disabled', false).removeAttr('title');
				$(this).removeClass('rp-locked');
			}
			chk.prop('checked', st.selected);
		});
	}

	$(document).ready(function() {

		rpTable = $('#table_rp').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: '<?= site_url("request_payment/get_data_request"); ?>',
				type: 'POST',
				data: function(d) {
					d.search = {
						value: $('#rp_search').val()
					};
					var f = rpGetFilters();
					d.company_id = f.company_id;
					d.date_from = f.date_from;
					d.date_to = f.date_to;
					d.kategori = f.kategori;
				},
				error: function() {
					alert('Gagal memuat data. Silakan coba lagi.');
				}
			},
			columns: [{
					data: 'no_dokumen',
					orderable: false,
					searchable: false,
					render: function(data, type, row) {
						var st = rpGetState(row.no_dokumen);
						st.dpp = row.dpp;
						st.company_id = row.company_id;
						st.company_nama = row.company_nama;
						return '<input type="checkbox" class="rp-rowchk" data-no="' + row.no_dokumen + '">';
					}
				},
				{
					data: null,
					orderable: false,
					searchable: false,
					render: function(data, type, row, meta) {
						return meta.row + 1 + meta.settings._iDisplayStart;
					}
				},
				{
					data: 'no_dokumen',
					render: function(data, type, row) {
						return '<span class="rp-kategori-badge">' + (row.kategori || '-') + '</span><br>' +
							'<span class="rp-docnum">' + row.no_dokumen + '</span>';
					}
				},
				{
					data: 'request_by',
					render: function(data, type, row) {
						var comp = row.company_nama ? ' <span class="rp-company-muted">- ' + row.company_nama + '</span>' : '';
						return (row.request_by || '') + comp;
					}
				},
				{
					data: 'tanggal'
				},
				{
					data: 'keperluan',
					render: function(data, type, row) {
						var html = '<div>' + (row.keperluan || '') + '</div>';
						if (row.reject_reason) {
							html += '<div class="rp-reject-note">Ditolak: ' + $('<div>').text(row.reject_reason).html() +
								' <a onclick="rpShowRejectHistory(\'' + row.no_dokumen + '\')">(histori)</a></div>';
						}
						return html;
					}
				},
				{
					data: 'dpp',
					className: 'rp-nilai',
					render: function(data) {
						return rpFmt(data);
					}
				},
				{
					data: 'no_dokumen',
					orderable: false,
					searchable: false,
					render: function(data, type, row) {
						var no = row.no_dokumen;
						var st = rpGetState(no);
						return '<div class="rp-tax-wrap"><input type="checkbox" class="rp-ppn" data-no="' + no + '" ' + (st.ppn ? 'checked' : '') + '>' +
							'<div class="rp-tax-amt" id="rp_ppn_amt_' + no + '"></div></div>';
					}
				},
				{
					data: 'no_dokumen',
					orderable: false,
					searchable: false,
					render: function(data, type, row) {
						var no = row.no_dokumen;
						var st = rpGetState(no);
						return '<div class="rp-tax-wrap"><input type="checkbox" class="rp-pph23" data-no="' + no + '" ' + (st.pph23 ? 'checked' : '') + '>' +
							'<div class="rp-tax-amt" id="rp_pph23_amt_' + no + '"></div></div>';
					}
				},
				{
					data: 'no_dokumen',
					orderable: false,
					searchable: false,
					render: function(data, type, row) {
						var no = row.no_dokumen;
						var st = rpGetState(no);
						return '<div class="rp-tax-wrap"><input type="checkbox" class="rp-pph21" data-no="' + no + '" ' + (st.pph21 ? 'checked' : '') + '>' +
							'<div class="rp-tax-amt" id="rp_pph21_amt_' + no + '"></div></div>';
					}
				},
				{
					data: 'no_dokumen',
					orderable: false,
					searchable: false,
					render: function(data, type, row) {
						var no = row.no_dokumen;
						var st = rpGetState(no);
						return '<select class="rp-admin-select rp-admin" data-no="' + no + '">' +
							'<option value="0" ' + (st.admin == 0 ? 'selected' : '') + '>-</option>' +
							'<option value="2500" ' + (st.admin == 2500 ? 'selected' : '') + '>2.500</option>' +
							'<option value="6500" ' + (st.admin == 6500 ? 'selected' : '') + '>6.500</option>' +
							'</select>';
					}
				},
				{
					data: 'no_dokumen',
					orderable: false,
					searchable: false,
					className: 'rp-dibayar',
					render: function(data, type, row) {
						return '<span id="rp_dibayar_' + row.no_dokumen + '">' + rpFmt(row.dpp) + '</span>';
					}
				},
				{
					data: 'print_url',
					orderable: false,
					searchable: false,
					className: 'text-center',
					render: function(data, type, row) {
						if (data && data !== '') {
							return '<a href="' + data + '" target="_blank" class="btn btn-sm btn-info" title="Print Dokumen"><i class="fa fa-print"></i></a>';
						}
						return '<span class="text-muted" title="Tidak ada dokumen print untuk tipe ini">-</span>';
					}
				}
			],
			pageLength: 25,
			lengthChange: false,
			searching: false,
			dom: 'rtip',
			language: {
				processing: '<i class="fa fa-spinner fa-spin"></i> Memproses...',
				emptyTable: 'Tidak ada dokumen pada status ini.',
				zeroRecords: 'Tidak ada dokumen yang cocok.',
				info: 'Menampilkan _START_ - _END_ dari _TOTAL_ entri',
				infoEmpty: 'Menampilkan 0 - 0 dari 0 entri',
				paginate: {
					first: 'Pertama',
					last: 'Terakhir',
					next: 'Selanjutnya',
					previous: 'Sebelumnya'
				}
			},
			drawCallback: function(settings) {
				var json = this.api().ajax.json();
				if (json) {
					$('#rp_count_belum').text(json.recordsTotal || 0);
				}
				// restore amounts + lock UI
				$('#table_rp tbody tr').each(function() {
					var no = $(this).find('.rp-rowchk').data('no');
					if (no) {
						$(this).attr('data-no', no);
						rpRefreshRow(no);
					}
				});
				rpApplyLockUI();
				rpUpdateBulkBar();
			}
		});

		// entries per page
		$('#rp_entries').on('change', function() {
			rpTable.page.len(parseInt($(this).val())).draw();
		});

		// search
		$('#rp_search').on('keyup', function() {
			rpTable.draw();
		});

		// filter button: reload tabel + kartu + chip (reset seleksi agar tidak lintas-filter)
		$('#rp_btn_filter').on('click', function() {
			rpState = {};
			rpUpdateFilterChip();
			rpTable.draw();
			rpLoadSummary();
		});

		// reset filter: kosongkan semua input lalu reload
		$('#rp_btn_reset').on('click', function() {
			$('#rp_f_company').val('');
			$('#rp_f_from').val('');
			$('#rp_f_to').val('');
			$('#rp_f_kategori').val('');
			$('#rp_search').val('');
			rpState = {};
			rpUpdateFilterChip();
			rpTable.draw();
			rpLoadSummary();
		});

		// export excel: buka tab baru dengan filter aktif sebagai query string
		$('#rp_btn_export').on('click', function() {
			var f = rpGetFilters();
			var params = $.param({
				company_id: f.company_id,
				date_from: f.date_from,
				date_to: f.date_to,
				kategori: f.kategori,
				search: $('#rp_search').val() || ''
			});
			window.open('<?= site_url("request_payment/export_excel_request"); ?>?' + params, '_blank');
		});

		// muat kartu pertama kali
		rpUpdateFilterChip();
		rpLoadSummary();

		// select row
		$('#table_rp tbody').on('change', '.rp-rowchk', function() {
			var no = $(this).data('no');
			rpGetState(no).selected = $(this).is(':checked');
			rpApplyLockUI();
			rpUpdateBulkBar();
		});

		// check all (only selectable rows in current page)
		$('#rp_check_all').on('change', function() {
			var checked = $(this).is(':checked');
			var locked = rpGetLockedCompany();
			$('#table_rp tbody .rp-rowchk').each(function() {
				if ($(this).prop('disabled')) return;
				var no = $(this).data('no');
				var st = rpGetState(no);
				// jika belum ada lock, izinkan; jika ada lock, hanya company sama
				if (checked) {
					var curLock = rpGetLockedCompany();
					if (curLock && st.company_id && st.company_id !== curLock.id) return;
					st.selected = true;
				} else {
					st.selected = false;
				}
			});
			rpApplyLockUI();
			rpUpdateBulkBar();
		});

		// tax flags
		$('#table_rp tbody').on('change', '.rp-ppn', function() {
			rpGetState($(this).data('no')).ppn = $(this).is(':checked');
			rpRefreshRow($(this).data('no'));
		});
		$('#table_rp tbody').on('change', '.rp-pph23', function() {
			var no = $(this).data('no');
			var st = rpGetState(no);
			st.pph23 = $(this).is(':checked');
			if (st.pph23) {
				st.pph21 = false;
				$('.rp-pph21[data-no="' + no + '"]').prop('checked', false);
			}
			rpRefreshRow(no);
		});
		$('#table_rp tbody').on('change', '.rp-pph21', function() {
			var no = $(this).data('no');
			var st = rpGetState(no);
			st.pph21 = $(this).is(':checked');
			if (st.pph21) {
				st.pph23 = false;
				$('.rp-pph23[data-no="' + no + '"]').prop('checked', false);
			}
			rpRefreshRow(no);
		});
		$('#table_rp tbody').on('change', '.rp-admin', function() {
			rpGetState($(this).data('no')).admin = parseInt($(this).val()) || 0;
			rpRefreshRow($(this).data('no'));
		});
	});

	window.rpClearSelection = function() {
		for (var no in rpState) {
			rpState[no].selected = false;
		}
		$('#table_rp tbody .rp-rowchk').prop('checked', false);
		$('#rp_check_all').prop('checked', false);
		rpApplyLockUI();
		rpUpdateBulkBar();
	};

	window.rpShowRejectHistory = function(no) {
		$('#rp_reject_doc').text(no);
		$('#rp_reject_body').html('<p class="text-muted">Memuat...</p>');
		$('#rpRejectModal').modal('show');
		$.ajax({
			url: '<?= site_url("request_payment/reject_history"); ?>',
			type: 'POST',
			data: {
				no_dokumen: no
			},
			dataType: 'json',
			success: function(res) {
				if (!res.data || res.data.length === 0) {
					$('#rp_reject_body').html('<p class="text-muted">Belum ada histori reject.</p>');
					return;
				}
				var html = '<ul class="list-group">';
				res.data.forEach(function(r) {
					html += '<li class="list-group-item">' +
						'<div><b>' + (r.no_pengajuan || '-') + '</b> <span class="text-muted pull-right">' + r.rejected_at + '</span></div>' +
						'<div>Oleh: ' + (r.approver || '-') + '</div>' +
						'<div style="color:#C4453B;">' + $('<div>').text(r.alasan || '').html() + '</div>' +
						'</li>';
				});
				html += '</ul>';
				$('#rp_reject_body').html(html);
			},
			error: function() {
				$('#rp_reject_body').html('<p class="text-danger">Gagal memuat histori.</p>');
			}
		});
	};

	window.rpSubmitForApproval = function() {
		var sel = rpSelectedList();
		if (sel.length === 0) {
			alert('Pilih minimal 1 dokumen untuk diajukan.');
			return;
		}

		// client-side company-lock guard (server tetap validasi ulang)
		var comp = null,
			mixed = false;
		sel.forEach(function(no) {
			var cid = rpState[no].company_id;
			if (cid) {
				if (comp === null) comp = cid;
				else if (comp !== cid) mixed = true;
			}
		});
		if (mixed) {
			alert('Semua dokumen dalam 1 pengajuan harus dari company yang sama.');
			return;
		}

		if (!confirm(sel.length + ' dokumen akan diajukan untuk approval. Lanjutkan?')) return;

		var items = sel.map(function(no) {
			var st = rpState[no];
			return {
				no_dokumen: no,
				ppn: st.ppn ? 1 : 0,
				pph23: st.pph23 ? 1 : 0,
				pph21: st.pph21 ? 1 : 0,
				admin: st.admin || 0
			};
		});

		$.ajax({
			url: '<?= site_url("request_payment/submit_for_approval"); ?>',
			type: 'POST',
			data: {
				items: items
			},
			dataType: 'json',
			success: function(res) {
				if (res.status == 1) {
					alert(res.msg || 'Pengajuan berhasil dibuat.');
					rpState = {};
					rpTable.draw(false);
					rpLoadSummary();
				} else {
					alert(res.msg || 'Gagal mengajukan.');
				}
			},
			error: function() {
				alert('Terjadi error. Silakan coba lagi.');
			}
		});
	};
</script>
