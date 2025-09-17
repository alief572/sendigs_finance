<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="<?= base_url('assets/chosen_v1.8.7/chosen.min.css') ?>">
<div class="req_payment_dp" style="margin-top: 2vh;">
    <b>Receive Invoice Progress</b>
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
            <button type="button" class="btn btn-sm btn-primary search_pro" style="margin-top: 20px;">
                <i class="fa fa-search"></i> Cari
            </button>
        </div>
    </div>
    <div class="col_table">
        <table class="table table-bordered table_req_pay_pro">
            <thead class="bg-yellow">
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">No. PO</th>
                    <th class="text-center">No. Purchase Invoice</th>
                    <th class="text-center">No. Invoice</th>
                    <th class="text-center">No. Payment</th>
                    <th class="text-center">Nama Supplier</th>
                    <th class="text-center">Tanggal PO</th>
                    <th class="text-center">Keterangan</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                foreach ($list_po as $item) {
                    $sts = '<div class="badge bg-yellow">Waiting</div>';
                    $close = 0;
                    if ($item['ttl_persen_dp'] == $item['progress']) {
                        $sts = '<div class="badge bg-green">Belum Lunas</div>';
                        $close = 1;

                        $get_invoice = $this->db->get_where('tr_invoice_po', ['no_po' => $item['no_surat'], 'id_top' => $item['id_top']])->row();
                        $check_payment_approve = $this->db->get_where('payment_approve', ['no_doc' => $get_invoice->id, 'status' => 2])->result();

                        if (count($check_payment_approve) > 0) {
                            $sts = '<div class="badge bg-blue">Lunas</div>';
                            $close = 1;
                        }
                    }

                    $get_incoming = $this->db->get_where('tr_incoming_check', ['no_ipp' => $item['no_po']])->result();
                    $arr_id_incoming = [];

                    foreach ($get_incoming as $item_incoming) {
                        $arr_id_incoming[] = $item_incoming->kode_trans;
                    }

                    if (!empty($arr_id_incoming)) {
                        $this->db->select('count(a.no_po) as num_po');
                        $this->db->from('tr_invoice_po a');
                        $this->db->where_in('a.no_po', $arr_id_incoming);
                        $num_invoice = $this->db->get()->row();

                        // if ($num_invoice->num_po > 0) {
                        //     $sts = '<div class="badge bg-green">Complete</div>';
                        //     $close = 1;
                        // }
                    }



                    $view_btn = '';
                    $req_pay_btn = '<button type="button" class="btn btn-sm btn-primary req_app" style="margin-left: 0.5rem" title="Request Payment" data-no_po="' . $item['no_surat'] . '" data-id_top="' . $item['id_top'] . '" data-tipe="pro"><i class="fa fa-arrow-up"></i></button>';
                    if ($close == 1) {
                        $get_invoice = $this->db->select('id')->get_where('tr_invoice_po', ['no_po' => $item['no_surat']])->row_array();

                        $view_btn = '<button type="button" class="btn btn-sm btn-info view" data-id="' . $get_invoice['id'] . '" data-id_top="' . $get_invoice['id_top'] . '"  data-tipe="pro" title="view"><i class="fa fa-eye"></i></button>';
                        $req_pay_btn = '';
                    }

                    $list_dp_btn = '';
                    // if($item['ttl_persen_dp'] > 0) {
                    //     $list_dp_btn = '<button type="button" class="btn btn-sm btn-warning list_dp" data-no_po="'.$item['no_po'].'" style="margin-left: 0.5rem"><i class="fa fa-list"></i></button>';
                    // }

                    $no_purchase_invoice = [];
                    $no_invoice = [];

                    $get_invoice = $this->db->select('a.*')
                        ->from('tr_invoice_po a')
                        ->where('a.id_top', $item['id_top'])
                        ->like('a.no_po', $item['no_surat'])
                        ->get()
                        ->result();
                    
                    foreach ($get_invoice as $item_invoice) {
                        $no_purchase_invoice[] = str_replace(',', '', $item_invoice->id);
                        $no_invoice[] = str_replace(',', '', $item_invoice->invoice_no);
                    }

                    if(!empty($no_purchase_invoice)) {
                        $no_purchase_invoice = implode(', ', $no_purchase_invoice);
                    }else{
                        $no_purchase_invoice = '';
                    }

                    if(!empty($no_invoice)) {
                        $no_invoice = implode(', ', $no_invoice);
                    }else{
                        $no_invoice = '';
                    }

                    $no_payment = array();
                    $get_no_payment = $this->db->get_where('payment_approve', ['no_doc' => $item['id_invoice']])->result();
                    foreach ($get_no_payment as $item_no_payment) {
                        $no_payment[] = $item_no_payment->id_payment;
                    }

                    $no_payment = implode(', ', $no_payment);

                    echo '<tr>';
                    echo '<td class="text-center">' . $no . '</td>';
                    echo '<td class="text-center">'.$item['no_surat'].'</td>';
                    echo '<td class="text-center">' . $no_purchase_invoice . '</td>';
                    echo '<td class="text-center">' . $no_invoice . '</td>';
                    echo '<td class="text-center">'.$no_payment.'</td>';
                    echo '<td class="text-center">' . $item['nm_supplier'] . '</td>';
                    echo '<td class="text-center">' . date('d F Y', strtotime($item['tanggal'])) . '</td>';
                    echo '<td class="text-center">' . $item['keterangan_top'] . '</td>';
                    echo '<td class="text-center">' . $sts . '</td>';
                    echo '<td style="text-align: center;">' . $view_btn . $req_pay_btn . $list_dp_btn . '</td>';
                    echo '</tr>';

                    $no++;
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script src="<?= base_url('assets/chosen_v1.8.7/chosen.jquery.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        $('.table_req_pay_pro').dataTable();

        $('#select_supplier').chosen();
    });

    $(document).on('click', '.search_pro', function() {
        var kode_supplier = $('#select_supplier').val();

        $.ajax({
            type: "POST",
            url: siteurl + active_controller + "search_pro",
            data: {
                'kode_supplier': kode_supplier
            },
            cache: false,
            success: function(result) {
                $('.col_table').html(result);
                $('.table_req_pay_pro').dataTable();
            }
        });
    });
</script>