(function (root, factory) {
    'use strict';
    var api = factory();
    if (typeof module === 'object' && module.exports) module.exports = api;
    else {
        root.SidebarMenuSearch = api;
        function start() {
            var component = root.document.getElementById('sidebar-search');
            var navigation = root.document.getElementById('sidebar-navigation-menus');
            if (component && navigation) api.mount(component, navigation, {
                expand: function () {
                    if (root.document.body.classList.contains('sidebar-collapse')) {
                        var toggle = root.document.querySelector('[data-toggle="offcanvas"]');
                        if (toggle) toggle.click();
                    }
                }
            });
        }
        if (root.document.readyState === 'loading') root.document.addEventListener('DOMContentLoaded', start);
        else start();
    }
}(typeof window === 'object' ? window : this, function () {
    'use strict';
    function normalize(text) { return String(text).replace(/\s+/g, ' ').trim().toLowerCase(); }
    function children(node, tag) {
        return Array.prototype.filter.call(node.children, function (child) { return child.tagName === tag; });
    }
    function label(link) {
        var clone = link.cloneNode(true);
        Array.prototype.forEach.call(clone.querySelectorAll('i,.pull-right-container,.badge,.label'), function (node) { node.parentNode.removeChild(node); });
        return normalize(clone.textContent);
    }
    function mount(component, navigation, options) {
        if (component.getAttribute('data-search-ready') === 'true') return;
        options = options || {};
        var input = component.querySelector('#sidebar-menu-search'), clear = component.querySelector('#sidebar-search-clear');
        var empty = component.querySelector('#sidebar-search-empty'), mini = component.querySelector('#sidebar-search-mini-btn');
        if (!input || !clear || !empty || !mini) return;
        var nodes = Array.prototype.slice.call(navigation.querySelectorAll('li'));
        var entries = nodes.map(function (node) {
            var link = children(node, 'A')[0];
            return {node: node, label: link ? label(link) : '', lists: children(node, 'UL'), searchable: !node.hidden && node.style.display !== 'none' && !node.classList.contains('header')};
        });
        if (!entries.length) return;
        entries.forEach(function (entry) {
            if (entries.some(function (other) { return other !== entry && other.node.contains(entry.node) && !other.searchable; })) entry.searchable = false;
        });
        var saved = null, timer = null;
        var schedule = options.schedule || setTimeout, cancel = options.cancel || clearTimeout;
        function snapshot() {
            var lists = Array.prototype.slice.call(navigation.querySelectorAll('ul'));
            saved = nodes.concat(lists).map(function (node) { return {node: node, display: node.style.display, open: node.classList.contains('menu-open')}; });
        }
        function restore() {
            if (saved) saved.forEach(function (item) {
                item.node.style.display = item.display;
                item.node.classList.toggle('menu-open', item.open);
            });
            saved = null; empty.hidden = true; clear.hidden = true;
        }
        function filter() {
            timer = null;
            var query = normalize(input.value);
            if (!query) { restore(); return; }
            if (!saved) snapshot();
            clear.hidden = false;
            var matched = entries.filter(function (entry) { return entry.searchable && entry.label.indexOf(query) !== -1; });
            var visible = entries.filter(function (entry) {
                return entry.searchable && matched.some(function (match) {
                    return entry === match || entry.node.contains(match.node) || (match.lists.length && match.node.contains(entry.node));
                });
            });
            entries.forEach(function (entry) {
                entry.node.style.display = visible.indexOf(entry) !== -1 ? '' : 'none';
                var open = visible.some(function (child) { return child !== entry && entry.node.contains(child.node); });
                entry.lists.forEach(function (list) { list.style.display = open ? 'block' : 'none'; });
                if (entry.lists.length) entry.node.classList.toggle('menu-open', open);
            });
            empty.hidden = matched.length !== 0;
        }
        function reset() {
            if (timer !== null) cancel(timer);
            timer = null; input.value = ''; restore(); input.focus();
        }
        input.addEventListener('input', function () {
            if (timer !== null) cancel(timer);
            timer = schedule(filter, 50);
        });
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') { event.preventDefault(); event.stopPropagation(); reset(); }
        });
        clear.addEventListener('click', reset);
        mini.addEventListener('click', function () { if (options.expand) options.expand(); input.focus(); });
        component.setAttribute('data-search-ready', 'true'); component.hidden = false;
        return {filter: filter};
    }
    return {mount: mount};
}));
