/**
 * Popup preview evidence (Approval dan Detail Pelaporan).
 * Thumbnail adalah link biasa; tanpa skrip ini, atau dengan Ctrl/Shift-klik, file tetap terbuka di tab baru.
 * Tombol < > dan panah keyboard berpindah antar file pada baris item yang sama.
 */
(function ($) {
  "use strict";

  var files = [];
  var index = 0;
  var $modal = null;

  function buildModal() {
    $modal = $(
      '<div class="modal fade" id="ev-modal" tabindex="-1" role="dialog" aria-labelledby="ev-title">' +
        '<div class="modal-dialog modal-lg" role="document"><div class="modal-content">' +
        '<div class="modal-header">' +
        '<button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button>' +
        '<h4 class="modal-title" id="ev-title"></h4></div>' +
        '<div class="modal-body ev-stage"></div>' +
        '<div class="modal-footer ev-footer">' +
        '<span class="ev-nav">' +
        '<button type="button" class="btn btn-default ev-prev" aria-label="File sebelumnya">&lsaquo;</button>' +
        '<span class="ev-count" aria-live="polite"></span>' +
        '<button type="button" class="btn btn-default ev-next" aria-label="File berikutnya">&rsaquo;</button>' +
        "</span>" +
        '<a class="btn btn-primary ev-open" target="_blank" rel="noopener"><i class="fa fa-external-link"></i>&nbsp;Buka di tab baru</a>' +
        "</div></div></div></div>",
    ).appendTo("body");

    $modal.on("click", ".ev-prev", function () {
      show(index - 1);
    });
    $modal.on("click", ".ev-next", function () {
      show(index + 1);
    });
    $modal.on("keydown", function (e) {
      if (e.which === 37) show(index - 1);
      if (e.which === 39) show(index + 1);
    });
    // Kosongkan isi supaya PDF / gambar besar tidak tertinggal di memori
    $modal.on("hidden.bs.modal", function () {
      $modal.find(".ev-stage").empty();
    });
  }

  function show(i) {
    index = (i + files.length) % files.length;
    var file = files[index];
    var $stage = $modal.find(".ev-stage").empty();

    if (file.type === "image") {
      $("<img>", { src: file.url, alt: file.name }).appendTo($stage);
    } else if (file.type === "pdf") {
      $("<iframe>", { src: file.url, title: file.name }).appendTo($stage);
    } else {
      $stage.append(
        '<div class="ev-nopreview"><i class="fa fa-file-excel-o"></i>' +
          "<p>File ini tidak bisa ditampilkan di halaman. Buka atau unduh untuk melihat isinya.</p></div>",
      );
    }

    $modal.find("#ev-title").text(file.name);
    $modal.find(".ev-count").text(index + 1 + " / " + files.length);
    $modal.find(".ev-nav").toggle(files.length > 1);
    $modal.find(".ev-open").attr("href", file.url);
  }

  $(document).on("click", ".ev-thumb", function (e) {
    if (e.ctrlKey || e.metaKey || e.shiftKey) return;
    e.preventDefault();

    var $links = $(this).closest(".ev-list").find(".ev-thumb");
    files = $links
      .map(function () {
        return {
          url: this.getAttribute("href"),
          name: this.getAttribute("data-name"),
          type: this.getAttribute("data-type"),
        };
      })
      .get();

    if (!$modal) buildModal();
    show($links.index(this));
    $modal.modal("show");
  });
})(jQuery);
