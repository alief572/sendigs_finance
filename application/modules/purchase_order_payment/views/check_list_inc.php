<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="<?= base_url('assets/chosen_v1.8.7/chosen.min.css') ?>">
<div class="req_payment_inc" style="margin-top: 2vh;">
    <h4>Receive Invoice by Incoming</h4>
    <div class="row">
        <!-- <div class="col-md-4" style="margin-top: 20px;">
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
        </div> -->
        <div class="col-md-12 text-right" style="margin-top: 20px;">
            <button type="button" class="btn btn-sm btn-danger clear_checked_invoice" style="margin-top: 20px;">Clear Checked Invoice</button>
            <button type="button" class="btn btn-sm btn-success rec_invoice_btn" style="margin-top: 20px;">Receive Invoice</button>
        </div>
    </div>
    <div class="col_table">
        <table class="table table-bordered table_req_pay_inc">
            <thead class="bg-blue">
                <tr>
                    <th style="text-align: center;">

                    </th>
                    <th class="text-center">No</th>
                    <th class="text-center">No. Incoming</th>
                    <th class="text-center">No. PO</th>
                    <th class="text-center">Nominal Incoming</th>
                    <th class="text-center">Tanggal Incoming</th>
                    <th class="text-center">Nama Supplier</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                foreach ($list_inc as $item) {

                    $this->db->select('b.nama');
                    $this->db->from('tr_purchase_order a');
                    $this->db->join('new_supplier b', 'b.kode_supplier = a.id_suplier', 'left');
                    $this->db->where_in('a.no_po', explode(',', $item['no_ipp']));
                    $this->db->group_by('b.nama');
                    $get_nm_supplier = $this->db->get()->row_array();

                    $nm_supplier = (!empty($get_nm_supplier['nama'])) ? $get_nm_supplier['nama'] : null;

                    $id_rec_invoice = '';
                    $no_invoice = '';
                    $tgl_invoice = '';

                    $get_invoice = $this->db->query("SELECT * FROM tr_invoice_po WHERE no_po LIKE '%" . $item['kode_trans'] . "%'")->row();
                    if (count($get_invoice) > 0) {
                        $id_rec_invoice = $get_invoice->id;
                        $no_invoice = $get_invoice->invoice_no;
                        $tgl_invoice = date('d F Y', strtotime($get_invoice->invoice_date));
                    }

                    $complete = 0;
                    $status = '<div class="badge bg-orange">Draft</div>';
                    if ($id_rec_invoice !== '') {
                        $complete = 1;
                        $status = '<div class="badge bg-yellow">Waiting</div>';
                    }
                    if ($id_rec_invoice !== '') {
                        $get_invoice_payment = $this->db->get_where('payment_approve', ['no_doc' => $id_rec_invoice, 'status' => 2])->result();
                        if (count($get_invoice_payment) > 0) {
                            $complete = 1;
                            $status = '<div class="badge bg-green">Complete</div>';
                        }
                    }

                    $view_btn = '';
                    // $req_inc_app_btn = '<button type="button" class="btn btn-sm btn-primary req_inc_app" data-kode_trans="' . $item['kode_trans'] . '" data-tipe_incoming="'.$item['tipe_incoming'].'" title="Request Payment"><i class="fa fa-arrow-up"></i></button>';
                    if ($complete > 0) {
                        // $get_invoice = $this->db->select('id')->get_where('tr_invoice_po', ['no_po' => $item['kode_trans']])->row_array();
                        $view_btn = '<button type="button" class="btn btn-sm btn-info view_inc" data-id="' . $id_rec_invoice . '" data-tipe_incoming="' . $item['tipe_incoming'] . '" title="view"><i class="fa fa-eye"></i></button>';
                        // $req_inc_app_btn = '';
                    }

                    $checked = '';
                    $checked_invoice = $this->db->get_where('tr_check_invoice', [
                        'kode_trans' => $item['kode_trans'],
                        'id_user' => $this->auth->user_id()
                    ])->row();
                    if (count($checked_invoice) > 0) {
                        $checked = 'checked';
                    }

                    $check_box = '<input type="checkbox" name="check_invoice[]" class="check_invoice" data-kode_trans="' . $item['kode_trans'] . '" data-tipe_incoming="' . $item['tipe_incoming'] . '" value="' . $item['kode_trans'] . '" ' . $checked . '>';
                    if ($complete == 0) {
                        $total_invoice = 0;
                        $this->db->select('IF(SUM(b.hargasatuan * a.qty_order) IS NULL, 0, SUM(b.hargasatuan * a.qty_order)) as total_invoice');
                        $this->db->from('tr_incoming_check_detail a');
                        $this->db->join('dt_trans_po b', 'b.id = id_po_detail');
                        $this->db->where('a.kode_trans', $item['kode_trans']);
                        $get_ttl_invoice = $this->db->get()->row();
                        if (!empty($get_ttl_invoice)) {
                            $total_invoice = $get_ttl_invoice->total_invoice;
                        }

                        if ($total_invoice <= 0) {
                            $this->db->select('IF(SUM(b.hargasatuan * a.qty_oke) IS NULL, 0, SUM(b.hargasatuan * a.qty_oke)) as total_invoice');
                            $this->db->from('warehouse_adjustment_detail a');
                            $this->db->join('dt_trans_po b', 'b.id = no_ipp');
                            $this->db->where('a.kode_trans', $item['kode_trans']);
                            $get_ttl_invoice = $this->db->get()->row();
                            if (!empty($get_ttl_invoice)) {
                                $total_invoice = $get_ttl_invoice->total_invoice;
                            }
                        }

                        if($total_invoice <= 0) {
                            $this->db->select('IF(SUM(a.total_harga) IS NULL, 0, SUM(a.total_harga)) AS total_invoice');
                            $this->db->from('tr_pr_detail_kasbon a');
                            $this->db->where('a.id_kasbon', $item['no_ipp']);
                            $get_ttl_invoice = $this->db->get()->row();
                            if (!empty($get_ttl_invoice)) {
                                $total_invoice = $get_ttl_invoice->total_invoice;
                            }
                        }

                        if($total_invoice <= 0) {
                            $this->db->select('IF(SUM(a.harga_total) IS NULL, 0, SUM(a.harga_total)) AS total_invoice');
                            $this->db->from('dt_trans_po a');
                            $this->db->join('tr_purchase_order b', 'b.no_po = a.no_po');
                            $this->db->where('b.no_surat', $item['no_ipp']);
                            $get_ttl_invoice = $this->db->get()->row();
                            if (!empty($get_ttl_invoice)) {
                                $total_invoice = $get_ttl_invoice->total_invoice;
                            }
                        }

                        echo '<tr>';
                        echo '<td style="text-align: center;">' . $check_box . '</td>';
                        echo '<td style="text-align: center;">' . $no . '</td>';
                        echo '<td style="text-align: center;">' . $item['kode_trans'] . '</td>';
                        echo '<td style="text-align: center;">' . $no_po[$item['kode_trans']] . '</td>';
                        echo '<td style="text-align: right">' . number_format($total_invoice) . '</td>';
                        echo '<td style="text-align: center;">' . date('d F Y', strtotime($item['tanggal'])) . '</td>';
                        echo '<td style="text-align: center;">' . $nm_supplier . '</td>';
                        echo '</tr>';
                        $no++;
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>Receive Invoice by Incoming</h4>
            </div>
            <form action="" method="post" id="frm-data">
                <div class="modal-body" id="ModalView">

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary save_btn_modal"><i class="fa fa-save"></i> Save</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <span class="glyphicon glyphicon-remove"></span> Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/chosen_v1.8.7/chosen.jquery.min.js') ?>"></script>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('.table_req_pay_inc').dataTable();

        $('#select_supplier').chosen();
    });

    function checkCheckedInv() {
        var jqXHR = $.ajax({
            url: siteurl + active_controller + 'checkCheckedInv',
            async: false
        });

        return jqXHR.responseText;
    }

    $(document).on('click', '.check_invoice', function() {
        var kode_trans = $(this).data('kode_trans');
        var tipe_incoming = $(this).data('tipe_incoming');

        if ($(this).is(':checked')) {
            var tipe = 1;
        } else {
            var tipe = 0;
        }

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'check_invoice',
            data: {
                'kode_trans': kode_trans,
                'tipe_incoming': tipe_incoming,
                'tipe': tipe
            },
            cache: false,
            dataType: 'json',
            success: function(result) {

            },
            error: function(result) {
                swal({
                    type: 'error',
                    title: 'Error !',
                    text: 'Please try again later !'
                });
            }
        });
    });

    $(document).on('click', '.clear_checked_invoice', function() {
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'clear_checked_invoice',
            cache: false,
            dataType: 'json',
            success: function(result) {
                if (result.status == 1) {
                    swal({
                        title: 'Success !',
                        text: 'Your checked invoice has been removed !',
                        type: 'success'
                    }, function(isConfirm) {
                        location.reload(true);
                    });
                } else {
                    swal({
                        title: 'Warning !',
                        text: 'Your checked invoice cannot removed !',
                        type: 'error'
                    });
                }
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

    $(document).on('click', '.rec_invoice_btn', function() {

        var check_inv = checkCheckedInv();
        if (check_inv <= 0) {
            swal({
                title: 'Warning !',
                text: 'Please check at least 1 Invoice below !',
                type: 'error'
            });
        } else {
            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + 'rec_invoice_btn',
                cache: false,
                success: function(result) {
                    $('#ModalView').html(result);
                    $('#dialog-popup').modal('show');
                },
                error: function(result) {

                }
            });
        }
    });

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        swal({
                title: "Warning !",
                text: "PO Invoice will be created !",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, Create it!",
                cancelButtonText: "Cancel!",
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function(isConfirm) {
                if (isConfirm) {

                    var formdata = new FormData($('#frm-data')[0]);
                    $.ajax({
                        type: 'POST',
                        url: siteurl + active_controller + '/save_invoice',
                        data: formdata,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function(result) {
                            if (result.status == 1) {
                                swal({
                                    title: 'Success !',
                                    text: 'PO Invoice has been saved !',
                                    type: 'success'
                                }, function(isConfirm) {
                                    window.location.href = siteurl + active_controller;
                                });
                            } else {
                                swal({
                                    title: 'Failed !',
                                    text: 'PO Invoice has not been saved !',
                                    type: 'error'
                                });
                            }
                        },
                        error: function(result) {
                            swal({
                                title: 'Error !',
                                text: 'Please try again later !',
                                type: 'error'
                            });
                        }
                    });
                }
            });
    });
</script>