<div class="box-body">
    <form action="" method="post" id="form_download_qr">
        <table border="0" width="100%">
            <thead>
                <tr>
                    <td class="text-left" style='vertical-align:middle;' width='15%'>No PO</td>
                    <td class="text-left" style='vertical-align:middle;' width='2%'>:</td>
                    <td class="text-left" style='vertical-align:middle;'><?= $no_po; ?></td>
                    <td class="text-left" style='vertical-align:middle;' width='15%'>No PR</td>
                    <td class="text-left" style='vertical-align:middle;' width='2%'>:</td>
                    <td class="text-left" style='vertical-align:middle;'><?= $no_pr; ?></td>
                </tr>
                <tr>
                    <td class="text-left" style='vertical-align:middle;'>No Transaksi</td>
                    <td class="text-left" style='vertical-align:middle;'>:</td>
                    <td class="text-left" style='vertical-align:middle;'><?= $result_header->kode_trans; ?></td>
                    <td colspan="3"></td>
                </tr>
                <tr>
                    <td class="text-left" style='vertical-align:middle;'>Tanggal Penerimaan</td>
                    <td class="text-left" style='vertical-align:middle;'>:</td>
                    <td class="text-left" style='vertical-align:middle;'><?= date('d F Y', strtotime($result_header->created_date)); ?></td>
                    <td colspan="3"></td>
                </tr>
            </thead>
        </table><br>
        <!-- <table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
            <thead id='head_table'>
                <tr class='bg-blue'>
                    <th class="text-center"><input type="checkbox" id="all_check"></th>
                    <th class="text-center">No.</th>
                    <th class="text-center">No. PO</th>
                    <th class="text-center">Material</th>
                    <th class="text-center">Incoming</th>
                    <th class="text-center">Unit</th>
                    <th class="text-center">Konversi</th>
                    <th class="text-center">Packing</th>
                    <th class="text-center">Qty Pack</th>
                    <th class="text-center">Qty NG</th>
                    <th class="text-center">Qty Oke</th>
                    <th class="text-center">Qty Pack</th>
                    <th class="text-center">Expired Date</th>
                    <th class="text-center">Document</th>
                    <th class="text-center">Lot Description</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $No = 0;
                foreach ($result as $val => $valx) {
                    $No++;

                    $packing = $valx['qty_order'];
                    if ($valx['konversi'] > 0) {
                        $packing = ($valx['qty_order'] / $valx['konversi']);
                    }

                    // echo '<tr>';
                    // echo '<td class="text-center">' . $No . '</td>';
                    // echo '<td class="text-center">' . $valx['no_surat'] . '</td>';
                    // echo '<td class="text-center">' . $valx['nm_material'] . '</td>';
                    // echo '<td class="text-center">' . number_format($valx['qty_incoming'], 2) . '</td>';
                    // echo '<td class="text-center">' . strtoupper($valx['unit']) . '</td>';
                    // echo '<td class="text-center">' . number_format($valx['konversi'], 2) . '</td>';
                    // echo '<td class="text-center">' . number_format($valx['qty_incoming'] / $valx['konversi'], 2) . '</td>';
                    // echo '<td class="text-center">' . strtoupper($valx['packing']) . '</td>';
                    // echo '<td class="text-center">' . number_format($valx['qty_ng'], 2) . '</td>';
                    // echo '<td class="text-center">' . number_format($valx['qty_oke'], 2) . '</td>';
                    // echo '<td class="text-center">' . number_format(($valx['qty_incoming'] - $valx['qty_ng']) / $valx['konversi'], 2) . '</td>';
                    // echo '<td class="text-center">' . date('d F Y', strtotime($valx['expired_date'])) . '</td>';
                    // echo '</tr>';

                    echo '<tr>';
                    echo '<td class="text-center"><input type="checkbox" name="checkbox[]" class="check_box" value="' . $valx['id'] . '"></td>';
                    echo '<td class="text-center">' . $No . '</td>';
                    echo '<td class="text-center">' . $valx['no_surat'] . '</td>';
                    echo '<td class="text-center">' . $valx['nm_material'] . '</td>';
                    echo '<td class="text-center">' . number_format($valx['qty_order'], 2) . '</td>';
                    echo '<td class="text-center">' . strtoupper($valx['satuan']) . '</td>';
                    echo '<td class="text-center">' . number_format($valx['konversi'], 2) . '</td>';
                    echo '<td class="text-center">' . number_format($packing, 2) . '</td>';
                    echo '<td class="text-center">' . $valx['packing'] . '</td>';
                    echo '<td class="text-center">-</td>';
                    echo '<td class="text-center">-</td>';
                    echo '<td class="text-center">-</td>';
                    echo '<td class="text-center">-</td>';
                    echo '<td class="text-center">-</td>';
                    echo '<td class="text-center">-</td>';
                    echo '</tr>';

                    $get_checked_incoming = $this->db->get_where('tr_checked_incoming_detail', ['kode_trans' => $valx['kode_trans'], 'id_material' => $valx['id_material']])->result_array();
                    foreach ($get_checked_incoming as $checked_incoming) :



                        echo '<tr>';
                        echo '<td colspan="9"></td>';
                        echo '<td class="text-center">' . number_format($checked_incoming['qty_ng'], 2) . '</td>';
                        echo '<td class="text-center">' . number_format($checked_incoming['qty_oke'], 2) . '</td>';
                        echo '<td class="text-center">' . number_format($checked_incoming['qty_pack'], 2) . '</td>';
                        echo '<td class="text-center">' . date('d F Y', strtotime($checked_incoming['expired_date'])) . '</td>';
                        echo '<td class="text-center">';
                        if (file_exists($checked_incoming['uploaded_file'])) {
                            echo '<a href="' . base_url($checked_incoming['uploaded_file']) . '" class="btn btn-sm btn-primary" target="_blank">Download File</a>';
                        }
                        echo '</td>';
                        echo '<td>' . $checked_incoming['lot_description'] . '</td>';
                        echo '</tr>';
                    endforeach;
                }
                ?>
            </tbody>
        </table> -->

        <?php
        $exp_no_ipp = explode(',', $result_header->no_ipp);
        foreach ($exp_no_ipp as $no_ipp) {

            $this->db->select('a.*, b.no_surat, c.konversi, d.code as satuan, e.code as packing');
            $this->db->from('tr_incoming_check_detail a');
            $this->db->join('tr_purchase_order b', 'b.no_po LIKE CONCAT("%",a.no_ipp,"%")', 'left');
            $this->db->join('dt_trans_po f', 'f.id = a.id_po_detail', 'left');
            $this->db->join('new_inventory_4 c', 'c.code_lv4 = a.id_material', 'left');
            $this->db->join('ms_satuan d', 'd.id = c.id_unit', 'left');
            $this->db->join('ms_satuan e', 'e.id = c.id_unit_packing', 'left');
            $this->db->where('a.kode_trans', $kode_trans);
            $this->db->where('f.no_po', $no_ipp);
            $result = $this->db->get()->result_array();

            $get_no_surat = $this->db->select('no_surat')->get_where('tr_purchase_order', ['no_po' => $no_ipp])->row();
        ?>
            <b>No. PO : <?= $get_no_surat->no_surat ?></b>
            <table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
                <thead id='head_table'>
                    <tr class='bg-blue'>
                        <th class="text-center"><input type="checkbox" id="all_check"></th>
                        <th class="text-center">No.</th>
                        <th class="text-center">No. PO</th>
                        <th class="text-center">Material</th>
                        <th class="text-center">Incoming</th>
                        <th class="text-center">Unit</th>
                        <th class="text-center">Konversi</th>
                        <th class="text-center">Packing</th>
                        <th class="text-center">Qty Pack</th>
                        <th class="text-center">Qty NG</th>
                        <th class="text-center">Qty Oke</th>
                        <th class="text-center">Qty Pack</th>
                        <th class="text-center">Expired Date</th>
                        <th class="text-center">Document</th>
                        <th class="text-center">Lot Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $No = 0;
                    foreach ($result as $val => $valx) {
                        $No++;

                        $packing = $valx['qty_order'];
                        if ($valx['konversi'] > 0) {
                            $packing = ($valx['qty_order'] / $valx['konversi']);
                        }

                        // echo '<tr>';
                        // echo '<td class="text-center">' . $No . '</td>';
                        // echo '<td class="text-center">' . $valx['no_surat'] . '</td>';
                        // echo '<td class="text-center">' . $valx['nm_material'] . '</td>';
                        // echo '<td class="text-center">' . number_format($valx['qty_incoming'], 2) . '</td>';
                        // echo '<td class="text-center">' . strtoupper($valx['unit']) . '</td>';
                        // echo '<td class="text-center">' . number_format($valx['konversi'], 2) . '</td>';
                        // echo '<td class="text-center">' . number_format($valx['qty_incoming'] / $valx['konversi'], 2) . '</td>';
                        // echo '<td class="text-center">' . strtoupper($valx['packing']) . '</td>';
                        // echo '<td class="text-center">' . number_format($valx['qty_ng'], 2) . '</td>';
                        // echo '<td class="text-center">' . number_format($valx['qty_oke'], 2) . '</td>';
                        // echo '<td class="text-center">' . number_format(($valx['qty_incoming'] - $valx['qty_ng']) / $valx['konversi'], 2) . '</td>';
                        // echo '<td class="text-center">' . date('d F Y', strtotime($valx['expired_date'])) . '</td>';
                        // echo '</tr>';

                        echo '<tr>';
                        echo '<td class="text-center"><input type="checkbox" name="checkbox[]" class="check_box" value="' . $valx['id'] . '"></td>';
                        echo '<td class="text-center">' . $No . '</td>';
                        echo '<td class="text-center">' . $get_no_surat->no_surat . '</td>';
                        echo '<td class="text-center">' . $valx['nm_material'] . '</td>';
                        echo '<td class="text-center">' . number_format($valx['qty_order'], 2) . '</td>';
                        echo '<td class="text-center">' . strtoupper($valx['satuan']) . '</td>';
                        echo '<td class="text-center">' . number_format($valx['konversi'], 2) . '</td>';
                        echo '<td class="text-center">' . number_format($packing, 2) . '</td>';
                        echo '<td class="text-center">' . $valx['packing'] . '</td>';
                        echo '<td class="text-center">-</td>';
                        echo '<td class="text-center">-</td>';
                        echo '<td class="text-center">-</td>';
                        echo '<td class="text-center">-</td>';
                        echo '<td class="text-center">-</td>';
                        echo '<td class="text-center">-</td>';
                        echo '</tr>';

                        $get_checked_incoming = $this->db->get_where('tr_checked_incoming_detail', ['kode_trans' => $valx['kode_trans'], 'id_detail' => $valx['id'], 'id_material' => $valx['id_material']])->result_array();
                        foreach ($get_checked_incoming as $checked_incoming) :



                            echo '<tr>';
                            echo '<td colspan="9"></td>';
                            echo '<td class="text-center">' . number_format($checked_incoming['qty_ng'], 2) . '</td>';
                            echo '<td class="text-center">' . number_format($checked_incoming['qty_oke'], 2) . '</td>';
                            echo '<td class="text-center">' . number_format($checked_incoming['qty_pack'], 2) . '</td>';
                            echo '<td class="text-center">' . date('d F Y', strtotime($checked_incoming['expired_date'])) . '</td>';
                            echo '<td class="text-center">';
                            if (file_exists($checked_incoming['uploaded_file'])) {
                                echo '<a href="' . base_url($checked_incoming['uploaded_file']) . '" class="btn btn-sm btn-primary" target="_blank">Download File</a>';
                            }
                            echo '</td>';
                            echo '<td>' . $checked_incoming['lot_description'] . '</td>';
                            echo '</tr>';
                        endforeach;
                    }
                    ?>
                </tbody>
            </table>

        <?php
        }
        ?>

        <?php
        // echo ($file_incoming_material);
        if ($result_header->file_incoming_material !== '' && $result_header->file_incoming_material !== null) {
            echo '<span>Incoming Material File</span> <br>';
            $exp_file_incoming_material = explode('|', $result_header->file_incoming_material);
            foreach ($exp_file_incoming_material as $exp_material) {
                if (file_exists($exp_material)) {
                    echo '<a href="' . base_url($exp_material) . '" class="" style="margin-top: 2vh;margin-left: 1vh;"><i class="fa fa-download"></i> ' . $exp_material . '</a> <br>';
                }
            }
        }
        if ($result_header->file_incoming_check !== '' && $result_header->file_incoming_check !== null) {
            echo '<span>Incoming Check File</span> <br>';
            $exp_file_incoming_check = explode('|', $result_header->file_incoming_check);
            foreach ($exp_file_incoming_check as $exp_incoming) {
                if (file_exists($exp_incoming)) {
                    echo '<a href="' . base_url($exp_incoming) . '" class="" style="margin-top: 2vh;margin-left: 1vh;"><i class="fa fa-download"></i> ' . $exp_incoming . '</a> <br>';
                }
            }
        }
        ?>

        <button type="submit" class="btn btn-sm btn-success btn_download_qr" name="btn_download_qr" style="margin-top: 1vh;">Download QR</button>
    </form>
</div>
<script>
    $(document).on('change', '#all_check', function() {
        if ($('#all_check').prop('checked')) {
            $('.check_box').prop('checked', true);
        } else {
            $('.check_box').prop('checked', false);
        }
    });
    $(document).on('click', '.btn_download_qr', function(e) {
        e.preventDefault();

        var countCheckedCheck = $('.check_box:checked').length;
        if (countCheckedCheck > 0) {
            swal({
                    title: "Are you sure?",
                    text: "You will not be able to process again this data!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Yes, Process it!",
                    cancelButtonText: "No, cancel process!",
                    closeOnConfirm: false,
                    closeOnCancel: false
                },
                function(isConfirm) {
                    if (isConfirm) {

                        var checkboxx = [];
                        $('.check_box:checked').each(function() {
                            checkboxx.push($(this).val());
                        });

                        // var formData = new FormData($('#form_download_qr')[0]);
                        $.ajax({
                            url: base_url + active_controller + 'save_download_qr',
                            type: "POST",
                            data: {
                                'checkboxx': checkboxx
                            },
                            cache: false,
                            dataType: 'json',
                            success: function(data) {
                                window.open('incoming_check/download_incoming_checked_qr/' + data.id, '_blank');
                            },
                            error: function() {
                                swal({
                                    title: "Error Message !",
                                    text: 'An Error Occured During Process. Please try again..',
                                    type: "warning",
                                    timer: 7000
                                });
                                $('#checkMaterial').prop('disabled', false);
                            }
                        });
                    } else {
                        swal("Cancelled", "Data can be process again :)", "error");
                        $('#checkMaterial').prop('disabled', false);
                        return false;
                    }
                });
        } else {
            swal("Error !", "Please check at least on check box first !", "error");
        }
    });
</script>