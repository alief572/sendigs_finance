<?php
// Id approval kelompok ini yang sama dengan voucher kelompok Payment lain (sudah posted) bukan bukti bahwa kelompok ini sudah diposting.
require __DIR__.'/Payment_reference_test.php';
$foreignBase=$checks;

const FV='BK--1026-0004';   // voucher kelompok yang diuji (approval APP-1)
const FR='BK--0926-0011';   // id approval APP-R milik kelompok ini, sekaligus voucher pembayaran LAIN
// Kelompok diuji: approval APP-1 (voucher FV, jurnal berkunci FV) ditambah approval transport FR (voucher FV) yang id-nya = voucher pembayaran lain.
function foreignFixture($ownerSts='1', $ownerType='Payment')
{
    $ci=fixture();$db=$ci->db;
    $db->update('tr_jurnal',['no_transaksi'=>FV]);                       // jurnal kelompok berkunci voucher (bukan id approval)
    $db->insert('payment_approve',['id'=>FR,'id_payment'=>FV,'no_doc'=>'RQ-FR','tipe'=>'transport']);
    // pembayaran LAIN: approval OTH dengan voucher = FR, jurnalnya sudah diposting dan accounting-nya berkunci FR
    $db->insert('payment_approve',['id'=>'OTH','id_payment'=>FR,'no_doc'=>'PCH-OTH','tipe'=>'Cash']);
    $db->insert('tr_payment_paid',['id'=>FR]);
    foreach([[300,0],[0,300]] as $i=>$m)$db->insert('tr_jurnal',['id'=>50+$i,'no_transaksi'=>FR,'jenis_transaksi'=>$ownerType,'sts'=>$ownerSts,'id_company'=>'7','nm_company'=>'Company','tgl_jurnal'=>'2026-09-05','coa'=>'5101','nm_coa'=>'Biaya','debit'=>$m[0],'kredit'=>$m[1],'keterangan'=>'Pembayaran lain']);
    // data nyata: pemilik sudah direkey ke voucher dan punya alias ke approval-nya sendiri (kalau tidak, resolve() pemilik ambigu)
    $db->query('CREATE TABLE tr_jurnal_payment_reference_aliases (run_id TEXT,journal_id INTEGER,original_no_transaksi TEXT,voucher TEXT,approval_ids TEXT,active INTEGER,created_at TEXT,PRIMARY KEY(run_id,journal_id))');
    foreach([50,51] as $jid)$db->insert('tr_jurnal_payment_reference_aliases',['run_id'=>'R1','journal_id'=>$jid,'original_no_transaksi'=>'OTH','voucher'=>FR,'approval_ids'=>json_encode(['OTH']),'active'=>1,'created_at'=>'2026-11-05 10:00:00']);
    $acc=$ci->load->dbs['accounting_stm'];
    $acc->insert('japh',['nomor'=>'101BK2600011','tgl'=>'2026-09-05','jml'=>'3.00','kdcab'=>'101','jenis_reff'=>'BUK','no_reff'=>FR,'jenis_ap'=>'V','note'=>'Payment '.FR]);
    $acc->insert('jurnal',['tipe'=>'BUK','nomor'=>'101BK2600011','no_reff'=>FR,'no_perkiraan'=>'5101','debet'=>'3','kredit'=>'0']);
    return $ci;
}
function inspectMsg($ci){ try{ (new Payment_reference($ci))->inspect(FV,true,true); return 'OK'; }catch(Throwable $e){ return $e->getMessage(); } }

// 1. pemilik id approval itu sudah posted -> bukan duplikat, kelompok bisa diposting
$ci=foreignFixture();
check(inspectMsg($ci)==='OK','id approval = voucher pembayaran lain yang sudah posted tidak memblokir');
// 1b. pemilik berjenis Refill Pettycash (kasus BK--0826-0020)
$ci=foreignFixture('1','Refill Pettycash');
check(inspectMsg($ci)==='OK','pemilik Refill Pettycash yang sudah posted juga tidak memblokir');
// 2. pemilik masih pending -> tetap diblokir (kasus BK--0925-0004)
$ci=foreignFixture('0');
check(strpos(inspectMsg($ci),'sudah ada pada accounting')!==false,'pemilik belum posted: tetap diblokir');
// 3. no_reff accounting = no_doc approval -> tetap diblokir (perilaku lama)
$ci=foreignFixture();$ci->load->dbs['accounting_stm']->insert('jurnal',['no_reff'=>'RQ-FR']);
check(strpos(inspectMsg($ci),'sudah ada pada accounting')!==false,'no_reff = no_doc approval tetap diblokir');
// 4. id approval kelompok ini yang bukan voucher siapa pun dan sudah ada di accounting -> tetap diblokir
$ci=foreignFixture();$ci->db->insert('payment_approve',['id'=>'BK--0926-0099','id_payment'=>FV,'no_doc'=>'RQ-99','tipe'=>'transport']);
$ci->load->dbs['accounting_stm']->insert('jurnal',['no_reff'=>'BK--0926-0099']);
check(strpos(inspectMsg($ci),'sudah ada pada accounting')!==false,'id approval milik sendiri di accounting tetap diblokir');
// 4b. approval lain memakai FR sebagai voucher TAPI jurnalnya bukan milik pemilik posted (tidak ada jurnal sts=1) -> tetap diblokir
$ci=foreignFixture();$ci->db->query("DELETE FROM tr_jurnal WHERE no_transaksi='".FR."'");
check(strpos(inspectMsg($ci),'sudah ada pada accounting')!==false,'tanpa jurnal posted pemilik, referensi dipertahankan');

// 5. posting penuh pada kasus 1: menulis voucher sendiri, tidak menyentuh baris milik pembayaran lain
$ci=foreignFixture();$acc=$ci->load->dbs['accounting_stm'];
$beforeOwner=$acc->get_where('jurnal',['no_reff'=>FR])->result_array();$beforeHdr=$acc->get_where('japh',['no_reff'=>FR])->result_array();
$id=$ci->db->get_where('tr_jurnal',['no_transaksi'=>FV])->row_array()['id'];
$res=(new Payment_posting($ci))->execute($id);
check(!empty($res['save'])||!empty($res['status']),'posting kelompok berhasil');
check($acc->get_where('japh',['no_reff'=>FV])->num_rows()===1&&$acc->get_where('jurnal',['no_reff'=>FV])->num_rows()===2,'accounting ditulis dengan voucher sendiri');
check($acc->get_where('jurnal',['no_reff'=>FR])->result_array()===$beforeOwner&&$acc->get_where('japh',['no_reff'=>FR])->result_array()===$beforeHdr,'baris accounting milik pembayaran lain tidak berubah');

echo 'PASS Payment foreign voucher reference: '.($checks-$foreignBase)." assertions\n";
