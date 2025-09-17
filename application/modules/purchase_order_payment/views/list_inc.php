<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="<?= base_url('assets/chosen_v1.8.7/chosen.min.css') ?>">
<div class="req_payment_inc" style="margin-top: 2vh;">
    <b>Request Payment Incoming Approved</b>
    <div class="row">
        <div class="col-md-4" style="margin-top: 20px;">
            <label for="">Supplier</label>
            <select name="supplier" id="select_supplier" class="form-control">
                <option value="">- Pilih Supplier -</option>
                <?php
                foreach ($list_supplier as $item_supp) {
                    echo '<option value="' . $item_supp->kode_supplier . '">' . $item_supp->nama . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="col-md-2" style="margin-top: 20px;">
            <button type="button" class="btn btn-sm btn-primary search_inc" style="margin-top: 20px;">
                <i class="fa fa-search"></i> Cari
            </button>
        </div>
        <div class="col-md-6 text-right" style="margin-top: 20px;">
            <a href="purchase_order_payment/check_list_inc" class="btn btn-sm btn-success rec_invoice_btn" style="margin-top: 20px;">Receive Invoice</a>
        </div>
    </div>
    <div class="col_table">
        <table class="table table-bordered table_req_pay_inc">
            <thead class="bg-blue">
                <tr>
                    <th class="text-center">No.</th>
                    <th class="text-center">No. Purchase Invoice</th>
                    <th class="text-center">No. Incoming</th>
                    <th class="text-center">No. PO</th>
                    <th class="text-center">No. Invoice</th>
                    <th class="text-center">No. Payment</th>
                    <th class="text-center">Tanggal Invoice</th>
                    <th class="text-center">Supplier</th>
                    <th class="text-center">Nominal Invoice</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                foreach ($list_inc as $item) {

                    $exp_no_po = explode(',', $item['no_po']);

                    $nm_supplier = [];

                    $no_ipp = [];
                    $this->db->select('a.no_ipp');
                    $this->db->from('tr_incoming_check a');
                    $this->db->where_in('a.kode_trans', $exp_no_po);
                    $get_no_ipp = $this->db->get()->result();
                    foreach ($get_no_ipp as $item_ipp) {
                        $no_ipp[] = $item_ipp->no_ipp;
                    }

                    $this->db->select('a.no_ipp');
                    $this->db->from('warehouse_adjustment a');
                    $this->db->where_in('a.kode_trans', $exp_no_po);
                    $get_no_ipp_warehouse = $this->db->get()->result();
                    foreach ($get_no_ipp_warehouse as $item_ipp_ware) {
                        $no_ipp[] = $item_ipp_ware->no_ipp;
                    }

                    if (count($no_ipp) > 0) {
                        $no_ipp = implode(',', $no_ipp);
                    } else {
                        $no_ipp = '';
                    }

                    $this->db->select('b.nama as nm_supplier');
                    $this->db->from('tr_purchase_order a');
                    $this->db->join('new_supplier b', 'b.kode_supplier = a.id_suplier', 'left');
                    $this->db->where_in('a.no_po', explode(',', $no_ipp));
                    $this->db->group_by('b.nama');
                    $get_nm_supplier = $this->db->get()->result();
                    foreach ($get_nm_supplier as $item_nm_supplier) {
                        $nm_supplier[] = $item_nm_supplier->nm_supplier;
                    }

                    if (count($nm_supplier) > 0) {
                        $nm_supplier = implode(', ', $nm_supplier);
                    } else {
                        $nm_supplier = '';
                    }

                    $status = '<div class="badge bg-yellow">Belum Lunas</div>';
                    // if($id_rec_invoice !== ''){
                    $get_invoice_payment = $this->db->get_where('payment_approve', ['no_doc' => $item['id'], 'status' => 2])->result();
                    if (count($get_invoice_payment) > 0) {
                        // $complete = 1;
                        // $status = '<div class="badge bg-green">Complete</div>';

                        $check_payment_approve = $this->db->get_where('payment_approve', ['no_doc' => $item['id'], 'status' => 2])->result();

                        if (count($check_payment_approve) > 0) {
                            $status = '<div class="badge bg-green">Lunas</div>';
                            $complete = 1;
                        }
                    }

                    $no_po = [];
                    $get_no_po = $this->db->query("
                        SELECT
                            c.no_surat
                        FROM
                            tr_incoming_check_detail a
                            LEFT JOIN dt_trans_po b ON b.id = a.id_po_detail
                            LEFT JOIN tr_purchase_order c ON c.no_po = b.no_po
                        WHERE
                            a.kode_trans IN ('" . str_replace(', ', ',', str_replace(",", "','", $item['no_po'])) . "')
                        GROUP BY c.no_surat

                        UNION ALL

                        SELECT
                            c.no_surat
                        FROM
                            warehouse_adjustment_detail a
                            LEFT JOIN dt_trans_po b ON b.id = a.no_ipp
                            LEFT JOIN tr_purchase_order c ON c.no_po = b.no_po
                        WHERE
                            a.kode_trans IN ('" . str_replace(', ', ',', str_replace(",", "','", $item['no_po'])) . "')
                        GROUP BY c.no_surat
                    ")->result();
                    foreach ($get_no_po as $item_no_po) {
                        $no_po[] = $item_no_po->no_surat;
                    }

                    $no_po = implode(', ', $no_po);

                    $view = '<button type="button" class="btn btn-sm btn-info view_inc" data-id="' . $item['id'] . '"><i class="fa fa-eye"></i></button>';
                    // }

                    $no_payment = array();
                    $get_no_payment = $this->db->get_where('payment_approve', ['no_doc' => $item['id']])->result();
                    foreach ($get_no_payment as $item_no_payment) {
                        $no_payment[] = $item_no_payment->id_payment;
                    }

                    $no_payment = implode(', ', $no_payment);

                    echo '<tr>';
                    echo '<td style="text-align: center;">' . $no . '</td>';
                    echo '<td style="text-align: center;">' . $item['id'] . '</td>';
                    echo '<td style="text-align: center;">' . $item['no_po'] . '</td>';
                    echo '<td style="text-align: center;">' . $no_po . '</td>';
                    echo '<td style="text-align: center;">' . $item['invoice_no'] . '</td>';
                    echo '<td style="text-align: center;">' . $no_payment . '</td>';
                    echo '<td style="text-align: center;">' . date('d F Y', strtotime($item['invoice_date'])) . '</td>';
                    echo '<td>' . $nm_supplier . '</td>';
                    echo '<td style="text-align: right">' . number_format($item['total_invoice'], 2) . '</td>';
                    echo '<td style="text-align: center;">' . $status . '</td>';
                    echo '<td style="text-align: center;">' . $view . '</td>';
                    echo '</tr>';
                    $no++;
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<script src="<?= base_url('assets/chosen_v1.8.7/chosen.jquery.min.js') ?>"></script>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('.table_req_pay_inc').dataTable();

        $('#select_supplier').chosen();
    });

    $(document).on('click', '.search_inc', function() {
        var kode_supplier = $('#select_supplier').val();

        $.ajax({
            type: "POST",
            url: siteurl + active_controller + "search_inc",
            data: {
                'kode_supplier': kode_supplier
            },
            cache: false,
            success: function(result) {
                $('.col_table').html(result);
                $('.table_req_pay_inc').dataTable();
            }
        });
    });
</script>