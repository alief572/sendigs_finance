<?php
$escape = function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
if (!empty($error)) {
    echo '<p>'.$escape($error).'</p>';
    return;
}

// Reuse the established print layout without rereading a mutable TOP source.
$data_invoice = (object)$invoice;
foreach (array('nm_customer', 'address', 'no_invoice', 'no_po') as $field) {
    $data_invoice->$field = $escape($invoice[$field] ?? '');
}
$description = trim((string)($invoice['print_keterangan'] ?? ''));
if ($description === '') $description = $snapshot['top']['desc_payment'] ?? '';
if (($invoice['tax_treatment'] ?? '') === 'KB') $description .= "\nKawasan Berikat";
$data_invoice->print_keterangan = nl2br($escape($description));
$data_actual_plan_tagih = (object)array('nominal_payment' => $invoice['total_nominal']);
$id_company = (string)$snapshot['owner']['id'];
$invoice_print_totals = array('vat' => $invoice['ppn_jurnal'], 'gross' => $invoice['tagihan_ppn_jurnal']);
$invoice_print_decimals = 2;
require __DIR__.'/print_invoice.php';
