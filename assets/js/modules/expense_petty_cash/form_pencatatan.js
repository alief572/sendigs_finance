/**
 * form_pencatatan.js
 * Dynamic form, calculation, validation, evidence upload for Pencatatan Petty Cash
 *
 * Dependencies: jQuery, Select2, SweetAlert2, Bootstrap Datepicker (loaded globally)
 * Global: FORM_DATA (injected from view)
 */
(function ($) {
  "use strict";

  var rowIndex = 0;
  var budgetInfo = FORM_DATA.budget_info || {
    budget: 0,
    budget_terpakai: 0,
    sisa_budget: 0,
  };

  // =========================================================================
  // FORMAT ANGKA INDONESIA
  // =========================================================================

  /**
   * Format number to Indonesian format (titik as thousands separator)
   * @param {number|string} number
   * @returns {string} formatted string e.g. "1.234.567"
   */
  function formatAngka(number) {
    var num = parseInt(number, 10);
    if (isNaN(num)) return "0";
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }

  /**
   * Parse Indonesian formatted string back to integer
   * @param {string} str e.g. "1.234.567"
   * @returns {number} parsed integer e.g. 1234567
   */
  function parseAngka(str) {
    if (!str) return 0;
    var cleaned = str.toString().replace(/\./g, "");
    var num = parseInt(cleaned, 10);
    return isNaN(num) ? 0 : num;
  }

  // =========================================================================
  // CALCULATION
  // =========================================================================

  /**
   * Calculate row total = jumlah x nominal
   * @param {jQuery} $row - the table row element
   */
  function calculateRowTotal($row) {
    var jumlah = parseInt($row.find(".jumlah-input").val(), 10) || 0;
    var nominal = parseAngka($row.find(".nominal-input").val());
    var total = jumlah * nominal;

    $row.find(".total-display").val(formatAngka(total));
    $row.find(".total-hidden").val(total);
  }

  /**
   * Recalculate Grand Total from all rows
   */
  function calculateGrandTotal() {
    var grandTotal = 0;
    $("#detail-table tbody tr").each(function () {
      var total = parseInt($(this).find(".total-hidden").val(), 10) || 0;
      grandTotal += total;
    });

    $("#grand-total-display").text(formatAngka(grandTotal));
    checkBudgetWarning(grandTotal);
    renderJurnalSimulation();
  }

  /**
   * Show/hide budget warning if Grand Total > Sisa Budget
   * @param {number} grandTotal
   */
  function checkBudgetWarning(grandTotal) {
    var sisaBudget = budgetInfo.sisa_budget || 0;
    if (grandTotal > sisaBudget) {
      $("#budget-warning-container").show();
    } else {
      $("#budget-warning-container").hide();
    }
  }

  // =========================================================================
  // JURNAL SIMULATION (Real-time Preview)
  // =========================================================================

  /**
   * Render jurnal simulation table based on current form data.
   * Shows preview of journal entries that will be created on save.
   */
  function renderJurnalSimulation() {
    var company = $("#company").val();
    var tanggal = $("#tanggal").val() || "-";
    var grandTotal = 0;
    var rows = [];

    // Collect detail rows data
    $("#detail-table tbody tr").each(function () {
      var $row = $(this);
      var coa = $row.find(".coa-select").val();
      var coaText = $row.find(".coa-select option:selected").text().trim();
      var total = parseInt($row.find(".total-hidden").val(), 10) || 0;

      if (coa && total > 0) {
        // Extract COA name (format: "5104-01-20 - Biaya ATK")
        var coaName =
          coaText.indexOf(" - ") > -1
            ? coaText.split(" - ").slice(1).join(" - ")
            : coaText;
        rows.push({ coa: coa, coaName: coaName, total: total });
        grandTotal += total;
      }
    });

    // Always show simulation container
    $("#jurnal-simulation-container").show();

    var emptyStm =
      '<tr><td colspan="6" class="text-center text-muted" style="padding:10px;"><em>Tidak ada data (Company bukan STM)</em></td></tr>';
    var emptyIC =
      '<tr><td colspan="6" class="text-center text-muted" style="padding:10px;"><em>Tidak ada data (Company bukan VUCA/SUSTAIN)</em></td></tr>';
    var emptyAll =
      '<tr><td colspan="6" class="text-center text-muted" style="padding:10px;"><em>Belum ada data</em></td></tr>';

    if (!company || rows.length === 0 || grandTotal === 0) {
      $("#jurnal-stm-body").html(emptyAll);
      $("#jurnal-stm-total-debit").text("-");
      $("#jurnal-stm-total-kredit").text("-");
      $("#jurnal-company-body").html(emptyAll);
      $("#jurnal-company-total-debit").text("-");
      $("#jurnal-company-total-kredit").text("-");
      $("#jurnal-stm-side-body").html(emptyAll);
      $("#jurnal-stm-side-total-debit").text("-");
      $("#jurnal-stm-side-total-kredit").text("-");
      $(".jurnal-company-name").text("VUCA/SUSTAIN");
      return;
    }

    if (company === "STM") {
      // === STM: N debit + 1 kredit Kas Kecil ===
      $("#jurnal-stm-section").show();
      $("#jurnal-intercompany-section").hide();

      var html = "";
      for (var i = 0; i < rows.length; i++) {
        html +=
          "<tr>" +
          "<td>" +
          tanggal +
          "</td>" +
          "<td>" +
          rows[i].coa +
          "</td>" +
          "<td>" +
          rows[i].coaName +
          "</td>" +
          "<td>STM</td>" +
          '<td class="text-right">' +
          formatAngka(rows[i].total) +
          "</td>" +
          '<td class="text-right">-</td>' +
          "</tr>";
      }
      // Credit: Kas Kecil
      html +=
        '<tr style="background: #fff3cd;">' +
        "<td>" +
        tanggal +
        "</td>" +
        "<td>1101-01-02</td>" +
        "<td>Kas Kecil</td>" +
        "<td>STM</td>" +
        '<td class="text-right">-</td>' +
        '<td class="text-right">' +
        formatAngka(grandTotal) +
        "</td>" +
        "</tr>";

      $("#jurnal-stm-body").html(html);
      $("#jurnal-stm-total-debit").text("Rp " + formatAngka(grandTotal));
      $("#jurnal-stm-total-kredit").text("Rp " + formatAngka(grandTotal));

      // Clear inter-company tables (show empty)
      $("#jurnal-company-body").html(emptyIC);
      $("#jurnal-company-total-debit").text("-");
      $("#jurnal-company-total-kredit").text("-");
      $("#jurnal-stm-side-body").html(emptyIC);
      $("#jurnal-stm-side-total-debit").text("-");
      $("#jurnal-stm-side-total-kredit").text("-");
      $(".jurnal-company-name").text("VUCA/SUSTAIN");
    } else if (company === "VUCA" || company === "SUSTAIN") {
      // === VUCA/SUSTAIN: 2 sets (Company side + STM side) ===
      $("#jurnal-stm-section").hide();
      $("#jurnal-intercompany-section").show();
      $(".jurnal-company-name").text(company);

      var coaHutang = company === "VUCA" ? "2103-01-01" : "2103-01-02";
      var nmHutang = "Hutang ke STM";
      var coaPiutang = company === "VUCA" ? "1103-01-01" : "1103-01-02";
      var nmPiutang = "Piutang " + company;

      // Set 1: Company side
      var htmlCompany = "";
      for (var j = 0; j < rows.length; j++) {
        htmlCompany +=
          "<tr>" +
          "<td>" +
          tanggal +
          "</td>" +
          "<td>" +
          rows[j].coa +
          "</td>" +
          "<td>" +
          rows[j].coaName +
          "</td>" +
          "<td>" +
          company +
          "</td>" +
          '<td class="text-right">' +
          formatAngka(rows[j].total) +
          "</td>" +
          '<td class="text-right">-</td>' +
          "</tr>";
      }
      htmlCompany +=
        '<tr style="background: #fff3cd;">' +
        "<td>" +
        tanggal +
        "</td>" +
        "<td>" +
        coaHutang +
        "</td>" +
        "<td>" +
        nmHutang +
        "</td>" +
        "<td>" +
        company +
        "</td>" +
        '<td class="text-right">-</td>' +
        '<td class="text-right">' +
        formatAngka(grandTotal) +
        "</td>" +
        "</tr>";

      $("#jurnal-company-body").html(htmlCompany);
      $("#jurnal-company-total-debit").text("Rp " + formatAngka(grandTotal));
      $("#jurnal-company-total-kredit").text("Rp " + formatAngka(grandTotal));

      // Set 2: STM side
      var htmlStm =
        "<tr>" +
        "<td>" +
        tanggal +
        "</td>" +
        "<td>" +
        coaPiutang +
        "</td>" +
        "<td>" +
        nmPiutang +
        "</td>" +
        "<td>STM</td>" +
        '<td class="text-right">' +
        formatAngka(grandTotal) +
        "</td>" +
        '<td class="text-right">-</td>' +
        "</tr>" +
        '<tr style="background: #fff3cd;">' +
        "<td>" +
        tanggal +
        "</td>" +
        "<td>1101-01-02</td>" +
        "<td>Kas Kecil</td>" +
        "<td>STM</td>" +
        '<td class="text-right">-</td>' +
        '<td class="text-right">' +
        formatAngka(grandTotal) +
        "</td>" +
        "</tr>";

      $("#jurnal-stm-side-body").html(htmlStm);
      $("#jurnal-stm-side-total-debit").text("Rp " + formatAngka(grandTotal));
      $("#jurnal-stm-side-total-kredit").text("Rp " + formatAngka(grandTotal));

      // Clear STM direct table (show empty)
      $("#jurnal-stm-body").html(emptyStm);
      $("#jurnal-stm-total-debit").text("-");
      $("#jurnal-stm-total-kredit").text("-");
    }
  }

  function renumberRows() {
    $("#detail-table tbody tr").each(function (idx) {
      $(this)
        .find(".row-number")
        .text(idx + 1);
    });
  }

  // =========================================================================
  // SELECT2 INIT
  // =========================================================================

  function initSelect2($element) {
    if ($element.hasClass("select2-hidden-accessible")) {
      $element.select2("destroy");
    }
    $element.select2({
      placeholder: "-- Pilih COA --",
      allowClear: true,
      width: "100%",
    });
  }

  function initAllSelect2() {
    $(".coa-select").each(function () {
      initSelect2($(this));
    });
    // Company select2
    if (
      $("#company").length &&
      !$("#company").hasClass("select2-hidden-accessible")
    ) {
      $("#company").select2({
        placeholder: "-- Pilih Company --",
        allowClear: true,
        width: "100%",
      });
    }
  }

  // =========================================================================
  // DATEPICKER INIT
  // =========================================================================

  function initDatepicker() {
    $(".datepicker").datepicker({
      format: "yyyy-mm-dd",
      autoclose: true,
      todayHighlight: true,
    });
  }

  // =========================================================================
  // ADD ROW
  // =========================================================================

  function getNextRowIndex() {
    var maxIdx = -1;
    $("#detail-table tbody tr").each(function () {
      var idx = parseInt($(this).attr("data-row"), 10);
      if (idx > maxIdx) maxIdx = idx;
    });
    return maxIdx + 1;
  }

  function addRow() {
    var idx = getNextRowIndex();
    var coaOptions = $("#coa-options-template").html();

    var rowHtml =
      '<tr data-row="' +
      idx +
      '">' +
      '<td class="text-center row-number"></td>' +
      "<td>" +
      '<select name="details[' +
      idx +
      '][coa_code]" class="form-control select2 coa-select" style="width: 100%;">' +
      coaOptions +
      "</select>" +
      "</td>" +
      "<td>" +
      '<input type="text" name="details[' +
      idx +
      '][pengeluaran]" class="form-control pengeluaran-input" value="" placeholder="Pengeluaran" maxlength="255">' +
      "</td>" +
      "<td>" +
      '<input type="text" name="details[' +
      idx +
      '][spesifikasi]" class="form-control" value="" placeholder="Spesifikasi" maxlength="255">' +
      "</td>" +
      "<td>" +
      '<input type="number" name="details[' +
      idx +
      '][jumlah]" class="form-control text-center jumlah-input" value="" min="1" max="9999">' +
      "</td>" +
      "<td>" +
      '<input type="text" name="details[' +
      idx +
      '][nominal]" class="form-control text-right nominal-input" value="" placeholder="0">' +
      "</td>" +
      "<td>" +
      '<input type="text" class="form-control text-right total-display" value="0" readonly>' +
      '<input type="hidden" name="details[' +
      idx +
      '][total]" class="total-hidden" value="0">' +
      "</td>" +
      "<td>" +
      '<div class="evidence-container" data-row="' +
      idx +
      '">' +
      '<input type="file" name="evidence_' +
      idx +
      '[]" class="evidence-input" multiple accept=".png,.jpg,.pdf,.xlsx,.xls" style="display:none;">' +
      '<button type="button" class="btn btn-xs btn-default btn-upload-evidence" title="Upload Evidence">' +
      '<i class="fa fa-upload"></i> Upload' +
      "</button>" +
      "</div>" +
      "</td>" +
      '<td class="text-center">' +
      '<button type="button" class="btn btn-danger btn-sm btn-remove-row" title="Hapus Baris"><i class="fa fa-trash"></i></button>' +
      "</td>" +
      "</tr>";

    $("#detail-table tbody").append(rowHtml);

    var $newRow = $('#detail-table tbody tr[data-row="' + idx + '"]');
    initSelect2($newRow.find(".coa-select"));
    renumberRows();
  }

  // =========================================================================
  // REMOVE ROW
  // =========================================================================

  function removeRow($btn) {
    var $row = $btn.closest("tr");
    var rowCount = $("#detail-table tbody tr").length;

    if (rowCount <= 1) {
      Swal.fire({
        icon: "warning",
        title: "Peringatan",
        text: "Minimal harus ada 1 baris detail item.",
        timer: 3000,
      });
      return;
    }

    $row.find(".coa-select").select2("destroy");
    $row.remove();
    renumberRows();
    calculateGrandTotal();
  }

  // =========================================================================
  // COA CHANGE - AUTO-FILL PENGELUARAN
  // =========================================================================

  function onCoaChange($select) {
    var $row = $select.closest("tr");
    var selectedOption = $select.find(":selected");
    var pengeluaran = selectedOption.data("pengeluaran") || "";
    $row.find(".pengeluaran-input").val(pengeluaran);
  }

  // =========================================================================
  // EVIDENCE UPLOAD
  // =========================================================================

  function uploadEvidence($container, files) {
    var rowIdx = $container.data("row");

    if (!files || files.length === 0) return;

    // Validate file count (max 5 per row)
    var existingCount = $container.find(".evidence-item").length;
    if (existingCount + files.length > 5) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Maksimal 5 file evidence per baris item.",
        timer: 3000,
      });
      return;
    }

    // Validate each file
    var allowedExt = ["png", "jpg", "pdf", "xlsx", "xls"];
    var maxSize = 5 * 1024 * 1024; // 5 MB

    for (var i = 0; i < files.length; i++) {
      var file = files[i];
      var ext = file.name.split(".").pop().toLowerCase();

      if (allowedExt.indexOf(ext) === -1) {
        Swal.fire({
          icon: "error",
          title: "Format File Tidak Valid",
          text:
            'File "' +
            file.name +
            '" tidak diizinkan. Format yang diterima: png, jpg, pdf, xlsx, xls.',
          timer: 3000,
        });
        return;
      }

      if (file.size > maxSize) {
        Swal.fire({
          icon: "error",
          title: "Ukuran File Terlalu Besar",
          text: 'File "' + file.name + '" melebihi batas 5 MB.',
          timer: 3000,
        });
        return;
      }
    }

    // Upload via AJAX — upload one file at a time
    var $uploadBtn = $container.find(".btn-upload-evidence");
    $uploadBtn
      .prop("disabled", true)
      .html('<i class="fa fa-spinner fa-spin"></i> Uploading...');

    var uploadQueue = [];
    for (var j = 0; j < files.length; j++) {
      uploadQueue.push(files[j]);
    }

    var uploadedFiles = [];
    var uploadErrors = [];

    function processNextUpload() {
      if (uploadQueue.length === 0) {
        // All uploads done
        $uploadBtn
          .prop("disabled", false)
          .html('<i class="fa fa-upload"></i> Upload');
        $container.find(".evidence-input").val("");

        if (uploadedFiles.length > 0) {
          // Ensure evidence list exists
          var $list = $container.find(".evidence-list");
          if ($list.length === 0) {
            $container.prepend('<ul class="list-unstyled evidence-list"></ul>');
            $list = $container.find(".evidence-list");
          }

          // Append uploaded files to list
          $.each(uploadedFiles, function (i, evidence) {
            var displayName =
              evidence.original_name.length > 15
                ? evidence.original_name.substring(0, 15) + "..."
                : evidence.original_name;

            var itemHtml =
              '<li class="evidence-item" data-encrypted="' +
              evidence.encrypted_name +
              '">' +
              "<small>" +
              '<i class="fa fa-file"></i> ' +
              '<span class="evidence-name" title="' +
              evidence.original_name +
              '">' +
              displayName +
              "</span> " +
              '<a href="javascript:void(0)" class="text-red btn-remove-evidence" data-encrypted="' +
              evidence.encrypted_name +
              '" title="Hapus">' +
              '<i class="fa fa-times"></i>' +
              "</a>" +
              '<input type="hidden" name="evidences[' +
              rowIdx +
              "][" +
              i +
              '][original_name]" value="' +
              evidence.original_name +
              '">' +
              '<input type="hidden" name="evidences[' +
              rowIdx +
              "][" +
              i +
              '][encrypted_name]" value="' +
              evidence.encrypted_name +
              '">' +
              '<input type="hidden" name="evidences[' +
              rowIdx +
              "][" +
              i +
              '][file_type]" value="' +
              evidence.file_type +
              '">' +
              '<input type="hidden" name="evidences[' +
              rowIdx +
              "][" +
              i +
              '][file_size]" value="' +
              evidence.file_size +
              '">' +
              "</small>" +
              "</li>";
            $list.append(itemHtml);
          });
        }

        if (uploadErrors.length > 0) {
          Swal.fire({
            icon: "warning",
            title: "Sebagian Upload Gagal",
            text: uploadErrors.join(", "),
            timer: 4000,
          });
        }
        return;
      }

      var file = uploadQueue.shift();
      var formData = new FormData();
      formData.append("evidence_file", file);

      $.ajax({
        url: FORM_DATA.base_url + "upload_evidence",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function (response) {
          if (response.status === true && response.data) {
            uploadedFiles.push(response.data);
          } else {
            uploadErrors.push(file.name + ": " + (response.message || "Gagal"));
          }
          processNextUpload();
        },
        error: function () {
          uploadErrors.push(file.name + ": Gagal koneksi server");
          processNextUpload();
        },
      });
    }

    processNextUpload();
  }

  function deleteEvidence($btn) {
    var evidenceId = $btn.data("id");
    var $item = $btn.closest(".evidence-item");

    $btn.html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
      url: FORM_DATA.base_url + "delete_evidence/" + evidenceId,
      type: "POST",
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          $item.fadeOut(200, function () {
            $(this).remove();
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Gagal Menghapus",
            text: response.message || "Gagal menghapus file evidence.",
            timer: 3000,
          });
          $btn.html('<i class="fa fa-times"></i>');
        }
      },
      error: function () {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Gagal menghubungi server. Periksa koneksi Anda.",
          timer: 3000,
        });
        $btn.html('<i class="fa fa-times"></i>');
      },
    });
  }

  // =========================================================================
  // VALIDATION
  // =========================================================================

  function clearValidation() {
    $(".form-group").removeClass("has-error");
    $(".validation-error").remove();
    $("td").removeClass("has-error");
  }

  function showFieldError($field, message) {
    var $group = $field.closest(".form-group");
    if (!$group.length) {
      $group = $field.closest("td");
    }
    $group.addClass("has-error");
    if (!$group.find(".validation-error").length) {
      $group.append(
        '<span class="help-block validation-error text-red"><small>' +
          message +
          "</small></span>",
      );
    }
  }

  /**
   * Validate the form before saving
   * @returns {boolean} true if valid
   */
  function validateForm() {
    clearValidation();
    var isValid = true;
    var errors = [];

    // Company is selected
    var company = $("#company").val();
    if (!company || company === "") {
      showFieldError($("#company"), "Company wajib dipilih.");
      isValid = false;
    }

    // Request by is not empty
    var requestBy = $.trim($("#request_by").val());
    if (!requestBy) {
      showFieldError($("#request_by"), "Request By wajib diisi.");
      isValid = false;
    }

    // At least 1 detail row
    var $rows = $("#detail-table tbody tr");
    if ($rows.length === 0) {
      errors.push("Detail item harus diisi minimal 1 baris.");
      isValid = false;
    }

    // Validate each row: jumlah > 0, nominal > 0
    $rows.each(function () {
      var $row = $(this);
      var jumlah = parseInt($row.find(".jumlah-input").val(), 10);
      var nominal = parseAngka($row.find(".nominal-input").val());

      if (!jumlah || jumlah <= 0) {
        showFieldError($row.find(".jumlah-input"), "Jumlah harus > 0");
        isValid = false;
      } else if (jumlah > 9999) {
        showFieldError($row.find(".jumlah-input"), "Jumlah maks 9.999");
        isValid = false;
      }

      if (!nominal || nominal <= 0) {
        showFieldError($row.find(".nominal-input"), "Nominal harus > 0");
        isValid = false;
      } else if (nominal > 999999999) {
        showFieldError($row.find(".nominal-input"), "Nominal maks 999.999.999");
        isValid = false;
      }
    });

    // Show collected errors via SweetAlert
    if (errors.length > 0) {
      Swal.fire({
        icon: "error",
        title: "Validasi Gagal",
        html: errors.join("<br>"),
        timer: 3000,
      });
    }

    return isValid;
  }

  // =========================================================================
  // SAVE FORM
  // =========================================================================

  function saveForm() {
    if (!validateForm()) return;

    var $btn = $("#btn-save");
    $btn
      .prop("disabled", true)
      .html('<i class="fa fa-spinner fa-spin"></i>&nbsp;Menyimpan...');

    // Collect form data
    var formData = {
      id: $("#pencatatan_id").val() || "",
      petty_cash_id: $("#petty_cash_id").val(),
      tanggal: $("#tanggal").val(),
      company: $("#company").val(),
      request_by: $.trim($("#request_by").val()),
      keterangan: $.trim($("#keterangan").val()),
      details: [],
      evidences: {},
    };

    // Collect detail rows
    $("#detail-table tbody tr").each(function (rowIdx) {
      var $row = $(this);
      var rowData = {
        coa_code: $row.find(".coa-select").val(),
        pengeluaran: $.trim($row.find(".pengeluaran-input").val()),
        spesifikasi: $.trim($row.find('input[name*="[spesifikasi]"]').val()),
        jumlah: parseInt($row.find(".jumlah-input").val(), 10) || 0,
        nominal: parseAngka($row.find(".nominal-input").val()),
      };

      formData.details.push(rowData);

      // Collect evidence data from hidden inputs for this row
      var evidenceFiles = [];
      $row.find(".evidence-item").each(function () {
        var $item = $(this);
        var encrypted = $item.find('input[name*="[encrypted_name]"]').val();
        if (encrypted) {
          evidenceFiles.push({
            original_name: $item.find('input[name*="[original_name]"]').val(),
            encrypted_name: encrypted,
            file_type: $item.find('input[name*="[file_type]"]').val(),
            file_size: $item.find('input[name*="[file_size]"]').val(),
          });
        }
      });
      if (evidenceFiles.length > 0) {
        formData.evidences[rowIdx] = evidenceFiles;
      }
    });

    $.ajax({
      url: FORM_DATA.base_url + "save",
      type: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        if (response.status === true) {
          Swal.fire({
            icon: "success",
            title: "Berhasil",
            text: response.message || "Pencatatan berhasil disimpan.",
          }).then(function () {
            window.location.href = FORM_DATA.base_url;
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Gagal Menyimpan",
            text: response.message || "Terjadi kesalahan saat menyimpan data.",
          });
        }
      },
      error: function () {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Gagal menghubungi server. Periksa koneksi Anda.",
        });
      },
      complete: function () {
        $btn
          .prop("disabled", false)
          .html('<i class="fa fa-save"></i>&nbsp;Simpan');
      },
    });
  }

  // =========================================================================
  // BUDGET INFO REFRESH
  // =========================================================================

  function refreshBudgetInfo() {
    $.ajax({
      url: FORM_DATA.base_url + "get_budget_info",
      type: "GET",
      data: { petty_cash_id: FORM_DATA.petty_cash_id },
      dataType: "json",
      success: function (response) {
        if (response.status === "success" && response.data) {
          budgetInfo = response.data;
          $("#budget-display").text(formatAngka(budgetInfo.budget));
          $("#budget-terpakai-display").text(
            formatAngka(budgetInfo.budget_terpakai),
          );
          $("#sisa-budget-display").text(formatAngka(budgetInfo.sisa_budget));

          // Re-check budget warning with current Grand Total
          var currentGrandTotal = 0;
          $("#detail-table tbody tr").each(function () {
            currentGrandTotal +=
              parseInt($(this).find(".total-hidden").val(), 10) || 0;
          });
          checkBudgetWarning(currentGrandTotal);
        }
      },
    });
  }

  // =========================================================================
  // NOMINAL INPUT MASKING
  // =========================================================================

  function applyNominalMask($input) {
    var rawVal = $input.val();
    var numVal = parseAngka(rawVal);
    if (numVal > 0) {
      $input.val(formatAngka(numVal));
    } else if (rawVal === "" || rawVal === "0") {
      $input.val("");
    }
  }

  // =========================================================================
  // EVENT BINDINGS
  // =========================================================================

  function bindEvents() {
    // Add row
    $("#btn-add-row").on("click", function () {
      addRow();
    });

    // Remove row (delegated)
    $("#detail-table").on("click", ".btn-remove-row", function () {
      removeRow($(this));
    });

    // Jumlah change - recalculate
    $("#detail-table").on("input change", ".jumlah-input", function () {
      var $row = $(this).closest("tr");
      calculateRowTotal($row);
      calculateGrandTotal();
    });

    // Nominal keyup/blur - format + recalculate
    $("#detail-table").on("keyup", ".nominal-input", function () {
      var $row = $(this).closest("tr");
      calculateRowTotal($row);
      calculateGrandTotal();
    });

    $("#detail-table").on("blur", ".nominal-input", function () {
      applyNominalMask($(this));
      var $row = $(this).closest("tr");
      calculateRowTotal($row);
      calculateGrandTotal();
    });

    // COA change - auto-fill pengeluaran
    $("#detail-table").on("change", ".coa-select", function () {
      onCoaChange($(this));
    });

    // Evidence upload button
    $("#detail-table").on("click", ".btn-upload-evidence", function () {
      var $container = $(this).closest(".evidence-container");
      $container.find(".evidence-input").trigger("click");
    });

    // Evidence file input change
    $("#detail-table").on("change", ".evidence-input", function () {
      var $container = $(this).closest(".evidence-container");
      var files = this.files;
      if (files.length > 0) {
        uploadEvidence($container, files);
      }
    });

    // Evidence delete (delegated)
    $("#detail-table").on("click", ".btn-remove-evidence", function (e) {
      e.preventDefault();
      deleteEvidence($(this));
    });

    // Save form
    $("#btn-save").on("click", function (e) {
      e.preventDefault();
      saveForm();
    });

    // Clear validation on field change
    $("#company, #request_by").on("change input", function () {
      $(this).closest(".form-group").removeClass("has-error");
      $(this).closest(".form-group").find(".validation-error").remove();
    });

    // Re-render jurnal simulation when company changes
    $("#company").on("change", function () {
      renderJurnalSimulation();
    });

    $("#detail-table").on(
      "change input",
      ".jumlah-input, .nominal-input",
      function () {
        $(this).closest("td").removeClass("has-error");
        $(this).closest("td").find(".validation-error").remove();
      },
    );
  }

  // =========================================================================
  // INITIALIZATION
  // =========================================================================

  function init() {
    // Determine starting row index from existing rows
    rowIndex = getNextRowIndex();

    // Init Select2 on all COA selects and company
    initAllSelect2();

    // Init datepicker
    initDatepicker();

    // Calculate initial grand total (for edit mode)
    calculateGrandTotal();

    // Bind all events
    bindEvents();
  }

  // Run on document ready
  $(document).ready(function () {
    init();
  });
})(jQuery);
