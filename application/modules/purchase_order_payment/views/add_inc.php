<?php
$req_payment_po = ($total_incoming - $total_dp);
if ($req_payment_po < 1) {
    $req_payment_po = 0;
}
?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<input type="hidden" name="tipe_req" value="inc">
<input type="hidden" name="no_po" id="" value="<?= implode(',', $results['no_po']) ?>" class="form-control form-control-sm">
<!-- <input type="hidden" name="tipe_incoming" value="<?= $tipe_incoming ?>"> -->
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nama Supplier</label>
            <input type="hidden" name="kode_supplier" value="<?= $results['kode_supplier'] ?>">
            <input type="text" name="nama_supplier" id="" class="form-control form-control-sm nama_supplier" value="<?= $results['nm_supplier'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Currency</label>
            <input type="text" name="currency" id="" class="form-control form-control-sm currency" value="<?= $results['currency'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Kurs</label>
            <input type="text" name="kurs" id="" value="1" class="form-control form-control-sm text-right auto_num">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nomor Incoming</label>
            <input type="text" name="nomor_po" id="" class="form-control form-control-sm nomor_po" value="<?= $results['incoming_no'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Receive Invoice Date</label>
            <input type="date" name="invoice_date" id="" class="form-control form-control-sm" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nomor Invoice</label>
            <input type="text" name="nomor_invoice" id="" class="form-control form-control-sm nomor_invoice" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Value DP</label>
            <input type="text" name="value_dp" id="" class="form-control form-control-sm text-right value_dp" value="<?= number_format($results['value_dp'], 2) ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nomor Faktur Pajak</label>
            <input type="text" name="nomor_faktur_pajak" id="" class="form-control form-control-sm nomor_faktur_pajak">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Total Incoming</label>
            <input type="text" name="total_pembelian" id="" class="form-control form-control-sm text-right total_pembelian" value="<?= number_format($results['total_invoice'], 2) ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nilai Disc</label>
            <input type="text" name="nilai_disc" id="" class="form-control form-control-sm text-right nilai_disc auto_num" value="<?= number_format($results['nilai_disc'], 2) ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nilai PPN</label>
            <input type="text" name="nilai_ppn" id="" class="form-control form-control-sm text-right nilai_ppn auto_num" value="<?= number_format($results['nilai_ppn'], 2) ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Total Invoice</label>
            <input type="text" name="total_invoice" id="" class="form-control form-control-sm text-right total_invoice auto_num" value="<?= number_format($results['total_invoice'], 2) ?>" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Request Payment PO</label>
            <input type="text" name="req_payment_po" id="" class="form-control form-control-sm text-right req_payment_po auto_num">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Upload Invoice</label>
            <input type="file" name="upload_invoice" id="" class="form-control form-control-sm upload_invoice">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Notes</label>
            <input type="text" name="notes" id="" class="form-control form-control-sm notes" value="">
        </div>
    </div>
    <div class="col-md-6">
        <b>Informasi Bank :</b>
        <div class="form-group">
            <label for="">Bank</label>
            <input type="text" name="bank" id="" class="form-control form-control-sm" placeholder="- Bank -">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Invoice Date</label>
            <input type="date" name="invoice_date_real" id="" class="form-control form-control-sm invoice_date_real">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">No. Bank</label>
            <input type="text" name="no_bank" id="" class="form-control form-control-sm" placeholder="- No. Bank -">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Tanggal Faktur Pajak</label>
            <input type="date" name="tanggal_faktur_pajak" id="" class="form-control form-control-sm tanggal_faktur_pajak">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nama</label>
            <input type="text" name="nm_acc_bank" id="" class="form-control form-control-sm" placeholder="- Nama Acc Bank -">
        </div>
    </div>

    <?php
    foreach ($results['no_incoming'] as $id_incoming) {
    ?>
        <div class="col-md-12">
            <h4>Detail Incoming : <?= $id_incoming ?></h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Nomor PO</th>
                        <th class="text-center">Produk</th>
                        <th class="text-center">Qty PO</th>
                        <th class="text-center">Qty Incoming</th>
                        <th class="text-center">Harga Satuan</th>
                        <th class="text-center">Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    // $this->db->select('a.*, b.qty as qty_po, b.hargasatuan, c.no_surat');
                    // $this->db->from('tr_incoming_check_detail a');
                    // $this->db->join('dt_trans_po b', 'b.id = a.id_po_detail');
                    // $this->db->join('tr_purchase_order c', 'c.no_po = b.no_po');
                    // $this->db->where('a.kode_trans', $id_incoming);
                    // $get_detail_inc = $this->db->get()->result();

                    $get_detail_inc = $this->db->query("
                        SELECT
                            e.qty_oke as qty_order,   
                            b.qty as qty_po,
                            b.hargasatuan as hargasatuan,
                            c.no_surat as no_surat,
                            d.nama as nm_material
                        FROM
                            tr_incoming_check_detail a
                            LEFT JOIN dt_trans_po b ON b.id = a.id_po_detail
                            LEFT JOIN tr_purchase_order c ON c.no_po  = b.no_po
                            LEFT JOIN new_inventory_4 d ON d.code_lv4 = a.id_material 
                            JOIN tr_checked_incoming_detail e ON e.kode_trans = a.kode_trans AND e.id_material = a.id_material
                        WHERE
                            a.kode_trans = '" . $id_incoming . "'
                        GROUP BY a.id
                        
                        UNION ALL

                        SELECT 
                            a.qty_oke as qty_order,
                            b.qty as qty_po,
                            b.hargasatuan as hargasatuan,
                            c.no_surat as no_surat,
                            d.stock_name as nm_material
                        FROM
                            warehouse_adjustment_detail a
                            LEFT JOIN dt_trans_po b ON b.id = a.no_ipp
                            LEFT JOIN tr_purchase_order c ON c.no_po  = b.no_po
                            LEFT JOIN accessories d ON d.id = a.id_material 
                            LEFT JOIN warehouse_adjustment e ON e.kode_trans = a.kode_trans
                        WHERE
                            a.kode_trans = '" . $id_incoming . "' AND e.category = 'incoming stok'
                        GROUP BY a.id

                        UNION ALL

                        SELECT
                            a.qty_oke as qty_order,
                            a.qty_oke as qty_po,
                            c.harga as hargasatuan,
                            d.no_pr as no_surat,
                            d.nm_barang as nm_material
                        FROM
                            warehouse_adjustment_detail a
                            LEFT JOIN warehouse_adjustment b ON b.kode_trans = a.kode_trans
                            LEFT JOIN tr_pr_detail_kasbon c ON c.id_kasbon = b.no_ipp AND c.id_detail = a.id_material
                            LEFT JOIN rutin_non_planning_detail d ON d.id = a.id_material
                        WHERE
                            a.kode_trans = '".$id_incoming."' AND b.category = 'incoming non rutin'
                        GROUP BY a.id

                        UNION ALL

                        SELECT 
                            a.qty_oke as qty_order,
                            b.qty as qty_po,
                            b.hargasatuan as hargasatuan,
                            c.no_surat as no_surat,
                            a.nm_material as nm_material
                        FROM
                            warehouse_adjustment_detail a
                            LEFT JOIN tr_purchase_order c ON c.no_surat = a.no_ipp
                            LEFT JOIN dt_trans_po b ON b.no_po = c.no_po AND b.namamaterial = a.nm_material
                            LEFT JOIN warehouse_adjustment e ON e.kode_trans = a.kode_trans
                        WHERE
                            a.kode_trans = '" . $id_incoming . "' AND e.category = 'incoming asset'
                        GROUP BY a.id
                     ")->result();
                    if (!$get_detail_inc) {
                        print_r($this->db->error($get_detail_inc));
                        exit;
                    }

                    $grand_total = 0;

                    $no = 1;
                    foreach ($get_detail_inc as $item) {
                        echo '<tr>';
                        echo '<td class="text-center">' . $no . '</td>';
                        echo '<td class="text-center">' . $item->no_surat . '</td>';
                        echo '<td class="text-center">' . $item->nm_material . '</td>';
                        echo '<td class="text-center">' . number_format($item->qty_po) . '</td>';
                        echo '<td class="text-center">' . number_format($item->qty_order) . '</td>';
                        echo '<td class="text-right">' . number_format($item->hargasatuan, 2) . '</td>';
                        echo '<td class="text-right">' . number_format($item->qty_order * $item->hargasatuan, 2) . '</td>';
                        echo '</tr>';
                        $no++;

                        $grand_total += ($item->qty_order * $item->hargasatuan);
                    }
                    ?>
                </tbody>
                <tbody>
                    <tr>
                        <td colspan="6" align="right">
                            <b>Grand Total</b>
                        </td>
                        <td align="right">
                            <b>
                                <?= number_format($grand_total, 2) ?>
                            </b>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php
    }
    ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script>
    $(document).ready(function() {
        $('.select2_modal').select2({
            width: '100%',
            dropdownParent: $('#dialog-popup')
        });
    });
    $('.auto_num').autoNumeric('init');
    $(document).on('change', '.persen_dp', function() {
        var total_pembelian = $('.total_pembelian').val();
        if (total_pembelian == '' || total_pembelian == null) {
            total_pembelian = 0;
        } else {
            total_pembelian = total_pembelian.split(',').join('');
            total_pembelian = parseFloat(total_pembelian);
        }

        var persen_dp = parseFloat($(this).val());

        var value_dp = (total_pembelian * persen_dp / 100);

        $('.value_dp').val(value_dp.toLocaleString());
    });
</script>