<?php
// Header/baris Excel Report Pembayaran: tgl bayar, voucher BBK, urutan kolom template, tab lain tidak berubah.
require __DIR__.'/Query_test.php';
require dirname(__DIR__).'/libraries/Jurnal_pembayaran_xlsx.php';
require_once APPPATH.'libraries/PHPExcel.php';
$db->query('ALTER TABLE payment_approve ADD COLUMN tgl_bayar TEXT');
$db->query('DELETE FROM tr_jurnal');
$db->query('DELETE FROM payment_approve');
$db->insert('payment_approve',['id'=>'A1','id_payment'=>'BK--0926-0015','no_doc'=>'DPM-1','tipe'=>'Cash','tgl_bayar'=>'2026-09-20']);
$db->insert('payment_approve',['id'=>'A2','id_payment'=>'BK--0926-0016','no_doc'=>'DPM-2','tipe'=>'Cash','tgl_bayar'=>null]);
journal('Payment','1','BK--0926-0015','7','100.00','0.00');
journal('Payment','1','BK--0926-0016','7','0.00','100.00');

class Bbk_model extends Jurnal_all_model {
    protected function paymentExportInfo($vouchers){
        $info=parent::paymentExportInfo($vouchers);
        if(isset($info['BK--0926-0015']))$info['BK--0926-0015']['bbk']='2026-STM/BBK/IX/03';
        return $info;
    }
}
$model=new Bbk_model();$model->db=$db;
$rows=$model->export_payment_rows(['tab'=>'PAY','year'=>2026]);
check(count($rows)===4,'export keeps every accounting line');
$byVoucher=[];foreach($rows as $r)$byVoucher[$r['header']['display_no_transaksi']]=$r['line'];
check($byVoucher['BK--0926-0015']['tgl_bayar']==='2026-09-20'&&$byVoucher['BK--0926-0015']['bbk_voucher']==='2026-STM/BBK/IX/03','payment date and BBK voucher resolved');
check($byVoucher['BK--0926-0016']['tgl_bayar']===''&&$byVoucher['BK--0926-0016']['bbk_voucher']==='-','no payment date / BBK keeps placeholder');

// Tanpa tabel transfer (sqlite fixture) resolver tetap aman: BBK '-', tanggal tetap terbaca.
$plain=new Jurnal_all_model();$plain->db=$db;
$plainRows=$plain->export_payment_rows(['tab'=>'PAY','year'=>2026]);
check(count($plainRows)===4,'resolver without transfer tables still exports');
foreach($plainRows as $r)check($r['line']['bbk_voucher']==='-','BBK placeholder when transfer tables are missing');

$tick="'";
$rows[0]['line']['keterangan']='=HYPERLINK("x")';
$rows[0]['line']['debit']='1234.50';
$binary=Jurnal_pembayaran_xlsx::render($rows,Jurnal_all_model::TITLES,'Report_Jurnal_20261126_112425');
$file=tempnam(sys_get_temp_dir(),'xt');file_put_contents($file,$binary);
check(substr($binary,0,2)==='PK','real xlsx zip container');
restore_error_handler();$sheet=PHPExcel_IOFactory::load($file)->getActiveSheet();@unlink($file);
$expected=['Status','Jenis Jurnal','Tgl Jurnal/Bayar','No. Jurnal Accounting (Transaksi)','Tgl Posting','No. Transaksi','No. Referensi','No. Voucher','Kategori Transaksi','Company','Divisi','No. COA','Nama COA','Keterangan','Debit','Kredit'];
foreach($expected as $i=>$title)check($sheet->getCell(PHPExcel_Cell::stringFromColumnIndex($i).'1')->getValue()===$title,'header '.$title);
check($sheet->getCell('Q1')->getValue()===null,'no extra columns');
$data=[];foreach([2,3,4,5] as $n)$data[$sheet->getCell('F'.$n)->getValue()]??=$n;
$n=$data['BK--0926-0015'];
check($sheet->getCell('C'.$n)->getValue()==='2026-09-20','Tgl Jurnal/Bayar uses payment date');
check($sheet->getCell('H'.$n)->getValue()==='2026-STM/BBK/IX/03','No. Voucher column');
check($sheet->getCell('N'.$n)->getValue()==='=HYPERLINK("x")'&&$sheet->getCell('N'.$n)->getDataType()==='s','formula text stays a string cell');
check((float)$sheet->getCell('O'.$n)->getValue()===1234.5&&$sheet->getCell('O'.$n)->getDataType()==='n','debit numeric');
$m=$data['BK--0926-0016'];
check($sheet->getCell('C'.$m)->getValue()===$rows[2]['line']['tgl_jurnal'],'fallback to journal date');
check($sheet->getCell('H'.$m)->getValue()==='-','voucher placeholder');
echo "PASS Payment export xlsx: {$checks} assertions\n";
