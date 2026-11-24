(() => {
    const el = id => document.getElementById('rp-atur-' + id);
    const list = el('list');
    const esc = value => $('<span>').text(value == null ? '' : String(value)).html().replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    const rupiah = value => 'Rp ' + value.toLocaleString('id-ID', { maximumFractionDigits: 2 });
    const endpoint = name => siteurl + 'request_payment/' + name;
    const shell = document.getElementById('rp-atur');
    const page = document.getElementById('rp_bulk_bar').closest('.box');
    const selection = new Set();
    let documents, groups, done;
    const draft = (docs, previous) => {
        const account = docs.length === 1 ? documents[docs[0]].account : {};
        return { docs, bank: account.bank || '', no_rek: account.no_rek || '', nama: account.nama || '',
            admin: previous ? previous.admin : '', bearer: previous ? previous.bearer : 'recipient' };
    };
    function tax(doc) {
        const dpp = Math.round(Number(doc.nilai_pengajuan) * 100);
        const ppn = doc.flags.ppn ? Math.ceil(dpp * 11 / 10000) : 0;
        const pph23 = doc.flags.pph23 ? Math.ceil(dpp / 5000) : 0;
        const pph21 = doc.flags.pph21 ? Math.ceil(dpp / 4000) : 0;
        return { ppn, pph23, pph21, net: dpp / 100 + ppn - pph21 - pph23 };
    }
    function updateTotals() {
        let bankTotal = 0;
        groups.forEach((group, i) => {
            const net = group.docs.reduce((sum, no) => sum + tax(documents[no]).net, 0);
            const admin = Number(group.admin) || 0;
            const transfer = net - (group.bearer === 'recipient' ? admin : 0);
            const bank = transfer + admin;
            bankTotal += bank;
            el('sum-' + i).innerHTML = '<div><small class="trf-label">TOTAL DIBAYARKAN</small>' + rupiah(net) +
                '</div><div class="text-success"><small class="trf-label">JUMLAH TRANSFER KE PENERIMA</small>' + rupiah(transfer) +
                '</div><div><small class="trf-label">TOTAL KELUAR BANK</small>' + rupiah(bank) + '</div>';
            const panel = el('sum-' + i).closest('.trf-panel');
            panel.querySelector('.trf-recipient').textContent = group.nama ? 'Transfer ke ' + group.nama : '';
            panel.querySelectorAll('input[type=radio]').forEach(input => input.disabled = !admin);
        });
        bankTotal += Object.values(documents).filter(doc => doc.group_type === 'debt').reduce((sum, doc) => sum + tax(doc).net, 0);
        el('bank').textContent = rupiah(bankTotal);
        el('count').textContent = groups.length;
        el('merge').disabled = selection.size < 2;
        el('picked').textContent = selection.size ? selection.size + ' dokumen dicentang' : '';
    }
    function render() {
        list.innerHTML = groups.map((group, i) => {
            const rows = group.docs.map(no => {
                const doc = documents[no];
                const t = tax(doc);
                return '<tr><td class="text-center"><input type="checkbox" aria-label="Pilih ' + esc(no) + '" data-pick="' + esc(no) + '"' +
                    (selection.has(no) ? ' checked' : '') + (doc.group_type === 'refill' ? ' disabled' : '') + '></td><td>' +
                    '<span class="rp-transfer-tag">' + esc(doc.kategori) + '</span><br><b>' + esc(no) + '</b></td><td>' + esc(doc.request_by) +
                    '</td><td style="min-width:250px"><textarea class="form-control input-sm" rows="3" aria-label="Keperluan ' + esc(no) + '" data-note="' + esc(no) + '">' + esc(doc.note) +
                    '</textarea><small class="text-muted">Bisa dilengkapi. Cetak dokumen tetap memakai keterangan asli.</small></td><td>' + rupiah(Number(doc.nilai_pengajuan)) +
                    '</td><td>' + (t.ppn ? rupiah(t.ppn) : '-') + '</td><td>' + (t.pph23 ? rupiah(t.pph23) : '-') + '</td><td>' +
                    (t.pph21 ? rupiah(t.pph21) : '-') + '</td><td>' + rupiah(t.net) + '</td><td style="min-width:155px">' +
                    '<label class="btn btn-default btn-xs"><i class="fa fa-paperclip"></i> Lampirkan file' +
                    '<input class="sr-only" type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" data-file="' + esc(no) + '"></label>' +
                    (doc.files.length ? doc.files.map((file, index) => '<div>' + esc(file.name) + ' <button type="button" class="btn btn-link btn-xs text-danger" aria-label="Hapus lampiran" data-remove="' +
                    esc(no) + '" data-index="' + index + '">&times;</button></div>').join('') : '<div class="text-muted"><small>Tidak wajib</small></div>') + '</td></tr>';
            }).join('');
            const field = (name, label, placeholder, width) => '<label><span class="trf-label">' + label +
                ' <span class="text-danger">*</span></span><input class="form-control input-sm" data-field="' + name + '" value="' + esc(group[name]) +
                '" required maxlength="' + width + '" placeholder="' + placeholder + '"></label>';
            return '<div class="trf-panel" data-group="' + i + '"><div class="trf-head"><span class="rp-transfer-tag">Transfer ' + (i + 1) +
                '</span><b class="trf-recipient"></b><span class="text-muted">' + (group.docs.length > 1 ? 'Gabungan ' : '') + group.docs.length +
                ' dokumen &middot; ' + esc([...new Set(group.docs.map(no => documents[no].kategori))].join(' + ')) + '</span>' +
                (group.docs.length > 1 ? '<button type="button" class="btn btn-default btn-xs" data-split="' + i + '"><i class="fa fa-scissors"></i> Pisahkan</button>' : '') +
                '</div><div class="trf-body"><div class="table-responsive"><table class="table table-bordered"><thead><tr>' +
                '<th>PILIH</th><th>NO. DOKUMEN / KATEGORI</th><th>REQUEST BY</th><th>KEPERLUAN</th><th>DPP</th><th>PPN</th><th>PPH23</th><th>PPH21</th><th>DIBAYARKAN</th><th>LAMPIRAN TAMBAHAN (opsional)</th>' +
                '</tr></thead><tbody>' + rows + '</tbody></table></div><div class="trf-rek">' +
                field('bank', 'BANK TUJUAN', 'mis. BCA', 100) + field('no_rek', 'NO. REKENING', 'angka, titik atau strip', 80) +
                field('nama', 'NAMA PENERIMA (A/N REKENING)', 'sesuai buku tabungan', 160) + '</div><div class="trf-fields">' +
                '<label><span class="trf-label">NOMINAL ADMIN (1x per transfer)</span><input class="form-control input-sm text-right" type="number" min="0" step="0.01" ' +
                'data-field="admin" placeholder="0" value="' + esc(group.admin) + '"><small class="text-muted">Isi nominal sesuai bank. Kosong = tanpa admin.</small></label>' +
                '<div><span class="trf-label">ADMIN DITANGGUNG</span>' + ['recipient', 'company'].map(type => '<label class="radio-inline"><input type="radio" name="bear-' + i +
                '" data-field="bearer" value="' + type + '"' + (group.bearer === type ? ' checked' : '') + '> ' +
                (type === 'recipient' ? 'Penerima' : 'Perusahaan') + '</label>').join('') +
                '</div></div><div class="trf-sum" id="rp-atur-sum-' + i + '"></div></div></div>';
        }).join('');
        const debt = Object.values(documents).filter(doc => doc.group_type === 'debt');
        if (debt.length) {
            list.insertAdjacentHTML('beforeend', '<div class="well well-sm">Hutang Petty Cash &mdash; ' + esc(debt.map(doc => doc.no_dokumen).join(', ')) + '</div>');
        }
        updateTotals();
    }
    window.rpArrangePayment = function (numbers, state, company, callback) {
        if (!company) {
            alert('Company dokumen harus terverifikasi.');
            return;
        }
        $.post(endpoint('transfer_accounts'), { documents: numbers }, response => {
            if (Number(response.status) !== 1) {
                alert(response.msg);
                return;
            }
            done = callback;
            documents = response.documents;
            selection.clear();
            numbers.forEach(no => {
                const doc = documents[no];
                doc.flags = { ppn: +state[no].ppn, pph21: +state[no].pph21, pph23: +state[no].pph23, admin: state[no].admin || 0 };
                doc.note = doc.keperluan || '';
                doc.files = [];
            });
            groups = numbers.filter(no => documents[no].group_type !== 'debt').map(no => draft([no]));
            el('company').textContent = company.nama;
            el('docs').textContent = numbers.length;
            el('error').hidden = true;
            page.style.display = 'none';
            shell.hidden = false;
            render();
            el('back').focus();
            window.scrollTo(0, 0);
        }, 'json').fail(() => alert('Gagal memuat dokumen transfer.'));
    };
    const close = () => {
        shell.hidden = true;
        page.style.display = '';
        groups = documents = null;
    };
    el('back').onclick = el('cancel').onclick = () => {
        if (confirm('Batalkan pengaturan pembayaran?')) close();
    };
    list.addEventListener('input', event => {
        const input = event.target;
        if (input.dataset.note != null) {
            documents[input.dataset.note].note = input.value;
        }
        if (input.dataset.field && input.type !== 'radio') {
            groups[Number(input.closest('[data-group]').dataset.group)][input.dataset.field] = input.value;
            updateTotals();
        }
    });
    list.addEventListener('change', event => {
        const input = event.target;
        if (input.dataset.pick != null) {
            input.checked ? selection.add(input.dataset.pick) : selection.delete(input.dataset.pick);
        }
        if (input.dataset.field === 'bearer') {
            groups[Number(input.closest('[data-group]').dataset.group)].bearer = input.value;
        }
        if (input.dataset.file != null) {
            documents[input.dataset.file].files.push(...input.files);
            render();
        }
        updateTotals();
    });
    list.addEventListener('click', event => {
        const button = event.target.closest('button');
        if (!button) return;
        if (button.dataset.remove != null) {
            documents[button.dataset.remove].files.splice(Number(button.dataset.index), 1);
            render();
        }
        if (button.dataset.split != null) {
            const i = Number(button.dataset.split);
            groups.splice(i, 1, ...groups[i].docs.map(no => draft([no])));
            selection.clear();
            render();
        }
    });
    el('merge').onclick = () => {
        const selected = groups.flatMap(group => group.docs.filter(no => selection.has(no)));
        const first = groups.find(group => group.docs.some(no => selection.has(no)));
        const merged = draft(selected, first);
        groups = groups.flatMap(group => {
            const docs = group.docs.filter(no => !selection.has(no));
            const remaining = docs.length ? [docs.length === group.docs.length ? group : draft(docs, group)] : [];
            return group === first ? [merged, ...remaining] : remaining;
        });
        selection.clear();
        render();
    };
    el('form').onsubmit = event => {
        event.preventDefault();
        if (!confirm('Ajukan pembayaran untuk approval?')) return;
        const payload = new FormData();
        payload.append('transfers', JSON.stringify(groups.map(group => ({ ...group, admin: group.admin || '0' }))));
        let files = 0;
        Object.values(documents).forEach((doc, i) => {
            const item = { no_dokumen: doc.no_dokumen, ...doc.flags, keterangan_finance: doc.note === doc.keperluan ? '' : doc.note };
            Object.entries(item).forEach(([key, value]) => payload.append('items[' + i + '][' + key + ']', value));
            doc.files.forEach(file => {
                payload.append('transfer_files[' + files + ']', file);
                payload.append('attachment_docs[' + files++ + ']', doc.no_dokumen);
            });
        });
        const button = el('form').querySelector('button[type=submit]');
        const fail = msg => { el('error').textContent = msg; el('error').hidden = false; };
        if (files > 10 || Object.values(documents).some(doc => doc.files.some(file => file.size > 5242880))) {
            fail('Unggah maksimal 10 file, masing-masing maksimal 5 MB.');
            return;
        }
        button.disabled = true;
        $.ajax({ url: endpoint('submit_for_approval'), method: 'POST', data: payload, processData: false, contentType: false, dataType: 'json' })
            .done(response => {
                if (Number(response.status) !== 1) return fail(response.msg);
                close();
                done();
                alert(response.msg);
            }).fail(() => fail('Pengajuan gagal dikirim.')).always(() => { button.disabled = false; });
    };
})();
