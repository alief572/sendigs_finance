<?php $escape=function($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}; ?>
<div id="consulting-receipt">
    <p>PPh aktual diisi sesuai bukti pada invoice konsultasi baru. Invoice lama tetap mengikuti pilihan PPh penerimaan.</p>
    <input type="hidden" name="no_inv" value="<?= count($rows) ?>"><input type="hidden" name="id_alokasi" value="<?= $escape($id_alokasi) ?>">
    <input type="hidden" name="allocation_fingerprint" value="<?= $escape($allocation_fingerprint) ?>"><input type="hidden" name="pph23_dipotong" value="<?= $escape($choice) ?>"><input type="hidden" name="ppn_dipotong" value="<?= $escape($ppn_choice) ?>">
    <input type="hidden" name="kontrol" value="0"><input type="hidden" name="total_debit_jurnal" value="0"><input type="hidden" name="total_kredit_jurnal" value="0">
    <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Invoice</th><th>Company</th><th>Total Tagihan</th><th>Saldo Piutang</th><th>Penerimaan</th><th>Admin</th><th>PPh Aktual</th><th>Sisa Piutang</th></tr></thead><tbody>
    <?php foreach($rows as $index=>$context): $n=$index+1;$invoice=$context['invoice']; ?>
        <tr><td><?= $escape($invoice['no_invoice']) ?><input type="hidden" name="id_inv_<?= $n ?>" value="<?= $escape($invoice['id']) ?>"><input type="hidden" name="receipt_fingerprint_<?= $n ?>" value="<?= $escape($context['fingerprint']) ?>"></td>
            <td><?= $escape($context['owner']['nm_company']) ?></td><td class="text-right"><?= number_format((float)$invoice['tagihan_ppn_jurnal'],2) ?></td><td class="text-right"><?= number_format((float)$invoice['saldo_piutang'],2) ?></td>
            <?php foreach(array('penerimaan'=>'Penerimaan','biaya_admin'=>'Biaya admin') as $field=>$label): ?><td><input type="number" class="form-control" name="<?= $field ?>_<?= $n ?>" aria-label="<?= $escape($label.' '.$invoice['no_invoice']) ?>" min="0" step="0.01" value="0.00" required></td><?php endforeach; ?>
            <td><?php if($context['new']): ?><input type="number" class="form-control" name="pph_actual_<?= $n ?>" aria-label="<?= $escape('PPh aktual '.$invoice['no_invoice']) ?>" min="0" step="0.01" value="0.00" required><?php else: ?>Aturan invoice lama<?php endif; ?></td><td class="text-right" data-remaining="<?= $n ?>"><?= number_format((float)$invoice['saldo_piutang'],2) ?></td>
        </tr>
    <?php endforeach; ?></tbody></table></div>
    <p>Sisa alokasi bank: <strong><?= number_format(($allocation['capacity']-$allocation['used'])/100,2) ?></strong></p>
    <h4>Jurnal Penerimaan</h4><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Company</th><th>COA</th><th>Nama COA</th><th>Deskripsi</th><th>Debit</th><th>Credit</th></tr></thead><tbody id="consulting-receipt-journal"></tbody><tfoot><tr><th colspan="4">Balancing</th><th id="consulting-receipt-debit"></th><th id="consulting-receipt-credit"></th></tr></tfoot></table></div>
    <p id="consulting-receipt-error" role="status" aria-live="polite"></p>
</div>
<script src="<?= base_url('assets/js/invoicing/consulting.js?v='.filemtime(FCPATH.'assets/js/invoicing/consulting.js')) ?>"></script>
<script>
(function(){
    'use strict';
    var rows=<?= json_encode(array_map(function($r){return array('invoice'=>$r['invoice']['no_invoice'],'balance'=>Invoice_calculation::cents($r['invoice']['saldo_piutang']),'new'=>$r['new'],'owner'=>$r['owner']['nm_company'],'accounts'=>$r['accounts'],'remainingPph'=>$r['remaining_pph']);},$rows),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
    var available=<?= $allocation['capacity']-$allocation['used'] ?>,choice=<?= json_encode($choice) ?>,container=document.getElementById('consulting-receipt');
    function money(cents){return(cents/100).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});}
    function recalc(){
        var body=document.getElementById('consulting-receipt-journal');body.textContent='';var debit=0,credit=0,cashTotal=0,error='';
        rows.forEach(function(row,index){var n=index+1;
            try{
                var cash=ConsultingInvoice.cents(container.querySelector('[name="penerimaan_'+n+'"]').value),admin=ConsultingInvoice.cents(container.querySelector('[name="biaya_admin_'+n+'"]').value),pph=row.new?ConsultingInvoice.cents(container.querySelector('[name="pph_actual_'+n+'"]').value):0;
                var adjustment=!row.new&&choice==='N'?Math.min(row.remainingPph,cash):0,reduction=cash+admin+pph-adjustment,remaining=row.balance-reduction;
                if(remaining<0)throw new Error('Penerimaan melebihi saldo '+row.invoice+'.');
                cashTotal+=cash;container.querySelector('[data-remaining="'+n+'"]').textContent=money(remaining);
                [['bank',cash,0],['admin',admin,0],['pph',pph,adjustment],['receivable',0,reduction]].forEach(function(line){if(!line[1]&&!line[2])return;var a=row.accounts[line[0]],tr=document.createElement('tr');[row.owner,a.code,a.name,a.name+' - '+row.invoice,money(line[1]),money(line[2])].forEach(function(text){var td=document.createElement('td');td.textContent=text;tr.appendChild(td);});body.appendChild(tr);debit+=line[1];credit+=line[2];});
            }catch(e){error=e.message;}
        });
        if(cashTotal>available)error='Penerimaan melebihi sisa alokasi bank.';
        container.querySelector('[name=kontrol]').value=error?'1':'0';container.querySelector('[name=total_debit_jurnal]').value=debit/100;container.querySelector('[name=total_kredit_jurnal]').value=credit/100;
        document.getElementById('consulting-receipt-debit').textContent=money(debit);document.getElementById('consulting-receipt-credit').textContent=money(credit);document.getElementById('consulting-receipt-error').textContent=error;
    }
    container.addEventListener('input',recalc);recalc();
}());
</script>
