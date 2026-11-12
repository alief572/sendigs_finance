<?php
// Detail kelompok harus sama dengan daftar: company kelompok = MAX(company), bukan company baris anchor MIN(id) yang bisa kosong.
require __DIR__.'/Query_test.php';

function groupRows($number, array $companies) {
    global $db, $id;
    $first = null;
    foreach ($companies as $i => $c) {
        $db->insert('tr_jurnal', ['id'=>++$id,'no_jurnal'=>'SOURCE-'.$id,'no_transaksi'=>$number,'jenis_transaksi'=>'Payment','sts'=>'0',
            'id_company'=>$c[0],'nm_company'=>$c[1],'tgl_jurnal'=>'2026-10-14','coa'=>'5101-01','nm_coa'=>'Biaya khusus',
            'debit'=>$i===0?'100.00':'0','kredit'=>$i===0?'0':'100.00','keterangan'=>'Detail aman','nm_divisi'=>'Finance']);
        if ($first === null) $first = $id;
    }
    return $first;
}
function detailCompany($model, $anchorId) {
    try { return $model->detail($anchorId, false)['header']['nm_company']; }
    catch (InvalidArgumentException $e) { return 'ERR:'.$e->getMessage(); }
}
$model = new Jurnal_all_model(); $model->db = $db;

// 1. baris pertama tanpa company, baris lain STM: detail terbuka dan company = STM (sama dengan MAX di daftar)
$a = groupRows('PAY-NOCOMP-1', [['',''], ['7','STM']]);
check(detailCompany($model, $a) === 'STM', 'anchor tanpa company memakai company kelompok');

// 2. dua company, anchor kosong: MAX leksikal (VUCA > STM), persis seperti MAX(nm_company) di groups()
$b = groupRows('PAY-NOCOMP-2', [['',''], ['7','STM'], ['4','VUCA']]);
check(detailCompany($model, $b) === 'VUCA', 'dua company: sama dengan MAX(nm_company) di daftar');

// 3. anchor yang sudah punya company tidak diganti
$c = groupRows('PAY-WITHCOMP', [['7','STM'], ['4','VUCA']]);
check(detailCompany($model, $c) === 'STM', 'anchor ber-company tidak diubah');

// 4. semua baris tanpa company: tetap ditolak seperti sebelumnya
$d = groupRows('PAY-ALLEMPTY', [['',''], ['','']]);
check(strpos(detailCompany($model, $d), 'Dokumen sumber jurnal tidak tersedia') !== false, 'kelompok tanpa company sama sekali tetap ditolak');

echo "PASS Jurnal All detail company kelompok\n";
