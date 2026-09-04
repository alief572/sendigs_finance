<?php
$ENABLE_ADD     = has_permission('Report_Jurnal_Penerimaan.Add');
$ENABLE_MANAGE  = has_permission('Report_Jurnal_Penerimaan.Manage');
$ENABLE_VIEW    = has_permission('Report_Jurnal_Penerimaan.View');
$ENABLE_DELETE  = has_permission('Report_Jurnal_Penerimaan.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
  :root {
    --blue-header: #1f6fb2;
    --blue-dark: #155a91;
    --bg: #f4f6f8;
    --card-bg: #ffffff;
    --border: #e2e6ea;
    --text: #2b333b;
    --text-muted: #6b7684;
    --green: #28a745;
    --red: #d9534f;
    --amber: #d98c15;
  }

  .panel {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 20px;
  }

  .panel-header {
    background: #eef3f7;
    border-bottom: 1px solid var(--border);
    padding: 14px 20px;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--text);
  }

  .panel-body {
    padding: 18px 20px 15px 20px;
  }

  .filters {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    margin-bottom: 18px;
  }

  .filter-select-client {
    min-width: 220px;
    flex: 1 1 220px;
    max-width: 280px;
  }

  .filter-select-company {
    min-width: 200px;
    flex: 1 1 200px;
    max-width: 260px;
  }

  .filter-select-year {
    width: 110px;
    flex: 0 0 110px;
  }

  .select2-container--default .select2-selection--single {
    border-radius: 5px;
    border: 1px solid #cfd6dc;
    height: 35px;
    line-height: 35px;
  }

  .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 33px;
    font-size: 13px;
    color: var(--text);
  }

  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 33px;
  }

  .filters .spacer {
    flex: 1;
  }

  .btn-mock {
    border: none;
    color: #fff;
    cursor: pointer;
    font-weight: 500;
    padding: 8px 14px;
    border-radius: 5px;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: opacity 0.2s;
  }
  .btn-mock:hover {
    opacity: 0.9;
    color: #fff;
  }

  .btn-search {
    background: var(--blue-header);
  }
  .btn-export {
    background: var(--green);
  }
  .btn-reset {
    background: var(--red);
  }

  .summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 6px;
  }

  @media (max-width: 992px) {
    .summary-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  .summary-card {
    border: 1px solid var(--border);
    border-left: 4px solid var(--blue-header);
    border-radius: 6px;
    padding: 10px 14px;
    background: #fafcfe;
  }
  .summary-card.warn {
    border-left-color: var(--amber);
  }
  .summary-card.danger {
    border-left-color: var(--red);
  }

  .summary-card .label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: var(--text-muted);
    margin-bottom: 4px;
    display: block;
    text-align: left !important;
  }

  .summary-card .value {
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
    font-variant-numeric: tabular-nums;
    text-align: right !important;
  }

  .summary-note {
    font-size: 12.5px;
    color: var(--text-muted);
    margin: 6px 0 16px 0;
  }

  .table-scroll {
    position: relative;
    max-height: calc(100vh - 350px);
    min-height: 400px;
    overflow: auto;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: #ffffff;
  }

  /* Custom Modern Scrollbar */
  .table-scroll::-webkit-scrollbar {
    width: 8px;
    height: 8px;
  }
  .table-scroll::-webkit-scrollbar-track {
    background: #f1f5f9;
  }
  .table-scroll::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 4px;
  }
  .table-scroll::-webkit-scrollbar-thumb:hover {
    background: #64748b;
  }

  table.report-table {
    border-collapse: separate !important;
    border-spacing: 0 !important;
    width: 100% !important;
    font-size: 12.5px;
    min-width: 1900px;
    margin-bottom: 0 !important;
  }

  /* Sticky Thead - ALL HEADERS */
  table.report-table thead th,
  table.dataTable thead th,
  #table_penawaran thead th {
    position: sticky !important;
    top: 0 !important;
    z-index: 30 !important;
    background-color: var(--blue-header) !important;
    background: var(--blue-header) !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    text-align: left;
    padding: 10px 10px !important;
    white-space: nowrap !important;
    border: none !important;
    box-shadow: inset 0 -1px 0 var(--blue-dark), 0 2px 4px rgba(0, 0, 0, 0.15) !important;
  }

  /* Sticky Tfoot - ALL FOOTERS */
  table.report-table tfoot td,
  table.report-table tfoot th,
  table.dataTable tfoot td,
  table.dataTable tfoot th,
  #table_penawaran tfoot td,
  #table_penawaran tfoot th {
    position: sticky !important;
    bottom: 0 !important;
    z-index: 30 !important;
    background-color: #2b3a45 !important;
    background: #2b3a45 !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    padding: 10px 10px !important;
    white-space: nowrap !important;
    border: none !important;
    box-shadow: inset 0 1px 0 var(--blue-dark), 0 -2px 4px rgba(0, 0, 0, 0.12) !important;
  }

  table.report-table tbody td {
    padding: 8px 10px;
    border-bottom: 1px solid var(--border);
    border-right: 1px solid var(--border);
    white-space: nowrap;
    background: #ffffff;
    vertical-align: middle;
  }

  table.report-table tbody tr:nth-child(even) td {
    background-color: #fbfcfe;
  }
  table.report-table tbody tr:hover td {
    background-color: #f5f9fc !important;
  }

  .col-no {
    width: 45px !important;
    min-width: 45px !important;
    text-align: center !important;
  }
  .col-company {
    min-width: 120px !important;
  }
  .col-customer {
    min-width: 170px !important;
  }
  .col-spk {
    min-width: 140px !important;
  }
  .col-consultant {
    min-width: 140px !important;
  }
  .col-project {
    min-width: 240px !important;
    max-width: 280px !important;
  }
  .col-currency {
    min-width: 115px !important;
    text-align: right !important;
  }
  .col-month {
    min-width: 85px !important;
    text-align: right !important;
  }

  .project-cell {
    max-width: 260px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block;
    vertical-align: middle;
    cursor: help;
  }

  /* DataTables controls styling */
  .dt-container .dt-length,
  .dt-container .dt-search,
  .dt-container .dt-info,
  .dt-container .dt-paging {
    padding: 8px 4px;
    font-size: 13px;
  }

  /* Custom Dual-Tab Styles */
  .custom-tab-header {
    background: #eef3f7;
    border-bottom: 1px solid var(--border);
    padding: 10px 20px 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 10px;
  }

  .custom-tab-header .title-text {
    font-size: 16px;
    font-weight: 600;
    color: var(--text);
    padding-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .nav-tabs.custom-tabs {
    border-bottom: none !important;
    margin-bottom: -1px;
    display: flex;
    gap: 4px;
  }

  .nav-tabs.custom-tabs > li > a {
    background-color: #e2e8f0;
    color: #475569;
    border: 1px solid #cbd5e1 !important;
    border-bottom: none !important;
    border-radius: 6px 6px 0 0 !important;
    padding: 9px 18px;
    font-size: 13.5px;
    font-weight: 600;
    transition: all 0.2s;
    margin-right: 0;
  }

  .nav-tabs.custom-tabs > li.active > a,
  .nav-tabs.custom-tabs > li.active > a:focus,
  .nav-tabs.custom-tabs > li.active > a:hover {
    background-color: #ffffff !important;
    color: #1e293b !important;
    border-color: var(--border) var(--border) #ffffff !important;
    box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.05);
  }

  .nav-tabs.custom-tabs > li > a:hover {
    background-color: #edf2f7;
    color: #1e293b;
  }

  /* Table Style for Macet */
  .table-scroll-macet {
    position: relative;
    max-height: calc(100vh - 350px);
    min-height: 400px;
    overflow: auto;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: #ffffff;
  }

  table.report-table-macet {
    border-collapse: separate !important;
    border-spacing: 0 !important;
    width: 100% !important;
    font-size: 12.5px;
    margin-bottom: 0 !important;
  }

  table.report-table-macet thead th {
    position: sticky !important;
    top: 0 !important;
    z-index: 30 !important;
    background-color: #b91c1c !important;
    background: #b91c1c !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    text-align: left;
    padding: 10px 10px !important;
    white-space: nowrap !important;
    border: none !important;
    box-shadow: inset 0 -1px 0 #991b1b, 0 2px 4px rgba(0, 0, 0, 0.15) !important;
  }

  table.report-table-macet tfoot td,
  table.report-table-macet tfoot th {
    position: sticky !important;
    bottom: 0 !important;
    z-index: 30 !important;
    background-color: #2b3a45 !important;
    background: #2b3a45 !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    padding: 10px 10px !important;
    white-space: nowrap !important;
    border: none !important;
    box-shadow: inset 0 1px 0 #1e293b, 0 -2px 4px rgba(0, 0, 0, 0.12) !important;
  }

  table.report-table-macet tbody td {
    padding: 8px 10px;
    border-bottom: 1px solid var(--border);
    border-right: 1px solid var(--border);
    white-space: nowrap;
    background: #ffffff;
    vertical-align: middle;
  }

  table.report-table-macet tbody tr:nth-child(even) td {
    background-color: #fbfcfe;
  }
  table.report-table-macet tbody tr:hover td {
    background-color: #fef2f2 !important;
  }

  .btn-termin-badge {
    background: #fee2e2;
    color: #b91c1c !important;
    border: 1px solid #f87171;
    border-radius: 4px;
    padding: 3px 8px;
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
  }
  .btn-termin-badge:hover {
    background: #b91c1c;
    color: #ffffff !important;
    border-color: #991b1b;
  }
</style>

<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>

<div class="panel">
  <!-- Dual-Tab Navigation Header -->
  <div class="custom-tab-header">
    <div class="title-text">
      <i class="fa fa-file-text-o text-primary"></i> Report Actual Plan Tagih
    </div>
    <ul class="nav nav-tabs custom-tabs" role="tablist">
      <li role="presentation" class="active">
        <a href="#tab_annual" aria-controls="tab_annual" role="tab" data-toggle="tab">
          <i class="fa fa-calendar text-primary"></i> Plan Tagih (Tahunan)
        </a>
      </li>
      <li role="presentation">
        <a href="#tab_macet" aria-controls="tab_macet" role="tab" data-toggle="tab">
          <i class="fa fa-exclamation-triangle text-danger"></i> Tagihan Macet (All-Time)
        </a>
      </li>
    </ul>
  </div>

  <div class="panel-body">
    <div class="tab-content">

      <!-- TAB 1: PLAN TAGIH TAHUNAN -->
      <div role="tabpanel" class="tab-pane active" id="tab_annual">
        <!-- Filters Bar -->
        <div class="filters">
          <div class="filter-select-client">
            <select class="form-control select2" name="client">
              <option value="">- Select Client -</option>
              <?php foreach ($list_customer as $item_customer) : ?>
                <option value="<?= $item_customer->id_customer ?>"><?= strtoupper($item_customer->nm_customer) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="filter-select-company">
            <select class="form-control select2" name="company">
              <option value="">- Select Company -</option>
              <?php foreach ($list_company as $item_company) : ?>
                <option value="<?= $item_company->id_company ?>"><?= strtoupper($item_company->nm_company) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="filter-select-year">
            <select class="form-control select2" name="tahun">
              <?php
              for ($i = (date('Y') - 5); $i <= date('Y'); $i++) {
                  $selected = ($i == date('Y')) ? 'selected' : '';
                  echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
              }
              for ($i = (date('Y') + 1); $i <= ((date('Y') + 1) + 5); $i++) {
                  echo '<option value="' . $i . '">' . $i . '</option>';
              }
              ?>
            </select>
          </div>
          <button type="button" class="btn-mock btn-search" onclick="search_data();">
            <i class="fa fa-search"></i> Search
          </button>
          <button type="button" class="btn-mock btn-export" onclick="download_excel();">
            <i class="fa fa-download"></i> Download Excel
          </button>
          <button type="button" class="btn-mock btn-reset" onclick="reset();">
            <i class="fa fa-refresh"></i> Reset
          </button>
          <div class="spacer"></div>
        </div>

        <!-- 4 KPI Summary Cards -->
        <div class="summary-grid">
          <div class="summary-card">
            <div class="label">Nominal SPK</div>
            <div class="value" id="sumSpk">0</div>
          </div>
          <div class="summary-card">
            <div class="label">Nominal Invoice</div>
            <div class="value" id="sumInvoice">0</div>
          </div>
          <div class="summary-card warn">
            <div class="label">Nominal Un-Invoiced</div>
            <div class="value" id="sumUninvoiced">0</div>
          </div>
          <div class="summary-card danger">
            <div class="label">Macet</div>
            <div class="value" id="sumMacet">0</div>
          </div>
        </div>
        <div class="summary-note">
          Ringkasan dihitung dari seluruh baris yang cocok dengan filter Client/Company/Tahun saat ini — bukan cuma baris yang tampil di page ini — dan otomatis update saat filter berubah.
        </div>

        <!-- Sticky Table Container -->
        <div class="table-scroll">
          <table id="table_penawaran" class="table report-table">
            <thead>
              <tr>
                <th class="text-center col-no">No.</th>
                <th class="col-company">Company</th>
                <th class="col-customer">Customer</th>
                <th class="col-spk">No. SPK</th>
                <th class="col-consultant">Consultant</th>
                <th class="col-project">Project</th>
                <th class="text-right col-currency">Nominal SPK</th>
                <th class="text-right col-currency">Nominal Invoice</th>
                <th class="text-right col-currency">Un-Invoiced</th>
                <th class="text-right col-currency">Macet</th>
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    $no_bulan = sprintf('%02s', $i);
                    echo '<th class="text-right col-month">' . date('M', strtotime(date('Y') . '-' . $no_bulan . '-01')) . '</th>';
                }
                ?>
              </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="6" class="text-center" id="grandTotalLabel">Grand Total</th>
                <th class="text-right col-currency ttl_nominal_spk">0</th>
                <th class="text-right col-currency ttl_nominal_invoice">0</th>
                <th class="text-right col-currency ttl_nominal_uninvoice">0</th>
                <th class="text-right col-currency ttl_macet">0</th>
                <th class="text-right col-month ttl_jan">0</th>
                <th class="text-right col-month ttl_feb">0</th>
                <th class="text-right col-month ttl_mar">0</th>
                <th class="text-right col-month ttl_apr">0</th>
                <th class="text-right col-month ttl_may">0</th>
                <th class="text-right col-month ttl_jun">0</th>
                <th class="text-right col-month ttl_jul">0</th>
                <th class="text-right col-month ttl_aug">0</th>
                <th class="text-right col-month ttl_sep">0</th>
                <th class="text-right col-month ttl_oct">0</th>
                <th class="text-right col-month ttl_nov">0</th>
                <th class="text-right col-month ttl_dec">0</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- TAB 2: MONITORING TAGIHAN MACET (ALL-TIME) -->
      <div role="tabpanel" class="tab-pane" id="tab_macet">
        <!-- Filters Bar Macet -->
        <div class="filters">
          <div class="filter-select-client">
            <select class="form-control select2-macet" name="client_macet">
              <option value="">- Select Client -</option>
              <?php foreach ($list_customer as $item_customer) : ?>
                <option value="<?= $item_customer->id_customer ?>"><?= strtoupper($item_customer->nm_customer) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="filter-select-company">
            <select class="form-control select2-macet" name="company_macet">
              <option value="">- Select Company -</option>
              <?php foreach ($list_company as $item_company) : ?>
                <option value="<?= $item_company->id_company ?>"><?= strtoupper($item_company->nm_company) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="button" class="btn-mock btn-search" onclick="search_data_macet();">
            <i class="fa fa-search"></i> Search
          </button>
          <button type="button" class="btn-mock btn-export" onclick="download_excel_macet();">
            <i class="fa fa-download"></i> Download Excel
          </button>
          <button type="button" class="btn-mock btn-reset" onclick="reset_macet();">
            <i class="fa fa-refresh"></i> Reset
          </button>
          <div class="spacer"></div>
        </div>

        <!-- 4 KPI Summary Cards Macet -->
        <div class="summary-grid">
          <div class="summary-card">
            <div class="label">Total SPK Macet</div>
            <div class="value" id="sumSpkMacet">0</div>
          </div>
          <div class="summary-card">
            <div class="label">Total Nilai SPK</div>
            <div class="value" id="sumNominalSpkMacet">0</div>
          </div>
          <div class="summary-card">
            <div class="label">Sudah Terinvoice</div>
            <div class="value" id="sumInvoiceMacet">0</div>
          </div>
          <div class="summary-card danger">
            <div class="label">Total Tagihan Macet</div>
            <div class="value" id="sumTotalMacet" style="color: #b91c1c;">0</div>
          </div>
        </div>
        <div class="summary-note">
          Menampilkan seluruh SPK yang memiliki termin tagihan berstatus Macet (belum terinvoice) secara menyeluruh lintas tahun (All-Time). Klik tombol <strong>Detail</strong> pada baris untuk melihat rincian termin.
        </div>

        <!-- Sticky Table Container Macet -->
        <div class="table-scroll-macet">
          <table id="table_macet" class="table report-table-macet">
            <thead>
              <tr>
                <th class="text-center col-no">No.</th>
                <th class="col-company">Company</th>
                <th class="col-customer">Customer</th>
                <th class="col-spk">No. SPK</th>
                <th class="col-consultant">Consultant</th>
                <th class="col-project">Project</th>
                <th class="text-right col-currency">Nominal SPK</th>
                <th class="text-right col-currency">Nominal Invoice</th>
                <th class="text-right col-currency">Nominal Macet</th>
                <th class="text-center" style="width: 105px;">Rincian</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="6" class="text-center" id="grandTotalLabelMacet">Grand Total</th>
                <th class="text-right col-currency ttl_macet_spk">0</th>
                <th class="text-right col-currency ttl_macet_invoice">0</th>
                <th class="text-right col-currency ttl_macet_nominal" style="color: #fca5a5;">0</th>
                <th class="text-center">-</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Modal Detail Termin Macet -->
<div class="modal fade" id="modal_detail_macet" tabindex="-1" role="dialog" aria-labelledby="modalDetailMacetLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width: 85%; max-width: 1000px;">
    <div class="modal-content" style="border-radius: 8px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
      <div class="modal-header" style="background: #b91c1c; color: #ffffff; padding: 14px 20px;">
        <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.9;"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalDetailMacetLabel" style="font-weight: 600; font-size: 16px;">
          <i class="fa fa-exclamation-circle"></i> Rincian Termin Tagihan Macet
        </h4>
      </div>
      <div class="modal-body" style="padding: 20px; background: #fafbfc;">
        <!-- Ringkasan Info SPK -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 18px; margin-bottom: 18px;">
          <div class="row">
            <div class="col-md-6">
              <table class="table table-condensed" style="margin-bottom: 0; font-size: 13px;">
                <tr>
                  <td style="width: 120px; font-weight: 600; color: #64748b; border: none;">No. SPK</td>
                  <td style="border: none;">: <strong id="mdl_no_spk" class="text-primary">-</strong></td>
                </tr>
                <tr>
                  <td style="font-weight: 600; color: #64748b; border: none;">Customer</td>
                  <td style="border: none;">: <span id="mdl_customer">-</span></td>
                </tr>
                <tr>
                  <td style="font-weight: 600; color: #64748b; border: none;">Company</td>
                  <td style="border: none;">: <span id="mdl_company">-</span></td>
                </tr>
              </table>
            </div>
            <div class="col-md-6">
              <table class="table table-condensed" style="margin-bottom: 0; font-size: 13px;">
                <tr>
                  <td style="width: 120px; font-weight: 600; color: #64748b; border: none;">Project</td>
                  <td style="border: none;">: <span id="mdl_project">-</span></td>
                </tr>
                <tr>
                  <td style="font-weight: 600; color: #64748b; border: none;">Nilai Kontrak</td>
                  <td style="border: none;">: Rp <span id="mdl_nilai_spk">0</span></td>
                </tr>
                <tr>
                  <td style="font-weight: 600; color: #64748b; border: none;">Total Macet</td>
                  <td style="border: none;">: <strong id="mdl_total_macet" style="color: #b91c1c; font-size: 15px;">Rp 0</strong></td>
                </tr>
              </table>
            </div>
          </div>
        </div>

        <!-- Tabel Rincian Termin -->
        <div style="border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; background: #fff;">
          <table class="table table-striped table-bordered" style="margin-bottom: 0; font-size: 12.5px;">
            <thead>
              <tr style="background: #f1f5f9; color: #334155;">
                <th class="text-center" style="width: 45px;">No.</th>
                <th>Termin</th>
                <th class="text-center" style="width: 110px;">Tgl Tagih</th>
                <th class="text-center" style="width: 90px;">Bobot (%)</th>
                <th class="text-right" style="width: 140px;">Nominal Macet</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody id="mdl_tbody_termin">
              <tr>
                <td colspan="6" class="text-center" style="padding: 20px; color: #64748b;">
                  Memuat data...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0;">
        <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: 500;">
          <i class="fa fa-times"></i> Tutup
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="width: 1200px;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span> Posting Jurnal</h4>
      </div>
      <form action="" method="post" id="frm-data">
        <div class="modal-body" id="ModalView"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">
            <span class="glyphicon glyphicon-remove"></span> Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div id="form-data"></div>

<!-- Scripts -->
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type="text/javascript">
  var is_macet_loaded = false;

  $(document).ready(function() {
    var tahun = $('select[name="tahun"]').val();
    autoNum();
    init_select2();
    datatables('', '', tahun);

    // Tab switch handler
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
      var target = $(e.target).attr("href");
      if (target === '#tab_macet') {
        if (!is_macet_loaded) {
          init_select2_macet();
          datatables_macet('', '');
          is_macet_loaded = true;
        } else {
          if ($.fn.DataTable.isDataTable('#table_macet')) {
            $('#table_macet').DataTable().columns.adjust().draw(false);
          }
        }
      } else if (target === '#tab_annual') {
        if ($.fn.DataTable.isDataTable('#table_penawaran')) {
          $('#table_penawaran').DataTable().columns.adjust().draw(false);
        }
      }
    });
  });

  function init_select2() {
    $('.select2').select2({
      width: '100%'
    });
  }

  function init_select2_macet() {
    $('.select2-macet').select2({
      width: '100%'
    });
  }

  function autoNum() {
    if ($.fn.autoNumeric) {
      $('.autonum').autoNumeric('init');
    }
  }

  function format_rupiah(num) {
    if (num === null || num === undefined || isNaN(num) || num === '') {
      return '0';
    }
    return Number(num).toLocaleString('id-ID');
  }

  // --- TAB 1 (ANNUAL) FUNCTIONS ---
  function reset() {
    $('select[name="client"]').val('').trigger('change');
    $('select[name="company"]').val('').trigger('change');
    $('select[name="tahun"]').val('<?= date('Y') ?>').trigger('change');

    datatables('', '', '<?= date('Y') ?>');
  }

  function search_data() {
    var client = $('select[name="client"]').val();
    var company = $('select[name="company"]').val();
    var tahun = $('select[name="tahun"]').val();

    datatables(client, company, tahun);
  }

  function download_excel() {
    var client = $('select[name="client"]').val();
    var company = $('select[name="company"]').val();
    var tahun = $('select[name="tahun"]').val();

    if (!tahun || tahun.length <= 1) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'warning',
          title: 'Warning !',
          text: 'Tahun harus diisi !',
          timer: 3000
        });
      } else {
        alert('Tahun harus diisi !');
      }
      return false;
    }

    window.open(siteurl + active_controller + 'download_excel?client=' + client + '&company=' + company + '&tahun=' + tahun);
  }

  function datatables(client, company, tahun) {
    client = (typeof client !== 'undefined') ? client : '';
    company = (typeof company !== 'undefined') ? company : '';
    tahun = (typeof tahun !== 'undefined' && tahun !== '') ? tahun : $('select[name="tahun"]').val();

    $('#table_penawaran').DataTable({
      serverSide: true,
      processing: true,
      destroy: true,
      paging: true,
      lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
      ajax: {
        type: 'get',
        url: siteurl + active_controller + 'get_data_report_apt',
        cache: false,
        dataType: 'json',
        data: function(d) {
          d.client = client;
          d.company = company;
          d.tahun = tahun;
        },
        error: function(xhr, status, error) {
          console.error(error);
        }
      },
      autoWidth: false,
      columns: [
        { data: 'no', className: 'text-center col-no', orderable: false, width: '45px' },
        { data: 'company', className: 'col-company', width: '120px' },
        { data: 'customer', className: 'col-customer', width: '170px' },
        { data: 'no_spk', className: 'col-spk', width: '140px' },
        { data: 'consultant', className: 'col-consultant', width: '140px' },
        { 
          data: 'project', 
          className: 'col-project', 
          width: '240px',
          render: function(data, type, row) {
            if (!data) return '-';
            var clean = $('<div>').text(data).html();
            return '<span class="project-cell" title="' + clean + '">' + clean + '</span>';
          }
        },
        { data: 'nominal_spk', className: 'text-right col-currency', width: '115px' },
        { data: 'nominal_invoice', className: 'text-right col-currency', width: '115px' },
        { data: 'nominal_uninvoice', className: 'text-right col-currency', width: '120px' },
        { data: 'macet', className: 'text-right col-currency', width: '95px' },
        { data: 'jan', className: 'text-right col-month', width: '85px' },
        { data: 'feb', className: 'text-right col-month', width: '85px' },
        { data: 'mar', className: 'text-right col-month', width: '85px' },
        { data: 'apr', className: 'text-right col-month', width: '85px' },
        { data: 'may', className: 'text-right col-month', width: '85px' },
        { data: 'jun', className: 'text-right col-month', width: '85px' },
        { data: 'jul', className: 'text-right col-month', width: '85px' },
        { data: 'aug', className: 'text-right col-month', width: '85px' },
        { data: 'sep', className: 'text-right col-month', width: '85px' },
        { data: 'oct', className: 'text-right col-month', width: '85px' },
        { data: 'nov', className: 'text-right col-month', width: '85px' },
        { data: 'dec', className: 'text-right col-month', width: '85px' }
      ],
      drawCallback: function(settings) {
        var response = settings.json;
        if (response) {
          // Update top 4 Summary Cards
          $('#sumSpk').html(format_rupiah(response.total_nominal_spk));
          $('#sumInvoice').html(format_rupiah(response.total_invoice));
          $('#sumUninvoiced').html(format_rupiah(response.total_uninvoice));
          $('#sumMacet').html(format_rupiah(response.total_macet));

          // Update Footer Total
          var totalItems = (response.recordsFiltered !== undefined) ? response.recordsFiltered : 0;
          $('#grandTotalLabel').html('Grand Total (' + totalItems + ' item)');
          $('.ttl_nominal_spk').html(format_rupiah(response.total_nominal_spk));
          $('.ttl_nominal_invoice').html(format_rupiah(response.total_invoice));
          $('.ttl_nominal_uninvoice').html(format_rupiah(response.total_uninvoice));
          $('.ttl_macet').html(format_rupiah(response.total_macet));

          var bulan = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
          for (var i = 0; i < bulan.length; i++) {
            var m = bulan[i];
            $('.ttl_' + m).html(format_rupiah(response['total_' + m]));
          }
        }
      }
    });
  }

  // --- TAB 2 (MACET ALL-TIME) FUNCTIONS ---
  function reset_macet() {
    $('select[name="client_macet"]').val('').trigger('change');
    $('select[name="company_macet"]').val('').trigger('change');
    datatables_macet('', '');
  }

  function search_data_macet() {
    var client = $('select[name="client_macet"]').val();
    var company = $('select[name="company_macet"]').val();
    datatables_macet(client, company);
  }

  function download_excel_macet() {
    var client = $('select[name="client_macet"]').val() || '';
    var company = $('select[name="company_macet"]').val() || '';
    window.open(siteurl + active_controller + 'download_excel_macet?client=' + client + '&company=' + company);
  }

  function datatables_macet(client, company) {
    client = (typeof client !== 'undefined') ? client : '';
    company = (typeof company !== 'undefined') ? company : '';

    $('#table_macet').DataTable({
      serverSide: true,
      processing: true,
      destroy: true,
      paging: true,
      lengthMenu: [10, 25, 50, 100, 250],
      ajax: {
        type: 'get',
        url: siteurl + active_controller + 'get_data_report_macet',
        cache: false,
        dataType: 'json',
        data: function(d) {
          d.client = client;
          d.company = company;
        },
        error: function(xhr, status, error) {
          console.error(error);
        }
      },
      autoWidth: false,
      columns: [
        { data: 'no', className: 'text-center col-no', orderable: false, width: '45px' },
        { data: 'company', className: 'col-company', width: '120px' },
        { 
          data: 'customer', 
          className: 'col-customer', 
          width: '180px',
          render: function(data) {
            if (!data) return '-';
            var clean = $('<div>').text(data).html();
            return '<span class="project-cell" title="' + clean + '">' + clean + '</span>';
          }
        },
        { data: 'no_spk', className: 'col-spk', width: '140px' },
        { 
          data: 'consultant', 
          className: 'col-consultant', 
          width: '140px',
          render: function(data) {
            if (!data) return '-';
            var clean = $('<div>').text(data).html();
            return '<span class="project-cell" title="' + clean + '">' + clean + '</span>';
          }
        },
        { 
          data: 'project', 
          className: 'col-project', 
          width: '240px',
          render: function(data) {
            if (!data) return '-';
            var clean = $('<div>').text(data).html();
            return '<span class="project-cell" title="' + clean + '">' + clean + '</span>';
          }
        },
        { data: 'nominal_spk', className: 'text-right col-currency', width: '115px' },
        { data: 'nominal_invoice', className: 'text-right col-currency', width: '115px' },
        { 
          data: 'nominal_macet', 
          className: 'text-right col-currency', 
          width: '120px',
          render: function(data) {
            return '<strong style="color: #c0392b;">' + data + '</strong>';
          }
        },
        {
          data: null,
          className: 'text-center',
          orderable: false,
          width: '105px',
          render: function(data, type, row) {
            var escapedSpk = encodeURIComponent(row.no_spk);
            return '<button type="button" class="btn-termin-badge" onclick="show_detail_termin(\'' + escapedSpk + '\')" title="Klik untuk melihat rincian termin"><i class="fa fa-list-ol"></i> ' + row.jml_termin_macet + ' Termin</button>';
          }
        }
      ],
      drawCallback: function(settings) {
        var response = settings.json;
        if (response) {
          $('#sumSpkMacet').html(response.total_spk_macet + ' <span style="font-size:12px; font-weight:normal; color:#6b7684;">SPK</span>');
          $('#sumNominalSpkMacet').html(format_rupiah(response.total_nilai_spk));
          $('#sumInvoiceMacet').html(format_rupiah(response.total_invoice));
          $('#sumTotalMacet').html(format_rupiah(response.total_macet));

          var totalFiltered = (response.recordsFiltered !== undefined) ? response.recordsFiltered : 0;
          $('#grandTotalLabelMacet').html('Grand Total (' + totalFiltered + ' SPK)');
          $('.ttl_macet_spk').html(format_rupiah(response.total_nilai_spk));
          $('.ttl_macet_invoice').html(format_rupiah(response.total_invoice));
          $('.ttl_macet_nominal').html(format_rupiah(response.total_macet));
        }
      }
    });
  }

  function show_detail_termin(encodedSpk) {
    var spk = decodeURIComponent(encodedSpk);
    $('#modal_detail_macet').modal('show');
    $('#mdl_tbody_termin').html('<tr><td colspan="6" class="text-center" style="padding: 20px;"><i class="fa fa-spinner fa-spin"></i> Memuat data rincian termin...</td></tr>');
    $('#mdl_no_spk').text(spk);
    $('#mdl_customer').text('-');
    $('#mdl_company').text('-');
    $('#mdl_project').text('-');
    $('#mdl_nilai_spk').text('0');
    $('#mdl_total_macet').text('Rp 0');

    $.ajax({
      type: 'GET',
      url: siteurl + active_controller + 'get_detail_termin_macet',
      data: { id_spk_penawaran: spk },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1) {
          var spkData = res.spk || {};
          $('#mdl_customer').text(spkData.nm_customer || '-');
          $('#mdl_company').text(spkData.nm_company || '-');
          $('#mdl_project').text(spkData.nm_paket || '-');
          $('#mdl_nilai_spk').text(format_rupiah(spkData.nilai_kontrak || 0));
          $('#mdl_total_macet').text('Rp ' + res.total_macet_spk);

          var html = '';
          if (res.termin && res.termin.length > 0) {
            $.each(res.termin, function(idx, item) {
              html += '<tr>' +
                '<td class="text-center">' + (idx + 1) + '</td>' +
                '<td><strong>' + item.term_payment + '</strong></td>' +
                '<td class="text-center">' + item.tgl_tagih + '</td>' +
                '<td class="text-center">' + item.persen_payment + '</td>' +
                '<td class="text-right"><strong style="color: #dc2626;">Rp ' + item.nominal_payment + '</strong></td>' +
                '<td>' + item.desc_payment + '</td>' +
              '</tr>';
            });
          } else {
            html = '<tr><td colspan="6" class="text-center" style="padding: 20px; color: #64748b;">Tidak ada rincian termin macet.</td></tr>';
          }
          $('#mdl_tbody_termin').html(html);
        } else {
          $('#mdl_tbody_termin').html('<tr><td colspan="6" class="text-center text-danger" style="padding: 20px;">' + (res.message || 'Gagal memuat rincian termin.') + '</td></tr>');
        }
      },
      error: function() {
        $('#mdl_tbody_termin').html('<tr><td colspan="6" class="text-center text-danger" style="padding: 20px;">Terjadi kesalahan server saat memuat detail termin.</td></tr>');
      }
    });
  }
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>