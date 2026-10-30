<?php
require __DIR__ . '/consulting_fixture.php';

function contractFixture($bank = '218330833.33', $tax = 'PKP', $choice = 'Y') {
    $db = createFixtures();
    $db->alloc_details[0]['nominal_debit'] = $bank;
    $db->alloc_splits[0]['nominal'] = $bank;
    $gross = $tax === 'PKP' ? 22200000000 : 20000000000;
    $db->invoices[] = makeInvoice(1, 'CONCEPT-1', $tax, 20000000000, 18333333333, $tax === 'PKP' ? 2200000000 : 0, 366666667, $gross, $gross);
    $ci = new MockCi($db);
    $engine = new Consulting_receipt($ci);
    $ref = new ReflectionProperty('Consulting_receipt', 's'); $ref->setAccessible(true);
    $ref->setValue($engine, new CustomService($ci));
    $preview = $engine->preview(array('choose_inv'=>array(1), 'id_alokasi'=>100, 'pph23_dipotong'=>$choice));
    return array($db,$engine,$preview,contractPost($preview, $choice));
}
function contractPost($preview, $choice = 'Y') {
    $p = array('no_inv'=>(string)count($preview['rows']), 'id_alokasi'=>100, 'allocation_fingerprint'=>$preview['allocation_fingerprint'], 'pph23_dipotong'=>$choice);
    foreach ($preview['rows'] as $i=>$r) {
        $n=$i+1;
        $p['id_inv_'.$n]=$r['invoice']['id'];
        $p['receipt_fingerprint_'.$n]=$r['fingerprint'];
        $p['penerimaan_'.$n]=$r['invoice']['saldo_piutang'];
        $p['pph_actual_'.$n]=Invoice_calculation::decimal($r['default_pph']);
        $p['biaya_admin_'.$n]='2500.00'; $p['sisa_piutang_'.$n]='0.00';
    }
    return $p;
}
function rejected($res, $message) { checkAssert($res['status'] === 0, $message . ': ' . json_encode($res)); }

