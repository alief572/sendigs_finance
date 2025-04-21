<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
    <div class="box-header">
        <button type="button" class="btn btn-sm btn-success proses_payment"><i class="fa fa-check"></i> Proses</button>
        <button type="button" class="btn btn-sm btn-danger clear_choosed_payment"><i class="fa fa-close"></i> Clear Checked Payment</button>
    </div>
    <div class="box-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">No. Dokumen</th>
                    <th class="text-center">Tgl</th>
                    <th class="text-center">Keperluan</th>
                    <th class="text-center">Currency</th>
                    <th class="text-center">Supplier</th>
                    <th class="text-center">Total Invoice</th>
                    <th class="text-center">Option</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                foreach ($results as $item) {
                    $no_incoming = [];
                    $no_po = [];
                    $nm_supplier = [];

                    $get_rec_invoice = $this->db->get_where('tr_invoice_po', ['id' => $item->no_doc])->row();
                    // print_r($get_rec_invoice);
                    // exit;
                    if (!empty($get_rec_invoice)) {
                        if (strpos($get_rec_invoice->no_po, 'TRS1') !== false) {
                            $arr_no_incoming = str_replace(', ', ',', $get_rec_invoice->no_po);
                            $get_no_po = $this->db
                                ->select('a.no_ipp')
                                ->from('tr_incoming_check a')
                                ->where_in('a.kode_trans', explode(',', $arr_no_incoming))
                                ->get()
                                ->result();

                            $arr_no_po = [];
                            foreach ($get_no_po as $item_no_po) {
                                $arr_no_po[] = $item_no_po->no_ipp;
                            }

                            $arr_no_po = implode(',', $arr_no_po);
                            $arr_no_po = str_replace(', ', ',', $arr_no_po);

                            $get_no_surat = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $arr_no_po) . "')")->result();
                            foreach ($get_no_surat as $item_no_surat) {
                                $no_po[] = $item_no_surat->no_surat;
                            }
                        } else {
                            $no_po[] = $get_rec_invoice->no_po;
                        }
                    }

                    if (!empty($no_po)) {
                        $get_nm_supplier = $this->db
                            ->select('b.nama as nm_supplier')
                            ->from('tr_purchase_order a')
                            ->join('new_supplier b', 'b.kode_supplier = a.id_suplier', 'left')
                            ->where_in('a.no_surat', $no_po)
                            ->group_by('b.nama')
                            ->get()
                            ->result();
                        foreach ($get_nm_supplier as $item_supplier) {
                            $nm_supplier[] = $item_supplier->nm_supplier;
                        }
                    }

                    $nm_supplier = implode(', ', $nm_supplier);

                    $get_choosed_payment = $this->db->get_where('tr_choosed_payment', ['id_user' => $this->auth->user_id(), 'id_payment' => $item->id])->result();
                    $checked = (count($get_choosed_payment) > 0) ? 'checked' : null;

                    echo '<tr>';
                    echo '<td class="text-center">' . $no . '</td>';
                    echo '<td class="text-center">' . $item->no_doc . '</td>';
                    echo '<td class="text-center">' . date('d F Y', strtotime($item->created_on)) . '</td>';
                    echo '<td class="text-left">' . $item->keperluan . '</td>';
                    echo '<td class="text-center">' . $item->currency . '</td>';
                    echo '<td class="text-center">' . $nm_supplier . '</td>';
                    echo '<td class="text-right">' . number_format($item->jumlah, 2) . '</td>';
                    echo '<td class="text-center">';
                    echo '<input type="checkbox" class="check_payment" value="' . $item->id . '" ' . $checked . '>';
                    echo '</td>';
                    echo '</tr>';
                    $no++;
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>

<!-- page script -->
<script>
    function check_choosed_payment() {
        return $.ajax({
            type: "POST",
            url: siteurl + active_controller + 'proses_payment',
            cache: false,
            dataType: 'json'
        });
    }
    $(document).on('click', '.check_payment', function() {
        var val = $(this).val();

        var checked = 0;
        if ($(this).is(':checked')) {
            checked = 1;
        }

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'check_payment',
            data: {
                'id': val,
                'checked': checked
            },
            cache: false,
            success: function(result) {

            },
            error: function(result) {
                swal({
                    title: 'Error !',
                    text: 'Please try again later !',
                    type: 'error'
                });
            }
        });
    });

    $(document).on('click', '.clear_choosed_payment', function() {
        swal({
                title: "Are you sure?",
                text: "Your choosed payment data will be clearef!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, Clear it!",
                cancelButtonText: "No, cancel process!",
                closeOnConfirm: true,
                closeOnCancel: false
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: siteurl + active_controller + 'clear_choosed_payment',
                        type: "POST",
                        cache: false,
                        dataType: 'json',
                        success: function(data) {
                            if (data.status == 1) {
                                swal({
                                    title: "Clear Success!",
                                    text: "Clear choosed payment success !",
                                    type: "success"
                                }, function(isSuccess) {
                                    location.reload(true);
                                });
                            } else {
                                swal({
                                    title: 'Clear Failed !',
                                    text: 'Clear choosed Payment Failed !',
                                    type: 'warning'
                                });
                            }
                        },
                        error: function() {
                            swal({
                                title: 'Error !',
                                text: 'Please try again later !',
                                type: 'error'
                            });
                        }
                    });
                } else {
                    swal("Cancelled", "Data can be process again :)", "error");
                    return false;
                }
            });
    });

    $(document).on('click', '.proses_payment', function() {
        
        check_choosed_payment().done(function(data) {
            var choosed_payment = data.count_choosed_payment;

            if(choosed_payment > 0) {
                window.location.href = siteurl + active_controller + 'form_payment_new/?id_payment=' + data.arr_choosed_payment;
            }else{
                swal({
                    title: 'Warning !',
                    text: 'Please check at least 1 payment data !',
                    type: 'warning'
                });
            }
        }).fail(function(data) {
        });
    });
</script>