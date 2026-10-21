(function (root) {
    'use strict';
    const tabs = ['INV', 'RCV', 'PAY', 'EXP', 'PCS'];
    const defaults = () => ({ year: String(new Date().getFullYear()), month: '', company: '', category: '', q: '', start: 0, length: 10 });
    const escape = value => String(value == null ? '' : value).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const money = value => {
        const match = String(value == null ? '0' : value).match(/^(-?)(\d+)(?:\.(\d{1,2}))?$/);
        return match ? match[1] + match[2].replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '.' + (match[3] || '').padEnd(2, '0') : '—';
    };
    const journalDate = value => {
        const match = String(value == null ? '' : value).match(/^(\d{4})-(\d{2})-(\d{2})$/);
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return match && months[Number(match[2]) - 1] && Number(match[3]) >= 1 && Number(match[3]) <= 31
            ? match[3] + ' ' + months[Number(match[2]) - 1] + ' ' + match[1] : '—';
    };
    function columns(tab, posted) {
        return [['No.', null], ...(posted ? [['No. Jurnal Accounting', 'source_numbers'], ['Tgl Posting', 'posted_at']] : []),
            ['Tgl Jurnal', 'tgl_jurnal', 0], ['No. Transaksi', 'display_no_transaksi', 1], ['No. Referensi', 'no_referensi', 2],
            ...(tab === 'PAY' ? [['Kategori Transaksi', 'category', 3]] : []), ['Keterangan', 'keterangan', 4], ['Company', 'nm_company', 5],
            ['Debit', 'debit', 6], ['Kredit', 'kredit', 7], ['Aksi', 'action']];
    }
    function mount(el) {
        const find = selector => el.querySelector(selector);
        const all = selector => Array.from(el.querySelectorAll(selector));
        const posted = el.dataset.posted === '1';
        const canManage = el.dataset.manage === '1';
        const state = Object.fromEntries(tabs.map(tab => [tab, defaults()]));
        const form = find('#ja-filters');
        let active = tabs.includes(el.dataset.tab) ? el.dataset.tab : 'INV';
        let generation = 0, detailGeneration = 0, busy = false, loading = false, selected = null, searchTimer, summaryGeneration = 0;
        const message = (text, modal, warning) => { const box = find(modal ? '#ja-modal-message' : '#ja-message'); box.classList.toggle('alert-warning', !!warning); box.classList.toggle('alert-danger', !warning); box.textContent = text || ''; box.hidden = !text; };
        function params(values) {
            const result = new URLSearchParams();
            Object.entries(values).forEach(([key, value]) => { if (value != null) result.set(key, String(value)); });
            return result;
        }
        async function request(method, values, get) {
            const data = params(values);
            const response = await fetch(el.dataset.base + '/' + method + (get ? '?' + data : ''), {
                method: get ? 'GET' : 'POST', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: get ? undefined : data
            });
            let payload;
            try { payload = await response.json(); } catch (error) { throw new Error('Sesi atau respons tidak tersedia. Muat ulang halaman dan periksa status jurnal.'); }
            if (!response.ok || payload.status === 0) throw new Error(payload.msg || payload.error || 'Gagal memuat jurnal.');
            return payload;
        }
        const values = () => ({ ...state[active], tab: active });
        function saveFilters() { ['year', 'month', 'company', 'category'].forEach(key => { state[active][key] = form.elements[key].value; }); }
        function fill(select, options, first, selectedValue) {
            select.replaceChildren();
            if (first) select.add(new Option(first, ''));
            Object.entries(options).forEach(([value, label]) => select.add(new Option(label, value)));
            if (selectedValue && !Array.from(select.options).some(option => option.value === selectedValue)) select.add(new Option(selectedValue, selectedValue));
            select.value = selectedValue;
        }
        function setLoading(value) {
            loading = value;
            find('#ja-loading').hidden = !value;
            find('#ja-table').setAttribute('aria-busy', String(value));
            ['year', 'month', 'company', 'category'].forEach(key => { form.elements[key].disabled = value; });
            ['#ja-apply', '#ja-reset', '#ja-length'].forEach(selector => { find(selector).disabled = value; });
        }
        async function load() {
            const token = ++generation, tab = active, filter = { ...state[tab], tab };
            message('');
            find('#ja-loading-text').textContent = 'Memuat ' + (find('#ja-tab-' + tab).dataset.title || 'data jurnal') + '…';
            find('#ja-search').value = filter.q;
            find('#ja-section-title').textContent = find('#ja-tab-' + tab).dataset.title || 'Daftar jurnal';
            const cols = columns(tab, posted);
            find('#ja-table thead').innerHTML = '<tr>' + cols.map(([title]) => '<th>' + escape(title) + '</th>').join('') + '</tr>';
            find('#ja-table tbody').innerHTML = '<tr><td colspan="' + cols.length + '" class="text-center">Memuat data jurnal…</td></tr>';
            find('#ja-table tfoot').replaceChildren(); find('#ja-count').textContent = ''; find('#ja-page').textContent = '';
            ['#ja-export', '#ja-prev', '#ja-next'].forEach(selector => { find(selector).disabled = true; });
            setLoading(true);
            try {
                const [options, page] = await Promise.all([request('get_filters', filter), request('get_data_jurnal', filter)]);
                if (token !== generation) return;
                fill(form.elements.year, Object.fromEntries(options.years.map(year => [year, year])), 'Semua Tahun', state[tab].year);
                fill(form.elements.company, options.companies, 'Semua Company', state[tab].company);
                fill(form.elements.category, Object.fromEntries(options.categories.map(category => [category, category])), 'Semua Kategori', state[tab].category);
                form.elements.month.value = state[tab].month;
                find('#ja-search').value = state[tab].q; find('#ja-length').value = String(state[tab].length);
                render(page, tab);
                find('#ja-export').disabled = !page.recordsFiltered;
            } catch (error) {
                if (token !== generation) return;
                message(error.message); find('#ja-table tbody').innerHTML = '<tr><td colspan="20">Data jurnal gagal dimuat. Klik Terapkan filter untuk mencoba kembali.</td></tr>';
                find('#ja-table tfoot').replaceChildren(); find('#ja-count').textContent = ''; find('#ja-prev').disabled = true; find('#ja-next').disabled = true;
            } finally { if (token === generation) setLoading(false); }
        }
        function render(page, tab) {
            const cols = columns(tab, posted), filter = state[tab];
            find('#ja-table thead').innerHTML = '<tr>' + cols.map(([title, field, order]) => {
                const sorted = order != null && filter['order[0][column]'] === String(order);
                const direction = filter['order[0][dir]'] === 'desc' ? 'descending' : 'ascending';
                return '<th scope="col"' + (['debit', 'kredit'].includes(field) ? ' class="ja-money"' : '') + (order == null ? '' : ' aria-sort="' + (sorted ? direction : 'none') + '"') + '>' + (order == null ? escape(title) : '<button type="button" data-order="' + order + '" aria-label="Urutkan berdasarkan ' + escape(title) + '">' + escape(title) + '<span class="ja-sort" aria-hidden="true">' + (sorted ? (direction === 'ascending' ? '↑' : '↓') : '↕') + '</span></button>') + '</th>';
            }).join('') + '</tr>';
            find('#ja-table tbody').innerHTML = page.data.length ? page.data.map((row, index) => '<tr>' + cols.map(([, field]) => {
                if (!field) return '<td>' + (filter.start + index + 1) + '</td>';
                if (field === 'action') {
                    if (!posted && !canManage && row.source === 'EXP') return '<td>—</td>';
                    return '<td><button type="button" class="btn btn-sm btn-primary" data-id="' + Number(row.id) + '" data-source="' + escape(row.source) + '">' + (posted || !canManage ? 'Detail' : '+ Post') + '</button></td>';
                }
                const value = field === 'display_no_transaksi' ? (row.display_no_transaksi ?? row.no_transaksi) : row[field];
                if (field === 'keterangan' && row.debt_group) return '<td class="ja-description">' + escape(value || '') + '<br><small>' + escape(row.group_status || '') + (row.relation_warning ? ' — ' + escape(row.relation_warning) : '') + '</small></td>';
                const cellClass = ['debit', 'kredit'].includes(field) ? 'ja-money' : field === 'display_no_transaksi' ? 'ja-transaction' : field === 'keterangan' ? 'ja-description' : ['tgl_jurnal', 'no_referensi'].includes(field) ? 'ja-reference' : '';
                return '<td class="' + cellClass + '">' + (['debit', 'kredit'].includes(field) ? money(value) : escape(field === 'tgl_jurnal' || field === 'posted_at' ? (value ? journalDate(String(value).slice(0, 10)) : (posted ? '-' : '—')) : (value || (posted ? '-' : '—')))) + '</td>';
            }).join('') + '</tr>').join('') : '<tr><td colspan="' + cols.length + '" class="text-center">Tidak ada jurnal sesuai filter dan pencarian. Ubah filter atau pilih Reset untuk memeriksa kembali.</td></tr>';
            find('#ja-table tfoot').innerHTML = posted && page.recordsFiltered ? '<tr>' + cols.map(([, field], index) => '<td' + (['debit', 'kredit'].includes(field) ? ' class="ja-money"' : '') + '>' + (index === 0 ? 'TOTAL' : ['debit', 'kredit'].includes(field) ? money(page.totals[field]) : '') + '</td>').join('') + '</tr>' : '';
            find('#ja-count').textContent = 'Menampilkan ' + (page.recordsFiltered ? filter.start + 1 : 0) + '–' + (filter.start + page.data.length) + ' dari ' + page.recordsFiltered + ' transaksi';
            find('#ja-page').textContent = 'Halaman ' + (Math.floor(filter.start / filter.length) + 1) + ' / ' + Math.max(1, Math.ceil(page.recordsFiltered / filter.length));
            find('#ja-prev').disabled = filter.start === 0; find('#ja-next').disabled = filter.start + filter.length >= page.recordsFiltered;
        }
        async function summary() {
            if (posted) return;
            const token = ++summaryGeneration, data = {};
            tabs.forEach(tab => ['year', 'month', 'company', 'category'].forEach(key => { data['filters[' + tab + '][' + key + ']'] = state[tab][key]; }));
            try {
                const result = await request('get_summary', data);
                if (token !== summaryGeneration) return;
                all('.ja-card').forEach(card => {
                    const value = result[card.dataset.tab];
                    card.querySelector('[data-count]').textContent = String(value.count);
                    card.querySelector('[data-amount]').textContent = 'Debit Rp ' + money(value.debit);
                    card.querySelector('[data-age]').textContent = 'Tertua: ' + (value.oldest ? String(value.oldest).replace(/^(\d{4})-(\d{2})-(\d{2})$/, '$3/$2/$1') + ' (' + value.age + ' hari)' : '—');
                    find('#ja-tab-' + card.dataset.tab + ' [data-badge]').textContent = String(value.count);
                });
            } catch (error) { message(error.message); }
        }
        function activate(tab) {
            active = tab;
            all('.ja-tab, .ja-card').forEach(button => {
                const on = button.dataset.tab === tab; button.classList.toggle('active', on);
                if (button.classList.contains('ja-card')) button.setAttribute('aria-pressed', String(on));
                if (button.classList.contains('ja-tab')) { button.setAttribute('aria-selected', String(on)); button.tabIndex = on ? 0 : -1; }
            });
            find('#ja-panel').setAttribute('aria-labelledby', 'ja-tab-' + tab);
            find('#ja-category').hidden = tab !== 'PAY'; find('#ja-invoice-period-hint').hidden = tab !== 'INV'; load();
        }
        async function openDetail(button) {
            if (busy || loading) return;
            const token = ++detailGeneration;
            selected = { id: button.dataset.id, source: button.dataset.source };
            find('#ja-post').hidden = true; find('#ja-revise').hidden = true; find('#ja-reason-wrap').hidden = true; find('#ja-reason').value = '';
            find('#ja-detail').textContent = 'Memuat detail…'; message('', true);
            root.jQuery(find('#ja-modal')).modal('show');
            try {
                const detail = await request('preview', selected);
                if (token !== detailGeneration) return;
                find('#ja-detail').innerHTML = detail.html;
                find('#ja-post').hidden = !detail.can_post; find('#ja-revise').hidden = !detail.can_revise; find('#ja-reason-wrap').hidden = !detail.can_revise;
                if (detail.blocked_coa) message('Posting diblokir: COA hutang ke STM belum final dikonfirmasi Finance.', true);
            } catch (error) { if (token === detailGeneration) message(error.message, true); }
        }
        async function write(action) {
            if (busy || !selected) return;
            const reason = find('#ja-reason').value.trim();
            const isRevise = action === 'revisi_jurnal';
            if (isRevise && !reason) {
                message('Alasan revisi wajib diisi.', true);
                find('#ja-reason').focus();
                if (typeof root.Swal !== 'undefined' && typeof root.Swal.fire === 'function') {
                    root.Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Alasan revisi wajib diisi.' });
                }
                return;
            }
            let confirmed = false;
            const confirmText = isRevise ? 'Kembalikan jurnal ini untuk revisi?' : 'Posting jurnal ini ke accounting?';
            if (typeof root.Swal !== 'undefined' && typeof root.Swal.fire === 'function') {
                const res = await root.Swal.fire({
                    title: isRevise ? 'Konfirmasi Revisi' : 'Konfirmasi Posting',
                    text: confirmText,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: isRevise ? '#f39c12' : '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: isRevise ? 'Ya, Kembalikan' : 'Ya, Posting!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                });
                confirmed = !!res.isConfirmed;
            } else {
                confirmed = root.confirm(confirmText);
            }
            if (!confirmed) return;
            busy = true; find('#ja-post').disabled = true; find('#ja-revise').disabled = true; message('', true);
            try {
                const result = await request(action, { ...selected, alasan_revisi: reason });
                busy = false; root.jQuery(find('#ja-modal')).modal('hide'); state[active].start = 0; await load(); await summary();
                const successTitle = isRevise ? 'Revisi Berhasil!' : 'Posting Berhasil!';
                const successMsg = result.msg || (isRevise ? 'Jurnal berhasil dikembalikan untuk revisi.' : 'Jurnal berhasil diposting ke accounting.');
                if (typeof root.Swal !== 'undefined' && typeof root.Swal.fire === 'function') {
                    root.Swal.fire({
                        icon: 'success',
                        title: successTitle,
                        text: successMsg,
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6',
                        timer: 3500
                    });
                } else if (typeof root.swal === 'function') {
                    root.swal(successTitle, successMsg, 'success');
                }
                if (result.warning) message(result.warning, false, true);
            } catch (error) {
                const errorTitle = isRevise ? 'Revisi Gagal!' : 'Posting Gagal!';
                const errorMsg = error.message || (isRevise ? 'Gagal memproses revisi jurnal.' : 'Posting jurnal ke accounting gagal.');
                message(errorMsg, true); await load(); await summary();
                if (typeof root.Swal !== 'undefined' && typeof root.Swal.fire === 'function') {
                    root.Swal.fire({
                        icon: 'error',
                        title: errorTitle,
                        text: errorMsg,
                        showConfirmButton: true,
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#d33'
                    });
                } else if (typeof root.swal === 'function') {
                    root.swal(errorTitle, errorMsg, 'error');
                }
            }
            finally { busy = false; find('#ja-post').disabled = false; find('#ja-revise').disabled = false; }
        }
        all('.ja-tab, .ja-card').forEach(button => button.addEventListener('click', () => activate(button.dataset.tab)));
        all('.ja-tab').forEach(button => button.addEventListener('keydown', event => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault(); const index = tabs.indexOf(active);
            const tab = event.key === 'Home' ? tabs[0] : event.key === 'End' ? tabs[4] : tabs[(index + (event.key === 'ArrowRight' ? 1 : 4)) % 5];
            activate(tab); find('#ja-tab-' + tab).focus();
        }));
        form.addEventListener('submit', event => { event.preventDefault(); saveFilters(); state[active].start = 0; load(); summary(); });
        find('#ja-reset').addEventListener('click', () => { state[active] = defaults(); load(); summary(); });
        find('#ja-length').addEventListener('change', event => { state[active].length = Number(event.target.value); state[active].start = 0; load(); });
        find('#ja-search').addEventListener('input', event => { state[active].q = event.target.value; state[active].start = 0; clearTimeout(searchTimer); searchTimer = setTimeout(load, 250); });
        find('#ja-prev').addEventListener('click', () => { state[active].start = Math.max(0, state[active].start - state[active].length); load(); });
        find('#ja-next').addEventListener('click', () => { state[active].start += state[active].length; load(); });
        find('#ja-export').addEventListener('click', () => { root.location.assign(el.dataset.base + '/export_excel?' + params(values())); });
        find('#ja-table').addEventListener('click', event => {
            const detail = event.target.closest('[data-id]'), order = event.target.closest('[data-order]');
            if (detail) openDetail(detail);
            if (order) { const f = state[active], column = order.dataset.order; f['order[0][dir]'] = f['order[0][column]'] === column && f['order[0][dir]'] === 'asc' ? 'desc' : 'asc'; f['order[0][column]'] = column; f.start = 0; load(); }
        });
        find('#ja-action-form').addEventListener('submit', event => { event.preventDefault(); write('save_posting_jurnal'); });
        find('#ja-revise').addEventListener('click', () => write('revisi_jurnal'));
        root.jQuery(find('#ja-modal')).on('hide.bs.modal', event => { if (busy) event.preventDefault(); else { detailGeneration++; selected = null; } });
        activate(active); summary();
    }
    if (typeof module !== 'undefined' && module.exports) module.exports = { defaults, columns, escape, money, journalDate, mount };
    if (root.document) { const el = root.document.getElementById('journal-workspace'); if (el) mount(el); }
}(typeof window === 'undefined' ? globalThis : window));
