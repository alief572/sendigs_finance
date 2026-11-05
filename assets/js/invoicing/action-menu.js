(function (root, factory) {
    'use strict';
    if (typeof module === 'object' && module.exports) module.exports = factory;
    else root.InvoiceActionMenu = factory(root);
}(typeof window === 'object' ? window : this, function (root) {
    'use strict';
    var document = root.document, opened = null, sequence = 0;
    var labels = {
        'Create Invoice': ['Buat invoice', 'fa-file-text-o', 'blue'],
        'Create Invoice Vuca': ['Buat invoice Vuca', 'fa-files-o', 'green'],
        'View Invoice': ['Lihat invoice', 'fa-eye', 'blue'],
        'View Invoice Vuca': ['Lihat invoice Vuca', 'fa-eye', 'blue'],
        'View Invoice Non Konsultasi': ['Lihat invoice', 'fa-eye', 'blue'],
        'Edit Invoice Non KOnsultasi': ['Edit invoice', 'fa-pencil', 'amber'],
        'Revisi Invoice': ['Revisi invoice', 'fa-pencil', 'amber'],
        'Print Invoice': ['Cetak invoice', 'fa-print', 'blue'],
        'Print Invoice Non Konsultasi': ['Cetak invoice', 'fa-print', 'blue'],
        'Print Kwitansi': ['Cetak kwitansi', 'fa-file-pdf-o', 'green'],
        'Close Invoice': ['Tutup invoice', 'fa-times-circle', 'red'],
        'Select Quotation': ['Buat invoice', 'fa-plus', 'blue'],
        'Buat Invoice': ['Buat invoice', 'fa-plus', 'blue'],
        'Close Penawaran': ['Tutup penawaran', 'fa-times-circle', 'red'],
        'Tutup Penawaran': ['Tutup penawaran', 'fa-times-circle', 'red']
    };
    function available(state) {
        return state.actions.filter(function (action) {
            return !action.disabled && !action.classList.contains('disabled') && action.getAttribute('aria-disabled') !== 'true';
        });
    }
    function close(restoreFocus) {
        if (!opened) return;
        var previous = opened;
        opened = null;
        previous.menu.hidden = true;
        previous.button.setAttribute('aria-expanded', 'false');
        previous.cell.appendChild(previous.menu);
        if (restoreFocus) previous.button.focus();
    }
    function open(state, last) {
        close(false);
        opened = state;
        document.body.appendChild(state.menu);
        state.menu.hidden = false;
        state.menu.style.maxHeight = Math.max(80, root.innerHeight - 16) + 'px';
        var rect = state.button.getBoundingClientRect();
        var width = state.menu.offsetWidth, height = state.menu.offsetHeight;
        var top = rect.bottom + 4;
        if (top + height > root.innerHeight - 8) top = rect.top - height;
        state.menu.style.left = Math.max(8, Math.min(rect.right - width, root.innerWidth - width - 8)) + 'px';
        state.menu.style.top = Math.max(8, Math.min(top, root.innerHeight - height - 8)) + 'px';
        state.button.setAttribute('aria-expanded', 'true');
        var items = available(state);
        if (items.length) items[last ? items.length - 1 : 0].focus();
    }
    function enhance(cell) {
        if (cell.querySelector('.invoice-action-toggle')) return;
        var actions = Array.prototype.slice.call(cell.querySelectorAll('a,button'));
        if (actions.length <= 1) return;
        var button = document.createElement('button'), menu = document.createElement('div');
        var id = 'invoice-action-menu-' + (++sequence);
        button.type = 'button';
        button.className = 'btn btn-default invoice-action-toggle';
        button.textContent = 'Aksi';
        button.setAttribute('aria-haspopup', 'menu');
        button.setAttribute('aria-expanded', 'false');
        button.setAttribute('aria-controls', id);
        menu.className = 'invoice-action-menu';
        menu.setAttribute('id', id);
        menu.setAttribute('role', 'menu');
        menu.setAttribute('aria-label', 'Aksi invoice');
        menu.hidden = true;
        actions.forEach(function (action) {
            var title = action.getAttribute('title') || action.textContent || 'Aksi invoice';
            var presentation = Object.prototype.hasOwnProperty.call(labels, title) ? labels[title] : [title, 'fa-ellipsis-h', 'blue'];
            var icon = document.createElement('i'), label = document.createElement('span');
            action.className = action.className.split(/\s+/).filter(function (name) { return !/^btn(?:-|$)/.test(name); }).join(' ');
            icon.className = 'fa ' + presentation[1] + ' invoice-action-icon';
            icon.setAttribute('aria-hidden', 'true');
            label.textContent = presentation[0];
            action.replaceChildren(icon, label);
            action.setAttribute('data-action-tone', presentation[2]);
            action.setAttribute('aria-label', presentation[0]);
            action.setAttribute('role', 'menuitem');
            action.setAttribute('tabindex', '-1');
            menu.appendChild(action);
        });
        cell.replaceChildren(button, menu);
        var state = {cell: cell, button: button, menu: menu, actions: actions};
        button.addEventListener('click', function () {
            if (opened === state) close(false);
            else open(state, false);
        });
        button.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                open(state, event.key === 'ArrowUp');
            }
        });
        // Original links/data attributes remain intact for the delegated modal handlers.
        menu.addEventListener('click', function (event) {
            if (actions.some(function (action) { return action.contains(event.target); })) close(false);
        });
    }
    document.addEventListener('click', function (event) {
        if (opened && !opened.menu.contains(event.target) && !opened.button.contains(event.target)) close(false);
    });
    document.addEventListener('keydown', function (event) {
        if (!opened) return;
        if (event.key === 'Escape') { event.preventDefault(); close(true); return; }
        if (event.key === 'Tab') { close(true); return; }
        if (['ArrowDown', 'ArrowUp', 'Home', 'End'].indexOf(event.key) === -1) return;
        event.preventDefault();
        var items = available(opened);
        if (!items.length) return;
        var index = items.indexOf(document.activeElement);
        if (event.key === 'Home') index = 0;
        else if (event.key === 'End') index = items.length - 1;
        else index = (index + (event.key === 'ArrowUp' ? -1 : 1) + items.length) % items.length;
        items[index].focus();
    });
    root.addEventListener('scroll', function (event) {
        if (opened && !opened.menu.contains(event.target)) close(false);
    }, true);
    root.addEventListener('resize', function () { close(false); });
    return {enhance: enhance, close: close};
}));
