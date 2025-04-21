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
        $no = 1;
        foreach ($data_payment_list as $item) {
            $tgl_pengajuan = (isset($list_tgl_pengajuan_pembayaran[$item->no_doc])) ? $list_tgl_pengajuan_pembayaran[$item->no_doc]['tgl_pengajuan'] : '';

            $tgl_pembayaran = (isset($list_tgl_pengajuan_pembayaran[$item->no_doc])) ? $list_tgl_pengajuan_pembayaran[$item->no_doc]['tgl_pembayaran'] : '';

            $diajukan_oleh = (isset($list_tgl_pengajuan_pembayaran[$item->no_doc])) ? $list_tgl_pengajuan_pembayaran[$item->no_doc]['diajukan_oleh'] : '';

            $dibayar_oleh = (isset($list_tgl_pengajuan_pembayaran[$item->no_doc])) ? $list_tgl_pengajuan_pembayaran[$item->no_doc]['dibayar_oleh'] : '';

            echo '<tr>';
            echo '<td class="text-center">' . $no . '</td>';
            echo '<td class="text-left">' . $item->no_doc . '</td>';
            echo '<td class="text-left">' . $item->nama . '</td>';
            echo '<td class="text-left">' . $item->tgl_doc . '</td>';
            echo '<td class="text-left">' . $item->keperluan . '</td>';
            echo '<td class="text-left">' . $item->tipe . '</td>';
            echo '<td class="text-right">' . number_format($item->jumlah) . '</td>';
            echo '<td class="text-center">' . $diajukan_oleh . '</td>';
            echo '<td class="text-center">' . $tgl_pengajuan . '</td>';
            echo '<td class="text-center">' . $dibayar_oleh . '</td>';
            echo '<td class="text-center">' . $tgl_pembayaran . '</td>';
            echo '<td class="text-center">';
            $get_request_payment = $this->db->get_where('request_payment', ['no_doc' => $item->no_doc])->row();
            if (!empty($get_request_payment)) {
                if ($get_request_payment->status == '0') {
                    echo '<div class="badge bg-yellow text-light">Process</div>';
                }
                if ($get_request_payment->status == '1' || $get_request_payment->status == '2') {
                    $get_payment_approve = $this->db->get_where('payment_approve', ['no_doc' => $item->no_doc])->row();
                    if (count($get_payment_approve) > 0) {
                        if ($get_payment_approve->status == '2') {
                            echo '<div class="badge bg-green text-light">Paid</div>';
                        } else {
                            echo '<div class="badge bg-yellow text-light">Approved</div>';
                        }
                    } else {
                        echo '<div class="badge bg-yellow text-light">Approved</div>';
                    }
                }
            } else {
                $get_sts_reject = '';
                if ($item->tipe == 'transportasi') {
                    $get_sts_reject = $this->db->get_where('tr_transport_req', ['no_doc' => $item->no_doc])->row();
                }
                if ($item->tipe == 'kasbon') {
                    $get_sts_reject = $this->db->get_where('tr_kasbon', ['no_doc' => $item->no_doc])->row();
                }
                if ($item->tipe == 'expense') {
                    $get_sts_reject = $this->db->get_where('tr_expense', ['no_doc' => $item->no_doc])->row();
                }
                if ($item->tipe == 'periodik') {
                    $get_sts_reject = $this->db->get_where('tr_expense', ['no_doc' => $item->no_doc])->row();
                }

                if (!empty($get_sts_reject)) {
                    if ($get_sts_reject->sts_reject == '1') {
                        echo '<div class="badge bg-red text-light">Rejected</div>';
                    } else {
                        echo '<div class="badge bg-blue text-light">New</div>';
                    }
                } else {
                    echo '<div class="badge bg-blue text-light">New</div>';
                }
            }
            echo '</td>';
            echo '</tr>';

            $no++;
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