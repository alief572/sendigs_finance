/**
 * Expense Petty Cash - Approval
 *
 * DataTables initialization for approval index page,
 * Approve/reject dialogs with SweetAlert2 on approval view page.
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
  // DataTables Initialization (Approval Index Page)
  // =========================================================================

  if ($("#table-approval").length) {
    var table = $("#table-approval").DataTable({
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
      aaSorting: [[1, "asc"]],
      columnDefs: [{ targets: [0, 6, 7], orderable: false, searchable: false }],
      createdRow: function (tr, row) {
        // Selisih saldo: sorot baris agar Dept Head Finance mengecek (PC-R-05)
        if (row.selisih_saldo !== null && Number(row.selisih_saldo) !== 0) {
          $(tr).css("background-color", "#fff8e1");
        }
      },
      iDisplayLength: 10,
      aLengthMenu: [
        [10, 20, 50, 100],
        [10, 20, 50, 100],
      ],
      ajax: {
        url: BASE_URL + "get_data_approval",
        type: "POST",
        cache: false,
        error: function () {
          $("#table-approval tbody").html(
            '<tr><th colspan="8" class="text-center">No data found in the server</th></tr>',
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
          className: "text-center",
        },
        {
          // Column 5: Grand Total
          data: "grand_total",
          className: "text-right",
        },
        {
          // Column 6: Kontrol Saldo (selisih hitung fisik vs saldo seharusnya)
          data: "selisih_saldo",
          className: "text-center",
          render: function (data, type) {
            if (type !== "display") return data;
            if (data === null || data === undefined) return "-";
            var selisih = Number(data);
            if (selisih === 0) {
              return '<span class="label label-success">Sesuai</span>';
            }
            return (
              '<span class="label label-warning">Selisih ' +
              (selisih < 0 ? "-" : "+") +
              Math.abs(selisih)
                .toString()
                .replace(/\B(?=(\d{3})+(?!\d))/g, ".") +
              "</span><br><small>Perlu dicek</small>"
            );
          },
        },
        {
          // Column 7: Action button
          data: null,
          className: "text-center",
          render: function (data, type, row) {
            return (
              '<a href="' +
              BASE_URL +
              "view_approval/" +
              row.id +
              '" class="btn btn-success btn-xs" title="Approval">' +
              '<i class="fa fa-check"></i> Approval</a>'
            );
          },
        },
      ],
    });
  }

  // =========================================================================
  // Approve Button Handler (Approval View Page)
  // =========================================================================

  $(document).on("click", ".btn-approve", function () {
    var id =
      typeof PELAPORAN_ID !== "undefined" ? PELAPORAN_ID : $(this).data("id");

    // Selisih saldo hanya informasi: tetap bisa approve, tetapi approver diingatkan (PC-R-05)
    var adaSelisih =
      typeof SELISIH_SALDO !== "undefined" &&
      SELISIH_SALDO !== null &&
      Number(SELISIH_SALDO) !== 0;
    var peringatanSelisih = adaSelisih
      ? '<div class="alert alert-warning" style="text-align:left; margin-top:10px;"><i class="fa fa-warning"></i> Ada selisih saldo <strong>' +
        (Number(SELISIH_SALDO) < 0 ? "-" : "+") +
        "Rp " +
        Math.abs(Number(SELISIH_SALDO))
          .toString()
          .replace(/\B(?=(\d{3})+(?!\d))/g, ".") +
        "</strong>. Pastikan Dept Head Finance sudah mengecek selisih ini.</div>"
      : "";

    Swal.fire({
      icon: "question",
      title: "Approve Pelaporan?",
      html:
        "<p>Apakah Anda yakin ingin menyetujui pelaporan ini?</p>" +
        peringatanSelisih,
      showCancelButton: true,
      confirmButtonColor: "#28a745",
      confirmButtonText: "Ya, Approve",
      cancelButtonText: "Batal",
      allowOutsideClick: false,
    }).then(function (result) {
      if (result.isConfirmed) {
        // Show loading state
        Swal.fire({
          title: "Memproses...",
          text: "Mohon tunggu",
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: function () {
            Swal.showLoading();
          },
        });

        $.ajax({
          type: "POST",
          url: BASE_URL + "approve/" + id,
          dataType: "json",
          cache: false,
          success: function (response) {
            if (response.status === true || response.status == "1") {
              Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: response.message || "Pelaporan berhasil diapprove.",
                timer: 3000,
                allowOutsideClick: false,
              }).then(function () {
                window.location.href = BASE_URL + "approval";
              });
            } else {
              Swal.fire({
                icon: "error",
                title: "Gagal",
                text: response.message || "Gagal melakukan approval.",
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
  // Reject Button Handler (Approval View Page)
  // =========================================================================

  $(document).on("click", ".btn-reject", function () {
    var id =
      typeof PELAPORAN_ID !== "undefined" ? PELAPORAN_ID : $(this).data("id");

    Swal.fire({
      icon: "warning",
      title: "Reject Pelaporan",
      html: '<p class="text-left">Silakan masukkan alasan penolakan (minimal 10 karakter, maksimal 500 karakter):</p>',
      input: "textarea",
      inputPlaceholder: "Tuliskan alasan reject...",
      inputAttributes: {
        maxlength: 500,
        "aria-label": "Alasan Reject",
      },
      showCancelButton: true,
      confirmButtonColor: "#d33",
      confirmButtonText: "Ya, Reject",
      cancelButtonText: "Batal",
      allowOutsideClick: false,
      inputValidator: function (value) {
        if (!value || value.trim().length === 0) {
          return "Alasan reject wajib diisi.";
        }
        if (value.trim().length < 10) {
          return "Alasan reject minimal 10 karakter.";
        }
        if (value.trim().length > 500) {
          return "Alasan reject maksimal 500 karakter.";
        }
      },
    }).then(function (result) {
      if (result.isConfirmed) {
        var alasan = result.value.trim();

        // Show loading state
        Swal.fire({
          title: "Memproses...",
          text: "Mohon tunggu",
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: function () {
            Swal.showLoading();
          },
        });

        $.ajax({
          type: "POST",
          url: BASE_URL + "reject/" + id,
          data: { alasan: alasan },
          dataType: "json",
          cache: false,
          success: function (response) {
            if (response.status === true || response.status == "1") {
              Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: response.message || "Pelaporan berhasil direject.",
                timer: 3000,
                allowOutsideClick: false,
              }).then(function () {
                window.location.href = BASE_URL + "approval";
              });
            } else {
              Swal.fire({
                icon: "error",
                title: "Gagal",
                text: response.message || "Gagal melakukan reject.",
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
});
