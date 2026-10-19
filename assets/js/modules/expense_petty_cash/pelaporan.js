/**
 * Expense Petty Cash - Pelaporan (Index Page)
 *
 * DataTables initialization, filter handling, submit pelaporan action.
 */
$(document).ready(function () {
  // =========================================================================
  // Helper: Format date to "dd Mon YYYY"
  // =========================================================================

  var MONTH_NAMES = [
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "Mei",
    "Jun",
    "Jul",
    "Agu",
    "Sep",
    "Okt",
    "Nov",
    "Des",
  ];

  /**
   * Format date string (YYYY-MM-DD) to "dd Mon YYYY"
   */
  function formatDateIndo(dateStr) {
    if (!dateStr) return "-";
    var parts = dateStr.split("-");
    if (parts.length !== 3) return dateStr;
    var day = parseInt(parts[2], 10);
    var monthIdx = parseInt(parts[1], 10) - 1;
    var year = parts[0];
    if (monthIdx < 0 || monthIdx > 11) return dateStr;
    return (
      (day < 10 ? "0" + day : day) + " " + MONTH_NAMES[monthIdx] + " " + year
    );
  }

  // =========================================================================
  // DataTables Initialization
  // =========================================================================

  var table = $("#table-pelaporan").DataTable({
    processing: true,
    serverSide: true,
    destroy: true,
    responsive: true,
    oLanguage: {
<<<<<<< HEAD
      sSearch: "<b>Search : </b>",
      sLengthMenu: "_MENU_ &nbsp;&nbsp;<b>Records Per Page</b>&nbsp;&nbsp;",
      sInfo: "Showing _START_ to _END_ of _TOTAL_ entries",
      sInfoFiltered: "(filtered from _MAX_ total entries)",
      sZeroRecords: "No matching records found",
      sEmptyTable: "No data available in table",
      sLoadingRecords: "Please wait - loading...",
      oPaginate: {
        sPrevious: "Prev",
        sNext: "Next",
=======
      sSearch: "Cari pelaporan:",
      sLengthMenu: "Tampilkan _MENU_ per halaman",
      sInfo: "Menampilkan _START_–_END_ dari _TOTAL_ pelaporan",
      sInfoEmpty: "Menampilkan 0 pelaporan",
      sInfoFiltered: "(dari _MAX_ pelaporan keseluruhan)",
      sZeroRecords: "Tidak ada pelaporan yang cocok. Ubah pencarian atau reset filter.",
      sEmptyTable: "Belum ada pelaporan untuk ditampilkan.",
      sLoadingRecords: "Memuat daftar pelaporan...",
      sProcessing: "Memuat daftar pelaporan...",
      oPaginate: {
        sPrevious: "Sebelumnya",
        sNext: "Berikutnya",
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
      },
    },
    aaSorting: [[1, "desc"]],
    columnDefs: [{ targets: [0, 7], orderable: false, searchable: false }],
    iDisplayLength: 10,
    aLengthMenu: [
      [10, 20, 50, 100],
      [10, 20, 50, 100],
    ],
    ajax: {
      url: BASE_URL + "get_data_pelaporan",
      type: "POST",
      cache: false,
      data: function (d) {
        d.filter_company = $("#filter-company").val();
        d.filter_status = $("#filter-status").val();
      },
      error: function () {
        $("#table-pelaporan tbody").html(
<<<<<<< HEAD
          '<tr><th colspan="8" class="text-center">No data found in the server</th></tr>',
=======
          '<tr><td colspan="8" class="text-center">Daftar pelaporan gagal dimuat. Klik Terapkan filter untuk mencoba kembali.</td></tr>',
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
        );
      },
    },
    columns: [
      {
        // Column 0: Row number
        data: "no",
      },
      {
        // Column 1: No Pelaporan
        data: "no_pelaporan",
      },
      {
        // Column 2: Periode (format "dd Mon YYYY - dd Mon YYYY")
        data: null,
        render: function (data, type, row) {
          return (
            formatDateIndo(row.periode_start) +
            " - " +
            formatDateIndo(row.periode_end)
          );
        },
      },
      {
        // Column 3: Company
        data: "company",
      },
      {
        // Column 4: Jumlah Pencatatan
        data: "jumlah_pencatatan",
      },
      {
        // Column 5: Grand Total Periode
        data: "grand_total",
      },
      {
        // Column 6: Status (colored label)
        data: "status",
<<<<<<< HEAD
        render: function (data) {
=======
        render: function (data, type, row) {
          if (type !== "display") return data;
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
          var labelClass = "label-default";
          var labelText = data;

          switch (data) {
            case "draft":
              labelClass = "label-default";
              labelText = "Draft";
              break;
            case "waiting":
              labelClass = "label-warning";
              labelText = "Waiting";
              break;
            case "approved":
              labelClass = "label-success";
              labelText = "Approved";
              break;
            case "reject":
              labelClass = "label-danger";
              labelText = "Reject";
              break;
          }

<<<<<<< HEAD
          return (
            '<span class="label ' + labelClass + '">' + labelText + "</span>"
          );
=======
          var html = (
            '<span class="label ' + labelClass + '">' + $("<div>").text(labelText).html() + "</span>"
          );
          if (type === "display" && data === "reject" && row.alasan_reject) {
            html += '<div class="pelaporan-reject-reason"><strong>Alasan penolakan</strong>' +
              $("<div>").text(row.alasan_reject).html() + "</div>";
          }
          return html;
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
        },
      },
      {
        // Column 7: Action buttons
        data: null,
        render: function (data, type, row) {
<<<<<<< HEAD
          var html = "";
=======
          if (type !== "display") return "";
          var html = '<div class="pelaporan-actions">';
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47

          // Ajukan button — only if draft + has manage permission
          if (PERMISSIONS.has_manage && row.status === "draft") {
            html +=
              '<button type="button" class="btn btn-primary btn-xs btn-ajukan" data-id="' +
              row.id +
              '" title="Ajukan Pelaporan">' +
              '<i class="fa fa-send"></i> Ajukan</button> ';
          }

          // View button — always shown
          html +=
            '<a href="' +
            BASE_URL +
            "view_pelaporan/" +
            row.id +
<<<<<<< HEAD
            '" class="btn btn-info btn-xs" title="View">' +
            '<i class="fa fa-eye"></i></a> ';
=======
            '" class="btn btn-info btn-xs" title="Lihat detail pelaporan">' +
            '<i class="fa fa-eye" aria-hidden="true"></i> Detail</a> ';
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47

          // Print button — always shown, opens in new tab
          html +=
            '<a href="' +
            BASE_URL +
            "print_pelaporan/" +
            row.id +
<<<<<<< HEAD
            '" target="_blank" class="btn btn-default btn-xs" title="Print">' +
            '<i class="fa fa-print"></i></a> ';

          return html;
=======
            '" target="_blank" rel="noopener" class="btn btn-default btn-xs" title="Cetak pelaporan (tab baru)">' +
            '<i class="fa fa-print" aria-hidden="true"></i> Cetak</a> ';

          return html + "</div>";
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
        },
      },
    ],
  });

  // =========================================================================
  // Filter Handling
  // =========================================================================

  // Filter button click — reload table with filter values
  $("#btn-filter").on("click", function () {
<<<<<<< HEAD
    table.ajax.reload(null, false);
=======
    table.ajax.reload(null, true);
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
  });

  // Reset filter button — clear dropdowns and reload
  $("#btn-reset-filter").on("click", function () {
    $("#filter-company").val("");
    $("#filter-status").val("");
<<<<<<< HEAD
    table.ajax.reload(null, false);
=======
    table.ajax.reload(null, true);
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
  });

  // Filter on dropdown change — immediate reload without page refresh
  $("#filter-company, #filter-status").on("change", function () {
<<<<<<< HEAD
    table.ajax.reload(null, false);
=======
    table.ajax.reload(null, true);
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
  });

  // =========================================================================
  // Ajukan Pelaporan (Submit: draft → waiting)
  // =========================================================================

  $(document).on("click", ".btn-ajukan", function () {
    var id = $(this).data("id");
    // Redirect ke halaman konfirmasi (review detail sebelum ajukan)
    window.location.href = BASE_URL + "confirm_pelaporan/" + id;
  });
});
