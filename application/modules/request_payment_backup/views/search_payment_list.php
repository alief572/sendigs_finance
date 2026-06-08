<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">

<table class="table table-bordered" id="myTable">
    <thead>
        <tr>
            <th class="text-center">#</th>
            <th class="text-left">No Dokumen</th>
            <th class="text-left">Request By</th>
            <th class="text-left">Tanggal</th>
            <th class="text-left">Keperluan</th>
            <th class="text-tipe">Tipe</th>
            <th class="text-left">Nilai Pengajuan</th>
            <th class="text-center">Diajukan Oleh</th>
            <th class="text-center">Tanggal Pengajuan</th>
            <th class="text-center">Dibayar Oleh</th>
            <th class="text-center">Tanggal Pembayaran</th>
            <th class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $numb = 0;
        foreach ($data_payment_list as $record) {

            $nmuser = $record->nama;
            if ($record->tipe == 'kasbon') {
                $get_kasbon = $this->db->get_where('tr_kasbon', array('no_doc' => $record->no_doc))->row();
                $check_detail = $this->db->get_where('tr_pr_detail_kasbon', ['id_kasbon' => $record->no_doc])->result();
                if (count($check_detail)) {
                    if ($get_kasbon->tipe_pr == 'pr departemen') {
                        $this->db->select('b.nm_lengkap');
                        $this->db->from('rutin_non_planning_header a');
                        $this->db->join('users b', 'b.id_user = a.created_by');
                        $this->db->where('a.no_pr', $get_kasbon->id_pr);
                        $get_single_detail = $this->db->get()->row();

                        $nmuser = $get_single_detail->nm_lengkap;
                    }

                    if ($get_kasbon->tipe_pr == 'pr stok') {
                        $this->db->select('b.nm_lengkap');
                        $this->db->from('material_planning_base_on_produksi a');
                        $this->db->join('users b', 'b.id_user = a.created_by');
                        $this->db->where('a.no_pr', $get_kasbon->id_pr);
                        $get_single_detail = $this->db->get()->row();

                        $nmuser = $get_single_detail->nm_lengkap;
                    }

                    if ($get_kasbon->tipe_pr == 'pr asset') {
                        $this->db->select('b.nm_lengkap');
                        $this->db->from('tran_pr_header a');
                        $this->db->join('users b', 'b.id_user = a.created_by');
                        $this->db->where('a.no_pr', $get_kasbon->id_pr);
                        $get_single_detail = $this->db->get()->row();

                        $nmuser = $get_single_detail->nm_lengkap;
                    }
                }
            }

            $tgl_pengajuan = (isset($list_tgl_pengajuan_pembayaran[$record->no_doc])) ? $list_tgl_pengajuan_pembayaran[$record->no_doc]['tgl_pengajuan'] : '';

            $tgl_pembayaran = (isset($list_tgl_pengajuan_pembayaran[$record->no_doc])) ? $list_tgl_pengajuan_pembayaran[$record->no_doc]['tgl_pembayaran'] : '';

            $diajukan_oleh = (isset($list_tgl_pengajuan_pembayaran[$record->no_doc])) ? $list_tgl_pengajuan_pembayaran[$record->no_doc]['diajukan_oleh'] : '';

            $dibayar_oleh = (isset($list_tgl_pengajuan_pembayaran[$record->no_doc])) ? $list_tgl_pengajuan_pembayaran[$record->no_doc]['dibayar_oleh'] : '';

            $numb++; ?>
            <tr>
                <td><?= $numb; ?></td>
                <td><?= $record->no_doc ?></td>
                <td><?= $nmuser ?></td>
                <td><?= $record->tgl_doc ?></td>
                <td><?= $record->keperluan ?></td>
                <td><?= $record->tipe ?></td>
                <td><?= (($record->tipe == 'expense' and $record->kurang_bayar > 0) ? number_format($record->kurang_bayar) : number_format($record->jumlah)) ?></td>
                <td class="text-center"><?= $diajukan_oleh ?></td>
                <td class="text-center"><?= $tgl_pengajuan ?></td>
                <td class="text-center"><?= $dibayar_oleh ?></td>
                <td class="text-center"><?= $tgl_pembayaran ?></td>
                <td>
                    <?php
                    $get_request_payment = $this->db->get_where('request_payment', ['no_doc' => $record->no_doc])->row();
                    if (!empty($get_request_payment)) {
                        if ($record->sts_reject !== '1' && $record->sts_reject_manage !== '1') {
                            if ($get_request_payment->status == '0') {
                                echo '<div class="badge bg-blue">Open</div>';
                            }
                            if ($get_request_payment->status == '1' || $get_request_payment->status == '2') {
                                $get_payment_approve = $this->db->get_where('payment_approve', ['no_doc' => $record->no_doc])->row();
                                if (!empty($get_payment_approve) && $get_payment_approve->status == '2') {
                                    echo '<div class="badge bg-green text-light">Paid</div>';
                                } else {
                                    echo '<div class="badge bg-blue">Open</div>';
                                }
                            }
                        } else {
                            if ($record->sts_reject == '1') {
                                echo '<div class="badge bg-red">Rejected by Checker</div>';
                            } else if ($record->sts_reject_manage == '1') {
                                echo '<div class="badge bg-red">Rejected by Management</div>';
                            } else {
                                echo '<div class="badge bg-blue">Open</div>';
                            }
                        }
                    } else {
                        if ($record->sts_reject == '1') {
                            echo '<div class="badge bg-red">Rejected by Checker</div>';
                        } else if ($record->sts_reject_manage == '1') {
                            echo '<div class="badge bg-red">Rejected by Management</div>';
                        } else {
                            echo '<div class="badge bg-blue">Open</div>';
                        }
                    }
                    ?>
                </td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>

<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#myTable').dataTable();
    });
</script>