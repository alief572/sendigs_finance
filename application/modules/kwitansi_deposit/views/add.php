<?= mk_ui_assets() ?>
<div class="mk">
<h1>Kwitansi Deposit</h1>
<div class="crumb"><a href="<?= site_url('kwitansi_deposit') ?>">Kwitansi Deposit</a> / Tambah</div>

<div class="box primary">
    <form id="form_kwitansi" method="post" action="<?= site_url('kwitansi_deposit/save') ?>">
        <input type="hidden" name="id_company" id="id_company" value="">
        <input type="hidden" name="nm_company" id="nm_company" value="">
        <input type="hidden" name="id_customer" id="id_customer" value="">
        <input type="hidden" name="nm_customer" id="nm_customer" value="">

        <div class="grid2">
            <div>
                <div class="fg"><label>No Kwitansi</label><input type="text" id="preview_no_kwitansi" value="<?= htmlspecialchars($results['no_kwitansi_preview']) ?>" readonly><span class="hint">Otomatis sistem</span></div>
                <div class="fg"><label for="tanggal_kwitansi">Tanggal Kwitansi <span class="req">*</span></label><input type="date" name="tanggal_kwitansi" id="tanggal_kwitansi" value="<?= htmlspecialchars($results['today']) ?>"></div>
                <div class="fg"><label for="select_customer">Nama Customer <span class="req">*</span></label>
                    <select id="select_customer"><option value="">- Pilih Customer -</option>
                        <?php foreach ($results['customers'] as $c): ?>
                            <option value="<?= htmlspecialchars($c['id_customer']) ?>" data-name="<?= htmlspecialchars($c['nm_customer']) ?>"><?= htmlspecialchars($c['nm_customer']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <div class="fg"><label for="po_customer">No SPK <span class="req">*</span></label>
                    <select name="id_spk_penawaran" id="po_customer" disabled><option value="">- Pilih SPK -</option></select>
                    <span class="hint" id="spk_hint">Pilih customer dulu untuk lihat SPK</span>
                </div>
                <div class="fg"><label for="display_company">Company</label><input type="text" id="display_company" placeholder="Otomatis dari SPK" readonly><span class="hint">Mengikuti company SPK (masuk ke jurnal).</span></div>
                <div class="fg"><label for="nilai_kwitansi">Nilai Deposit <span class="req">*</span></label><input type="text" name="nilai_kwitansi" id="nilai_kwitansi" class="num" placeholder="0"></div>
                <div class="fg"><label for="keterangan">Keterangan</label><input type="text" name="keterangan" id="keterangan"></div>
            </div>
        </div>

        <div class="section-t" style="margin-top: 12px;">Jurnal Kwitansi Deposit</div>
        <div class="scroll">
            <table>
                <thead><tr><th>Tanggal Jurnal</th><th>COA</th><th>Nama Company</th><th>Nama Account</th><th>Deskripsi</th><th class="r">Debit</th><th class="r">Credit</th></tr></thead>
                <tbody>
                    <tr><td class="nowrap jt"></td><td>1102-01-01</td><td class="jc"></td><td>Piutang Dagang Usaha</td><td id="jurnal_ket_1">Piutang Dagang Usaha</td><td class="r jv">0</td><td class="r">0</td></tr>
                    <tr><td class="nowrap jt"></td><td>2102-01-02</td><td class="jc"></td><td>Deposit Customer</td><td id="jurnal_ket_2">Deposit Customer</td><td class="r">0</td><td class="r jv">0</td></tr>
                    <tr class="sum"><td colspan="5" class="c"><b>Balancing</b></td><td class="r"><b id="jtd">0</b></td><td class="r"><b id="jtk">0</b></td></tr>
                </tbody>
            </table>
        </div>

        <div class="inline" style="margin-top: 12px;">
            <a class="btn btn-danger" href="<?= site_url('kwitansi_deposit') ?>">Batal</a>
            <button type="submit" id="btn_submit" class="btn btn-success" disabled>Simpan Kwitansi</button>
            <span class="hint" id="save_hint">Lengkapi tanggal, customer, SPK, dan nilai deposit.</span>
        </div>
    </form>
</div>
</div>

<script>
(function () {
    var U = MockupUI;
    var $f = function (id) { return document.getElementById(id); };
    var URL_PREVIEW = <?= json_encode(site_url('kwitansi_deposit/preview_no_kwitansi')) ?>;
    var URL_SPK = <?= json_encode(site_url('kwitansi_deposit/get_spk_by_customer')) ?>;
    var URL_LIST = <?= json_encode(site_url('kwitansi_deposit')) ?>;

    // parseNum mockup (M:315): angka rupiah bulat; koma desimal dibuang.
    function parseNum(v) { var s = String(v || '').replace(/,\d{1,2}$/, '').replace(/[^\d-]/g, ''); var n = parseInt(s, 10); return isNaN(n) ? 0 : n; }
    function post(url, data, cb) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function () { var r = null; try { r = JSON.parse(xhr.responseText); } catch (e) {} cb(r); };
        xhr.onerror = function () { cb(null); };
        xhr.send(Object.keys(data).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]); }).join('&'));
    }

    function complete() { return !!($f('tanggal_kwitansi').value && $f('id_customer').value && $f('po_customer').value && parseNum($f('nilai_kwitansi').value) > 0); }

    function refresh() {
        var val = parseNum($f('nilai_kwitansi').value);
        Array.prototype.forEach.call(document.querySelectorAll('.jv'), function (c) { c.textContent = U.en(val); });
        $f('jtd').textContent = $f('jtk').textContent = U.en(val);
        var opt = $f('po_customer').options[$f('po_customer').selectedIndex];
        var spk = opt && opt.value ? (opt.getAttribute('data-spk-no') || opt.value) : '';
        var sfx = ' - ' + $f('preview_no_kwitansi').value + (spk ? ' - ' + spk : '');
        $f('jurnal_ket_1').textContent = 'Piutang Dagang Usaha' + sfx;
        $f('jurnal_ket_2').textContent = 'Deposit Customer' + sfx;
        Array.prototype.forEach.call(document.querySelectorAll('.jt'), function (c) { c.textContent = U.fdate($f('tanggal_kwitansi').value); });
        Array.prototype.forEach.call(document.querySelectorAll('.jc'), function (c) { c.textContent = $f('display_company').value; });
        var ok = complete();
        $f('btn_submit').disabled = !ok;
        $f('save_hint').textContent = ok ? 'Jurnal seimbang.' : 'Lengkapi tanggal, customer, SPK, dan nilai deposit.';
    }

    $f('nilai_kwitansi').onchange = function () { var v = parseNum(this.value); this.value = v ? U.idn(v) : ''; refresh(); };
    $f('nilai_kwitansi').oninput = refresh;
    $f('tanggal_kwitansi').onchange = function () {
        refresh();
        if (!this.value) return;
        post(URL_PREVIEW, {tanggal_kwitansi: this.value}, function (r) { if (r && r.status === 1 && r.no_kwitansi) { $f('preview_no_kwitansi').value = r.no_kwitansi; refresh(); } });
    };

    $f('select_customer').onchange = function () {
        var opt = this.options[this.selectedIndex];
        $f('id_customer').value = this.value; $f('nm_customer').value = opt ? (opt.getAttribute('data-name') || '') : '';
        $f('id_company').value = $f('nm_company').value = $f('display_company').value = '';
        var sel = $f('po_customer');
        sel.innerHTML = '<option value="">- Pilih SPK -</option>'; sel.disabled = true;
        if (!this.value) { $f('spk_hint').textContent = 'Pilih customer dulu untuk lihat SPK'; refresh(); return; }
        post(URL_SPK, {id_customer: this.value, nm_customer: $f('nm_customer').value}, function (r) {
            var list = r && r.status === 1 && r.data ? r.data : [];
            list.forEach(function (item) {
                var no = item.no_spk ? item.no_spk : item.id_spk_penawaran;
                var o = document.createElement('option');
                o.value = item.id_spk_penawaran; o.textContent = no + ' — ' + (item.nm_project || '');
                o.disabled = !item.id_company || !item.nm_company;
                o.setAttribute('data-company-id', item.id_company || ''); o.setAttribute('data-company-name', item.nm_company || ''); o.setAttribute('data-spk-no', no);
                sel.appendChild(o);
            });
            sel.disabled = !list.length;
            $f('spk_hint').textContent = list.length ? 'SPK diambil dari data SPK customer' : 'Customer ini belum punya SPK terdaftar.';
            refresh();
        });
        refresh();
    };

    $f('po_customer').onchange = function () {
        var o = this.options[this.selectedIndex];
        $f('id_company').value = o.getAttribute('data-company-id') || '';
        $f('nm_company').value = $f('display_company').value = o.getAttribute('data-company-name') || '';
        refresh();
    };

    var saving = false;
    $f('form_kwitansi').onsubmit = function (e) {
        e.preventDefault();
        if (saving || !complete()) return;
        var no = $f('preview_no_kwitansi').value;
        U.confirmBox('Konfirmasi', 'Simpan Kwitansi Deposit ' + no + '?', function () {
            saving = true; $f('btn_submit').disabled = true;
            var data = {};
            Array.prototype.forEach.call(this_form().elements, function (el) { if (el.name) data[el.name] = el.name === 'nilai_kwitansi' ? String(parseNum(el.value)) : el.value; });
            post(this_form().getAttribute('action'), data, function (r) {
                saving = false;
                if (r && r.status === 1) {
                    try { sessionStorage.setItem('mk_flash', 'Kwitansi Deposit ' + (r.no_kwitansi || no) + ' tersimpan.'); } catch (err) {}
                    window.location.href = URL_LIST;
                } else {
                    refresh();
                    U.toast((r && r.msg) || 'Gagal menyimpan: terjadi kesalahan sistem.');
                }
            });
        });
    };
    function this_form() { return $f('form_kwitansi'); }
    refresh();
})();
</script>
