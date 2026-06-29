/**
 * Expense Petty Cash - Pencatatan (Index Page)
 *
 * DataTables initialization, checkbox handling, create pelaporan trigger,
 * delete confirmation, and retry journal handler.
 */
$(document).ready(function () {
  // =========================================================================
  // DataTables Initialization
  // =========================================================================

  var table = $("#table-pencatatan").DataTable({
    processing: true,
    serverSide: true,
    destroy: true,
    responsive: true,
    oLanguage: {
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
      },
    },
    aaSorting: [[3, "desc"]],
    columnDefs: [{ targets: [0, 9], orderable: false, searchable: false }],
    iDisplayLength: 10,
    aLengthMenu: [
      [10, 20, 50, 100],
      [10, 20, 50, 100],
    ],
    ajax: {
      url: BASE_URL + "get_data_pencatatan",
      type: "POST",
      cache: false,
      error: function () {
        $("#table-pencatatan tbody").html(
          '<tr><th colspan="10" class="text-center">No data found in the server</th></tr>',
        );
      },
    },
    columns: [
      {
        // Column 0: Checkbox
        data: null,
        render: function (data, type, row) {
          if (row.status === "draft" && !row.in_pelaporan) {
            return (
              '<input type="checkbox" class="check-item" ' +
              'data-id="' +
              row.id +
              '" ' +
              'data-company="' +
              row.company +
              '" ' +
              'data-tanggal="' +
              row.tanggal +
              '">'
            );
          }
          return '<input type="checkbox" disabled>';
        },
      },
      {
        // Column 1: Row number
        data: "no",
      },
      {
        // Column 2: No Pencatatan + journal status indicator
        data: null,
        render: function (data, type, row) {
          var html = row.no_pencatatan;
          if (row.journal_status === "failed") {
            html +=
              ' <i class="fa fa-exclamation-triangle text-warning" title="Jurnal gagal disinkronisasi"></i>';
          }
          return html;
        },
      },
      {
        // Column 3: Tanggal (formatted DD/MM/YYYY)
        data: "tanggal",
        render: function (data) {
          if (!data) return "-";
          // Expect data as YYYY-MM-DD from server, format to DD/MM/YYYY
          var parts = data.split("-");
          if (parts.length === 3) {
            return parts[2] + "/" + parts[1] + "/" + parts[0];
          }
          return data;
        },
      },
      {
        // Column 4: Company
        data: "company",
      },
      {
        // Column 5: Request By
        data: "request_by",
      },
      {
        // Column 6: Keterangan (truncate 50 chars)
        data: "keterangan",
        render: function (data) {
          if (!data) return "-";
          if (data.length > 50) {
            return (
              '<span title="' +
              data.replace(/"/g, "&quot;") +
              '">' +
              data.substring(0, 50) +
              "..." +
              "</span>"
            );
          }
          return data;
        },
      },
      {
        // Column 7: Grand Total (already formatted from server)
        data: "grand_total",
      },
      {
        // Column 8: Status (colored label)
        data: "status",
        render: function (data) {
          var labelClass = "label-default";
          var labelText = data;

          switch (data) {
            case "draft":
              labelClass = "label-default";
              labelText = "Draft";
              break;
            case "waiting approval":
              labelClass = "label-warning";
              labelText = "Waiting Approval";
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

          return (
            '<span class="label ' + labelClass + '">' + labelText + "</span>"
          );
        },
      },
      {
        // Column 9: Action buttons
        data: null,
        render: function (data, type, row) {
          var html = "";

          // View button — always shown
          html +=
            '<a href="' +
            BASE_URL +
            "view/" +
            row.id +
            '" class="btn btn-info btn-xs" title="View">' +
            '<i class="fa fa-eye"></i></a> ';

          // Edit button — manage permission + status draft/reject + NOT in pelaporan
          if (
            PERMISSIONS.has_manage &&
            (row.status === "draft" || row.status === "reject") &&
            !row.in_pelaporan
          ) {
            html +=
              '<a href="' +
              BASE_URL +
              "edit/" +
              row.id +
              '" class="btn btn-warning btn-xs" title="Edit">' +
              '<i class="fa fa-pencil"></i></a> ';
          }

          // Delete button — delete permission + status draft/reject + NOT in pelaporan
          if (
            PERMISSIONS.has_delete &&
            (row.status === "draft" || row.status === "reject") &&
            !row.in_pelaporan
          ) {
            html +=
              '<button type="button" class="btn btn-danger btn-xs btn-delete" data-id="' +
              row.id +
              '" title="Delete">' +
              '<i class="fa fa-trash"></i></button> ';
          }

          // Retry journal button — manage permission + journal_status failed
          if (PERMISSIONS.has_manage && row.journal_status === "failed") {
            html +=
              '<button type="button" class="btn btn-default btn-xs btn-retry-journal" data-id="' +
              row.id +
              '" title="Retry Jurnal">' +
              '<i class="fa fa-refresh"></i></button> ';
          }

          return html;
        },
      },
    ],
    drawCallback: function () {
      // Reset check-all state after table redraw
      $("#check-all").prop("checked", false);
      updateBuatPelaporanButton();
    },
  });

  // =========================================================================
  // Checkbox Logic
  // =========================================================================

  // Check-all header checkbox: toggle all visible checkboxes
  $("#check-all").on("change", function () {
    var isChecked = $(this).is(":checked");
    $(".check-item:not(:disabled)").prop("checked", isChecked);
    updateBuatPelaporanButton();
  });

  // Individual checkbox change
  $(document).on("change", ".check-item", function () {
    // Update check-all state
    var totalCheckboxes = $(".check-item:not(:disabled)").length;
    var checkedCheckboxes = $(".check-item:checked").length;
    $("#check-all").prop(
      "checked",
      totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes,
    );
    updateBuatPelaporanButton();
  });

  /**
   * Enable/disable "Buat Pelaporan" button based on checkbox state
   */
  function updateBuatPelaporanButton() {
    var checkedCount = $(".check-item:checked").length;
    $("#btn-buat-pelaporan").prop("disabled", checkedCount === 0);
  }

  // =========================================================================
  // Buat Pelaporan
  // =========================================================================

  $("#btn-buat-pelaporan").on("click", function () {
    var selectedItems = [];

    $(".check-item:checked").each(function () {
      selectedItems.push({
        id: $(this).data("id"),
        company: $(this).data("company"),
        tanggal: $(this).data("tanggal"),
      });
    });

    // Validasi: minimal 1 pencatatan dipilih
    if (selectedItems.length === 0) {
      Swal.fire({
        icon: "warning",
        title: "Peringatan",
        text: "Silakan pilih minimal 1 pencatatan untuk membuat pelaporan.",
        allowOutsideClick: false,
      });
      return;
    }

    // Validasi: same company
    var companies = [];
    $.each(selectedItems, function (i, item) {
      if (companies.indexOf(item.company) === -1) {
        companies.push(item.company);
      }
    });

    if (companies.length > 1) {
      Swal.fire({
        icon: "error",
        title: "Validasi Gagal",
        text: "Pelaporan hanya dapat berisi pencatatan dari satu company yang sama.",
        allowOutsideClick: false,
      });
      return;
    }

    // Collect pencatatan IDs
    var pencatatanIds = [];
    $.each(selectedItems, function (i, item) {
      pencatatanIds.push(item.id);
    });

    // POST to create_pelaporan — server handles same-week validation
    Swal.fire({
      icon: "question",
      title: "Buat Pelaporan?",
      text:
        "Anda akan membuat pelaporan dari " +
        selectedItems.length +
        " pencatatan terpilih.",
      showCancelButton: true,
      confirmButtonText: "Ya, Buat",
      cancelButtonText: "Batal",
      allowOutsideClick: false,
    }).then(function (result) {
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: BASE_URL + "create_pelaporan",
          data: { pencatatan_ids: pencatatanIds },
          dataType: "json",
          cache: false,
          success: function (response) {
            if (response.status == "1") {
              Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: response.message || "Pelaporan berhasil dibuat.",
                timer: 3000,
                allowOutsideClick: false,
              }).then(function () {
                // Redirect to pelaporan page or reload
                if (response.redirect) {
                  window.location.href = response.redirect;
                } else {
                  table.ajax.reload(null, false);
                }
              });
            } else {
              Swal.fire({
                icon: "error",
                title: "Gagal",
                text: response.message || "Gagal membuat pelaporan.",
                allowOutsideClick: false,
              });
            }
          },
          error: function () {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "Terjadi kesalahan saat memproses data. Silakan coba lagi.",
              timer: 3000,
              allowOutsideClick: false,
            });
          },
        });
      }
    });
  });

  // =========================================================================
  // Delete Pencatatan
  // =========================================================================

  $(document).on("click", ".btn-delete", function () {
    var id = $(this).data("id");

    Swal.fire({
      icon: "warning",
      title: "Anda Yakin?",
      text: "Data pencatatan akan dihapus secara permanen.",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      confirmButtonText: "Ya, Hapus!",
      cancelButtonText: "Batal",
      allowOutsideClick: false,
    }).then(function (result) {
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: BASE_URL + "delete/" + id,
          dataType: "json",
          cache: false,
          success: function (response) {
            if (response.status == "1") {
              Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: response.message || "Data berhasil dihapus.",
                timer: 2000,
                allowOutsideClick: false,
              }).then(function () {
                table.ajax.reload(null, false);
              });
            } else {
              Swal.fire({
                icon: "error",
                title: "Gagal",
                text: response.message || "Gagal menghapus data.",
                allowOutsideClick: false,
              });
            }
          },
          error: function () {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "Terjadi kesalahan saat menghapus data. Silakan coba lagi.",
              timer: 3000,
              allowOutsideClick: false,
            });
          },
        });
      }
    });
  });

  // =========================================================================
  // Retry Journal
  // =========================================================================

  $(document).on("click", ".btn-retry-journal", function () {
    var id = $(this).data("id");
    var $btn = $(this);

    Swal.fire({
      icon: "question",
      title: "Retry Jurnal?",
      text: "Sistem akan mencoba menyinkronisasi ulang jurnal untuk pencatatan ini.",
      showCancelButton: true,
      confirmButtonText: "Ya, Retry",
      cancelButtonText: "Batal",
      allowOutsideClick: false,
    }).then(function (result) {
      if (result.isConfirmed) {
        // Disable button during processing
        $btn
          .prop("disabled", true)
          .html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
          type: "POST",
          url: BASE_URL + "retry_journal/" + id,
          dataType: "json",
          cache: false,
          success: function (response) {
            $btn.prop("disabled", false).html('<i class="fa fa-refresh"></i>');

            if (response.status == "1") {
              Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: response.message || "Jurnal berhasil disinkronisasi.",
                timer: 2000,
                allowOutsideClick: false,
              }).then(function () {
                table.ajax.reload(null, false);
              });
            } else {
              Swal.fire({
                icon: "error",
                title: "Gagal",
                text:
                  response.message ||
                  "Jurnal gagal disinkronisasi. Silakan coba lagi nanti.",
                allowOutsideClick: false,
              });
            }
          },
          error: function () {
            $btn.prop("disabled", false).html('<i class="fa fa-refresh"></i>');

            Swal.fire({
              icon: "error",
              title: "Error",
              text: "Terjadi kesalahan koneksi. Silakan coba lagi.",
              timer: 3000,
              allowOutsideClick: false,
            });
          },
        });
      }
    });
  });
});
