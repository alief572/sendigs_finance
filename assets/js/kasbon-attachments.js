function kasbonPrAttachments(result) {
    var list = $('.pr-attachment-list').empty();
    (result.attachments || []).forEach(function (item) {
        var row = $('<div>');
        if (item.url) {
            row.append($('<a>').attr({ href: item.url, target: '_blank', rel: 'noopener' }).text(item.name));
        } else {
            row.text('Lampiran tidak tersedia: ' + item.name);
        }
        list.append(row);
    });
    if (result.message) {
        list.append($('<p>').addClass('text-warning').text(result.message));
    }
}
