<?php
require __DIR__.'/Expense_report_updated_test.php';
$data=(object)['no_doc'=>'ER-BANK','id_kasbon'=>'KS-BANK','id_bank_pengembalian'=>'18','transfer_coa_bank'=>'1101-02-01','status'=>'2','lebih_bayar'=>185000,'transfer_jumlah'=>185000,'transfer_tanggal'=>'2026-10-28'];
$kasbon=null;$is_readonly=true;
$bank_options=[(object)['id'=>'18','nama'=>'PT. Sentral Tehnologi Managemen','rekening'=>'436.300.5287','coa_bank'=>'1101-02-01','nama_bank'=>'BCA'],
    (object)['id'=>'21','nama'=>'PT. SENTRAL SUSTAINABILITY CONSULTING','rekening'=>'436.400.0300','coa_bank'=>'1101-02-01','nama_bank'=>'BCA']];
function selected_return_options($html){preg_match_all('/<option\b[^>]*\bselected[^>]*>/',$html,$matches);return $matches[0];}
ob_start();include dirname(__DIR__).'/views/form_pengembalian.php';$html=ob_get_clean();$selected=selected_return_options($html);
testCheck(count($selected)===1&&strpos($selected[0],'value="18"')!==false&&strpos($selected[0],'data-company="STM"')!==false,'shared COA selects saved STM bank exactly once');
$data->id_bank_pengembalian='21';ob_start();include dirname(__DIR__).'/views/form_pengembalian.php';$html=ob_get_clean();$selected=selected_return_options($html);
testCheck(count($selected)===1&&strpos($selected[0],'value="21"')!==false&&strpos($selected[0],'data-company="SUSTAIN"')!==false,'explicit Sustain bank remains Sustain');
$data->id_bank_pengembalian='';ob_start();include dirname(__DIR__).'/views/form_pengembalian.php';$html=ob_get_clean();
testCheck(count(selected_return_options($html))===0,'ambiguous legacy COA does not pretend a bank was selected');
echo "PASS Return bank view regression\n";
