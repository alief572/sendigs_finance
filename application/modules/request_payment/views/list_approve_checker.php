<?php
$ENABLE_ADD     = has_permission('Approval_Request_Payment_Checker.Add');
$ENABLE_MANAGE  = has_permission('Approval_Request_Payment_Checker.Manage');
$ENABLE_VIEW    = has_permission('Approval_Request_Payment_Checker.View');
$ENABLE_DELETE  = has_permission('Approval_Request_Payment_Checker.Delete');

$count_transport = 0;
$count_kasbon = 0;
$count_expense = 0;
$count_periodik = 0;
$count_pembayaran_po = 0;
$count_direct_payment = 0;

foreach ($data as $item) :
    if ($item->tipe == 'transportasi') {
        $count_transport += 1;
    }
    if ($item->tipe == 'kasbon') {
        $count_kasbon += 1;
    }
    if ($item->tipe == 'expense') {
        if (strpos($item->no_doc, 'ER-') !== false || strpos($item->no_doc, 'ROS-') !== false) {
            $count_expense += 1;
        } else {
            $count_pembayaran_po += 1;
        }
    }
    if ($item->tipe == 'periodik') {
        $count_periodik += 1;
    }
    if ($item->tipe == 'direct_payment') {
        $count_direct_payment += 1;
    }
endforeach;
?>
<script src="//cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal')); ?>
<div class="box">
    <div class="container">
        <div class="row">
            <div class="col-md-4" style="margin-top: 2vh;">
                <div class="panel panel-default">
                    <div class="panel-heading bg-green">Transportasi</div>
                    <div class="panel-body">
                        <h2><?= $count_transport ?></h2>
                    </div>
                    <div class="panel-footer w-100">
                        <button type="button" class="btn btn-sm btn-primary btn_view_req" style="width: 100%;" data-val="transportasi"><i class="fa fa-eye"></i> View</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4" style="margin-top: 2vh;">
                <div class="panel panel-default">
                    <div class="panel-heading bg-yellow">Kasbon</div>
                    <div class="panel-body">
                        <h2><?= $count_kasbon ?></h2>
                    </div>
                    <div class="panel-footer w-100">
                        <button type="button" class="btn btn-sm btn-primary btn_view_req" style="width: 100%;" data-val="kasbon"><i class="fa fa-eye"></i> View</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4" style="margin-top: 2vh;">
                <div class="panel panel-default">
                    <div class="panel-heading bg-blue">Expense</div>
                    <div class="panel-body">
                        <h2><?= $count_expense ?></h2>
                    </div>
                    <div class="panel-footer w-100">
                        <button type="button" class="btn btn-sm btn-primary btn_view_req" style="width: 100%;" data-val="expense"><i class="fa fa-eye"></i> View</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4" style="margin-top: 2vh;">
                <div class="panel panel-default">
                    <div class="panel-heading bg-red">Periodik</div>
                    <div class="panel-body">
                        <h2><?= $count_periodik ?></h2>
                    </div>
                    <div class="panel-footer w-100">
                        <button type="button" class="btn btn-sm btn-primary btn_view_req" style="width: 100%;" data-val="periodik"><i class="fa fa-eye"></i> View</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4" style="margin-top: 2vh;">
                <div class="panel panel-default">
                    <div class="panel-heading bg-light-blue">Pembayaran PO</div>
                    <div class="panel-body">
                        <h2><?= $count_pembayaran_po ?></h2>
                    </div>
                    <div class="panel-footer w-100">
                        <button type="button" class="btn btn-sm btn-primary btn_view_req" style="width: 100%;" data-val="pembayaran_po"><i class="fa fa-eye"></i> View</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4" style="margin-top: 2vh;">
                <div class="panel panel-default">
                    <div class="panel-heading bg-grey">Direct Payment</div>
                    <div class="panel-body">
                        <h2><?= $count_direct_payment ?></h2>
                    </div>
                    <div class="panel-footer w-100">
                        <button type="button" class="btn btn-sm btn-primary btn_view_req" style="width: 100%;" data-val="direct_payment"><i class="fa fa-eye"></i> View</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- <div class="row"> -->
        <div class="col-md-12 list_transportasi" style="display: none;">
            <h2>Transportasi</h2>
            <table class="table table-bordered" id="table_transportasi" width="100%">
                <thead>
                    <tr>
                        <th class="text-center">No Dokument</th>
                        <th class="text-center">Request By</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Kepeluan</th>
                        <th class="text-center">Tipe</th>
                        <th class="text-center">Nilai Pengajuan</th>
                        <th class="text-center">Tanggal Pembayaran</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($data as $item_transportasi) :
                        if ($item_transportasi->tipe == 'transportasi') {
                            echo '<tr>';
                            echo '<td>' . $item_transportasi->no_doc . '</td>';
                            echo '<td>' . $item_transportasi->nama . '</td>';
                            echo '<td>' . $item_transportasi->tgl_doc . '</td>';
                            echo '<td>' . $item_transportasi->keperluan . '</td>';
                            echo '<td>' . $item_transportasi->tipe . '</td>';
                            echo '<td class="text-right">' . number_format($item_transportasi->jumlah) . '</td>';
                            echo '<td>' . $item_transportasi->tanggal . '</td>';
                            echo '<td>';
                            $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_transportasi->no_doc, 'ids' => $item_transportasi->ids])->row_array();

                            if ($item_transportasi->status == '0' || empty($get_sts_payment)) {
                                if ($item_transportasi->status == '9') {
                                    echo '<label class="label bg-orange">Rejected</label>';
                                } else {
                                    echo '<label class="label bg-aqua">Open</label>';
                                }
                            } elseif ($get_sts_payment['status'] == 1) {
                                echo '<label class="label bg-yellow">Process</label>';
                            } elseif ($get_sts_payment['status'] == 2) {
                                echo '<label class="label bg-red">Close</label>';
                            } else {
                                echo '<label class="label bg-gray"><span class="text-muted">Undefined</span></label>';
                            }
                            echo '</td>';
                            echo '<td class="text-center">';
                            if ($ENABLE_MANAGE && $get_sts_payment['status'] < 1) : ?>
                                <a href="<?= base_url($this->uri->segment(1) . '/approval_payment_checker/?type=' . $item_transportasi->tipe . '&id=' . $item_transportasi->id . '&nilai=' . $item_transportasi->jumlah); ?>" name="save" class="btn btn-primary btn-sm"><i class="fa fa-check-square-o">&nbsp;</i>Approve</a>
                                <!-- <input type="checkbox" name="status[]" id="status_<?= $numb ?>" value="<?= $item_transportasi->id ?>"> -->
                    <?php endif;
                            echo '</td>';
                            echo '</tr>';
                        }
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-12 list_kasbon" style="display: none;">
            <h2>Kasbon</h2>
            <a href="<?= base_url('approval_request_payment/export_excel_kasbon_checker/?tingkat=1') ?>" class="btn btn-sm btn-success"><i class="fa fa-files"></i> Export Excel</a>
            <table class="table table-bordered" id="table_kasbon" width="100%">
                <thead>
                    <tr>
                        <th class="text-center">No Dokument</th>
                        <th class="text-center">Request By</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Kepeluan</th>
                        <th class="text-center">Tipe</th>
                        <th class="text-center">Nilai Pengajuan</th>
                        <th class="text-center">Tanggal Pembayaran</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($data_kasbon as $item_kasbon) :
                        if ($item_kasbon->tipe == 'kasbon') {
                            $get_kasbon = $this->db->get_where('tr_kasbon', array('no_doc' => $item_kasbon->no_doc))->row();
                            $no_kasbon = (!empty($get_kasbon->no_kasbon_consultant)) ? $get_kasbon->no_kasbon_consultant : $item_kasbon->no_doc;
                            echo '<tr>';
                            echo '<td>' . $no_kasbon . '</td>';
                            echo '<td>' . $item_kasbon->nama . '</td>';
                            echo '<td>' . $item_kasbon->tgl_doc . '</td>';
                            echo '<td>' . $item_kasbon->keperluan . '</td>';
                            echo '<td>' . $item_kasbon->tipe . '</td>';
                            echo '<td class="text-right">' . number_format($item_kasbon->jumlah) . '</td>';
                            echo '<td>' . $item_kasbon->tanggal . '</td>';
                            echo '<td>';
                            $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_kasbon->no_doc, 'ids' => $item_kasbon->ids])->row_array();

                            if ($item_kasbon->status == '0' || empty($get_sts_payment)) {
                                if ($item_kasbon->status == '9') {
                                    echo '<label class="label bg-orange">Rejected</label>';
                                } else {
                                    echo '<label class="label bg-aqua">Open</label>';
                                }
                            } elseif ($get_sts_payment['status'] == 1) {
                                echo '<label class="label bg-yellow">Process</label>';
                            } elseif ($get_sts_payment['status'] == 2) {
                                echo '<label class="label bg-red">Close</label>';
                            } else {
                                echo '<label class="label bg-gray"><span class="text-muted">Undefined</span></label>';
                            }
                            echo '</td>';
                            echo '<td>';
                            // if ($ENABLE_MANAGE && $get_sts_payment['status'] < 1) :
                            if ($ENABLE_MANAGE && $get_kasbon->project_consultant == '0') : ?>
                                <?php if ($item_kasbon->status !== '2') : ?>
                                    <div class="text-center">
                                        <a href="<?= base_url($this->uri->segment(1) . '/approval_payment_checker/?type=' . $item_kasbon->tipe . '&id=' . $item_kasbon->id . '&nilai=' . $item_kasbon->jumlah); ?>" name="save" class="btn btn-primary btn-sm"><i class="fa fa-check-square-o">&nbsp;</i>Approve</a>
                                    </div>
                                    <!-- <input type="checkbox" name="status[]" id="status_<?= $numb ?>" value="<?= $item_kasbon->id ?>"> -->
                    <?php endif;
                            endif;
                            if ($ENABLE_MANAGE && $get_kasbon->project_consultant == '1') :

                                $get_kasbon_sendigs = $this->db->get_where('tr_kasbon', ['no_doc' => $item_kasbon->no_doc])->row();

                                $no_kasbon_consultant = (!empty($get_kasbon_sendigs)) ? $get_kasbon_sendigs->no_kasbon_consultant : '';

                                $get_kasbon_header = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_header', array('id' => $no_kasbon_consultant))->row();

                                $tipe = '';
                                $link_view = '';

                                if (!empty($get_kasbon_header)) {
                                    if ($get_kasbon_header->tipe == '1') {
                                        $tipe = 'Kasbon Subcont';
                                    }
                                    if ($get_kasbon_header->tipe == '2') {
                                        $tipe = 'Kasbon Akomodasi';
                                    }
                                    if ($get_kasbon_header->tipe == '3') {
                                        $tipe = 'Kasbon Others';
                                    }



                                    if ($get_kasbon_header->tipe == '1') {
                                        $link_view = base_url('kasbon_project/view_kasbon_subcont/' . urlencode(str_replace('/', '|', $get_kasbon_header->id)));
                                    }
                                    if ($get_kasbon_header->tipe == '2') {
                                        $link_view = base_url('kasbon_project/view_kasbon_akomodasi/' . urlencode(str_replace('/', '|', $get_kasbon_header->id)));
                                    }
                                    if ($get_kasbon_header->tipe == '3') {
                                        $link_view = base_url('kasbon_project/view_kasbon_others/' . urlencode(str_replace('/', '|', $get_kasbon_header->id)));
                                    }
                                }

                                if (($item_kasbon->status !== '2' && is_null($item_kasbon->app_checker))) :
                                    echo '<a href="' . base_url('approval_request_payment/approval_payment_checker/?id_exp_consultant=' . str_replace('/', '|', $get_kasbon->no_kasbon_consultant)) . '&id_kasbon=' . $item_kasbon->no_doc . '" class="btn btn-primary btn-sm"><i class="fa fa-check-square-o"></i> Approve</a>';
                                endif;

                                echo ' <a href="' . base_url('approval_request_payment/print_kasbon/' . urlencode(str_replace('/', '|', $get_kasbon->no_kasbon_consultant))) . '" class="btn btn-sm btn-info" title="Print PDF" target="_blank">';
                                echo '<i class="fa fa-print"></i>';
                                echo '</a>';

                                echo ' <a href="' . $link_view . '" class="btn btn-sm btn-info" title="View Kasbon" target="_blank"><i class="fa fa-eye"></i></a>';
                            endif;
                            echo '</td>';
                            echo '</tr>';
                        }
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-12 list_expense" style="display: none;">
            <h2>Expense</h2>
            <a href="<?= base_url('approval_request_payment/export_excel_expense_checker/?tingkat=1') ?>" class="btn btn-sm btn-success"><i class="fa fa-files"></i> Export Excel</a>
            <table class="table table-bordered" id="table_expense" width="100%">
                <thead>
                    <tr>
                        <th class="text-center">No Dokument</th>
                        <th class="text-center">Request By</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Kepeluan</th>
                        <th class="text-center">Tipe</th>
                        <th class="text-center">Nilai Pengajuan</th>
                        <th class="text-center">Tanggal Pembayaran</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($data as $item_expense) :
                        if ($item_expense->tipe == 'expense') {
                            $tipe = ucfirst($item_expense->tipe);
                            $get_expense = $this->db->get_where('tr_expense', ['no_doc' => $item_expense->no_doc])->row_array();
                            if ($get_expense['exp_inv_po'] == '1') {
                                $tipe = 'Pembayaran PO';
                            }
                            if (strpos($item_expense->no_doc, 'ROS') === true) {
                                $tipe = 'Pembayaran PIB';
                            }
                            if (strpos($item_expense->no_doc, 'ER-') !== false || strpos($item_expense->no_doc, 'ROS-') !== false) {
                                echo '<tr>';
                                echo '<td>' . $item_expense->no_doc . '</td>';
                                echo '<td>' . $item_expense->nama . '</td>';
                                echo '<td>' . $item_expense->tgl_doc . '</td>';
                                echo '<td>' . $item_expense->keperluan . '</td>';
                                echo '<td>' . $tipe . '</td>';
                                echo '<td class="text-right">' . number_format($item_expense->jumlah) . '</td>';
                                echo '<td>' . $item_expense->tanggal . '</td>';
                                echo '<td>';
                                $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_expense->no_doc, 'ids' => $item_expense->ids])->row_array();

                                if ($item_expense->status == '0' || empty($get_sts_payment)) {
                                    if ($item_expense->status == '9') {
                                        echo '<label class="label bg-orange">Rejected</label>';
                                    } else {
                                        echo '<label class="label bg-aqua">Open</label>';
                                    }
                                } elseif ($get_sts_payment['status'] == 1) {
                                    echo '<label class="label bg-yellow">Process</label>';
                                } elseif ($get_sts_payment['status'] == 2) {
                                    echo '<label class="label bg-red">Close</label>';
                                } else {
                                    echo '<label class="label bg-gray"><span class="text-muted">Undefined</span></label>';
                                }
                                echo '</td>';
                                echo '<td>';
                                // if ($ENABLE_MANAGE && $get_sts_payment['status'] < 1) : 
                                if ($ENABLE_MANAGE or $get_sts_payment['status'] < 1) {

                                    if ($get_expense['project_consultant'] == '1') {
                                        echo '
                                                <div class="text-center">
                                                    <a href="' . base_url('approval_request_payment/approval_payment_checker/?id_exp_consultant=' . urlencode(str_replace('/', '|', $get_expense['no_expense_consultant']))) . '&id_expense=' . $item_expense->no_doc . '" class="btn btn-sm btn-primary"><i class="fa fa-check-square-o">&nbsp;</i>Approve</a>
                                                </div>
                                            ';
                                    } else {
                                        echo '
                                                <div class="text-center">
                                                    <a href="' . base_url($this->uri->segment(1) . '/approval_payment_checker/?type=' . $item_expense->tipe . '&id=' . $item_expense->id . '&nilai=' . $item_expense->jumlah) . '" class="btn btn-sm btn-primary"><i class="fa fa-check-square-o">&nbsp;</i>Approve</a>
                                                </div>
                                            ';
                                    }
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        }
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-12 list_periodik" style="display: none;">
            <h2>Periodik</h2>
            <table class="table table-bordered" id="table_periodik" width="100%">
                <thead>
                    <tr>
                        <th class="text-center">No Dokument</th>
                        <th class="text-center">Request By</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Kepeluan</th>
                        <th class="text-center">Tipe</th>
                        <th class="text-center">Nilai Pengajuan</th>
                        <th class="text-center">Tanggal Pembayaran</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($data as $item_periodik) :
                        if ($item_periodik->tipe == 'periodik') {
                            echo '<tr>';
                            echo '<td>' . $item_periodik->no_doc . '</td>';
                            echo '<td>' . $item_periodik->nama . '</td>';
                            echo '<td>' . $item_periodik->tgl_doc . '</td>';
                            echo '<td>' . $item_periodik->keperluan . '</td>';
                            echo '<td>' . $item_periodik->tipe . '</td>';
                            echo '<td class="text-right">' . number_format($item_periodik->jumlah) . '</td>';
                            echo '<td>' . $item_periodik->tanggal . '</td>';
                            echo '<td>';
                            $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_periodik->no_doc, 'ids' => $item_periodik->ids])->row_array();

                            if ($item_periodik->status == '0' || empty($get_sts_payment)) {
                                if ($item_periodik->status == '9') {
                                    echo '<label class="label bg-orange">Rejected</label>';
                                } else {
                                    echo '<label class="label bg-aqua">Open</label>';
                                }
                            } elseif ($get_sts_payment['status'] == 1) {
                                echo '<label class="label bg-yellow">Process</label>';
                            } elseif ($get_sts_payment['status'] == 2) {
                                echo '<label class="label bg-red">Close</label>';
                            } else {
                                echo '<label class="label bg-gray"><span class="text-muted">Undefined</span></label>';
                            }
                            echo '</td>';
                            echo '<td>';
                            if ($ENABLE_MANAGE && $get_sts_payment['status'] < 1) : ?>
                                <div class="text-center"><a href="<?= base_url($this->uri->segment(1) . '/approval_payment_checker/?type=' . $item_periodik->tipe . '&id=' . $item_periodik->id . '&nilai=' . $item_periodik->jumlah); ?>" name="save" class="btn btn-primary btn-sm"><i class="fa fa-check-square-o">&nbsp;</i>Approve</a></div>
                                <!-- <input type="checkbox" name="status[]" id="status_<?= $numb ?>" value="<?= $item_periodik->id ?>"> -->
                    <?php endif;
                            echo '</td>';
                            echo '</tr>';
                        }
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-12 list_pembayaran_po" style="display: none;">
            <h2>Pembayaran PO</h2>
            <table class="table table-bordered" id="table_pembayaran_po" width="100%">
                <thead>
                    <tr>
                        <th class="text-center">No Dokumen</th>
                        <th class="text-center">No Invoice</th>
                        <th class="text-center">Request By</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Kepeluan</th>
                        <th class="text-center">Tipe</th>
                        <th class="text-center">Nilai Pengajuan</th>
                        <th class="text-center">Tanggal Pembayaran</th>
                        <th class="text-center">Keterangan PO</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="100">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($data as $item_expense) :
                        $no_invoice = (isset($list_no_invoice[$item_expense->no_doc])) ? $list_no_invoice[$item_expense->no_doc] : '';

                        if ($item_expense->tipe == 'expense') {
                            $tipe = ucfirst($item_expense->tipe);
                            $get_expense = $this->db->get_where('tr_expense', ['no_doc' => $item_expense->no_doc])->row_array();
                            if ($get_expense['exp_inv_po'] == '1') {
                                $tipe = 'Pembayaran PO';
                            }
                            if (strpos($item_expense->no_doc, 'ROS-') !== false) {
                                $tipe = 'Pembayaran PIB';
                            }


                            $exp_id_po = explode(',', $get_expense['id_po']);

                            $po_note = [];
                            $this->db->select('note');
                            $this->db->from('tr_purchase_order');
                            $this->db->where_in('no_surat', $exp_id_po);
                            $get_po_note = $this->db->get()->result();

                            foreach ($get_po_note as $item_po_note) {
                                $po_note[] = $item_po_note->note;
                            }

                            $po_note = implode(', ', $po_note);

                            if ($get_expense['exp_inv_po'] == '1') {
                                echo '<tr>';
                                echo '<td>' . $item_expense->no_doc . '</td>';
                                echo '<td>' . $no_invoice . '</td>';
                                echo '<td>' . $item_expense->nama . '</td>';
                                echo '<td>' . $item_expense->tgl_doc . '</td>';
                                echo '<td>' . $item_expense->keperluan . '</td>';
                                echo '<td>' . $tipe . '</td>';
                                echo '<td class="text-right">' . number_format($item_expense->jumlah) . '</td>';
                                echo '<td>' . $item_expense->tanggal . '</td>';
                                echo '<td>' . $po_note . '</td>';
                                echo '<td>';
                                $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_expense->no_doc, 'ids' => $item_expense->ids])->row_array();

                                if ($item_expense->status == '0' || empty($get_sts_payment)) {
                                    if ($item_expense->status == '9') {
                                        echo '<label class="label bg-orange">Rejected</label>';
                                    } else {
                                        echo '<label class="label bg-aqua">Open</label>';
                                    }
                                } elseif ($get_sts_payment['status'] == 1) {
                                    echo '<label class="label bg-yellow">Process</label>';
                                } elseif ($get_sts_payment['status'] == 2) {
                                    echo '<label class="label bg-red">Close</label>';
                                } else {
                                    echo '<label class="label bg-gray"><span class="text-muted">Undefined</span></label>';
                                }
                                echo '</td>';
                                echo '<td class="text-center">';
                                if ($ENABLE_MANAGE && $get_sts_payment['status'] < 1) : ?>
                                    <a href="<?= base_url($this->uri->segment(1) . '/approval_payment_checker/?type=' . $item_expense->tipe . '&id=' . $item_expense->id . '&nilai=' . $item_expense->jumlah); ?>" name="save" class="btn btn-primary btn-sm"><i class="fa fa-check-square-o"></i></a>

                                    <a href="javascript:void(0);" class="btn btn-sm btn-info view_receive_invoice" data-id_invoice="<?= $item_expense->no_doc ?>"><i class="fa fa-eye"></i></a>
                    <?php endif;
                                echo '</td>';
                                echo '</tr>';
                            }
                        }
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>

        <div class="col-md-12 list_direct_payment" style="display: none;">
            <h2>Direct Payment</h2>
            <table class="table table-bordered" id="table_direct_payment" width="100%">
                <thead>
                    <tr>
                        <th class="text-center">No Dokument</th>
                        <th class="text-center">Request By</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Kepeluan</th>
                        <th class="text-center">Tipe</th>
                        <th class="text-center">Nilai Pengajuan</th>
                        <th class="text-center">Tanggal Pembayaran</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($data as $item_dp) :
                        if ($item_dp->tipe == 'direct_payment') {
                            echo '<tr>';
                            echo '<td>' . $item_dp->no_doc . '</td>';
                            echo '<td>' . $item_dp->nama . '</td>';
                            echo '<td>' . $item_dp->tgl_doc . '</td>';
                            echo '<td>' . $item_dp->keperluan . '</td>';
                            echo '<td>' . $item_dp->tipe . '</td>';
                            echo '<td class="text-right">' . number_format($item_dp->jumlah) . '</td>';
                            echo '<td>' . $item_dp->tanggal . '</td>';
                            echo '<td>';
                            $get_sts_payment = $this->db->select('status')->get_where('payment_approve', ['no_doc' => $item_dp->no_doc, 'ids' => $item_dp->ids])->row_array();

                            if ($item_dp->status == '0' || empty($get_sts_payment)) {
                                if ($item_dp->status == '9') {
                                    echo '<label class="label bg-orange">Rejected</label>';
                                } else {
                                    echo '<label class="label bg-aqua">Open</label>';
                                }
                            } elseif ($get_sts_payment['status'] == 1) {
                                echo '<label class="label bg-yellow">Process</label>';
                            } elseif ($get_sts_payment['status'] == 2) {
                                echo '<label class="label bg-red">Close</label>';
                            } else {
                                echo '<label class="label bg-gray"><span class="text-muted">Undefined</span></label>';
                            }
                            echo '</td>';
                            echo '<td>';
                    ?>

                            <a href="<?= base_url($this->uri->segment(1) . '/approval_payment_checker/?type=' . $item_dp->tipe . '&id=' . $item_dp->id . '&nilai=' . $item_dp->jumlah); ?>" name="save" class="btn btn-primary btn-sm"><i class="fa fa-check-square-o">&nbsp;</i>Approve</a>

                    <?php
                            echo '</td>';
                            echo '</tr>';
                        }
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>
        <!-- </div> -->
    </div>

    <!-- /.box-body -->
</div>
<?= form_close() ?>

<div class="modal modal-default fade" id="modal_view_receive_invoice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title title_modal" id="myModalLabel">View Receive Invoice</h4>
            </div>
            <div class="modal-body" id="ModalViewSPPLM">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Tutup</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script type="text/javascript">
    function trshowall() {
        $(".trows").removeClass("hidden");
    }

    function trshow(id) {
        $(".trows").addClass("hidden");
        $(".rowshow" + id).removeClass("hidden");
    }
    var url_save = siteurl + 'request_payment/save_approval/';
    //Save

    $(document).ready(function() {
        $('#table_transportasi').dataTable();
        $('#table_kasbon').dataTable();
        $('#table_expense').dataTable();
        $('#table_periodik').dataTable();
        $('#table_pembayaran_po').dataTable();
    })

    $(document).on("click", ".btn_view_req", function() {
        var val = $(this).data('val');
        // alert(val);

        $(".list_" + val).toggle();
        if (val == "transportasi") {
            $(".list_kasbon").hide();
            $(".list_expense").hide();
            $(".list_periodik").hide();
            $('.list_pembayaran_po').hide();
            $('.list_direct_payment').hide();
        }
        if (val == "kasbon") {
            $(".list_transportasi").hide();
            $(".list_expense").hide();
            $(".list_periodik").hide();
            $('.list_pembayaran_po').hide();
            $('.list_direct_payment').hide();
        }
        if (val == "expense") {
            $(".list_transportasi").hide();
            $(".list_kasbon").hide();
            $(".list_periodik").hide();
            $('.list_pembayaran_po').hide();
            $('.list_direct_payment').hide();
        }
        if (val == "periodik") {
            $(".list_transportasi").hide();
            $(".list_kasbon").hide();
            $(".list_expense").hide();
            $('.list_pembayaran_po').hide();
            $('.list_direct_payment').hide();
        }
        if (val == "pembayaran_po") {
            $(".list_transportasi").hide();
            $(".list_kasbon").hide();
            $(".list_expense").hide();
            $(".list_periodik").hide();
            $('.list_direct_payment').hide();
        }
        if (val == "direct_payment") {
            $(".list_transportasi").hide();
            $(".list_kasbon").hide();
            $(".list_expense").hide();
            $(".list_periodik").hide();
            $(".list_pembayaran_po").hide();
        }
    });

    $(document).on('click', '.view_receive_invoice', function() {
        var id_invoice = $(this).data('id_invoice');

        $.ajax({
            type: "POST",
            url: siteurl + active_controller + "view_receive_invoice",
            data: {
                "id_invoice": id_invoice
            },
            cache: false,
            success: function(result) {
                $('#ModalViewSPPLM').html(result);
                $('#modal_view_receive_invoice').modal('show');
            },
            error: function(result) {
                swal({
                    title: 'Error!',
                    text: 'Please try again later!',
                    type: 'error'
                });
            }
        });
    });

    $('#frm_data').on('submit', function(e) {
        e.preventDefault();
        var errors = "";
        if (errors == "") {
            swal({
                    title: "Anda Yakin?",
                    text: "Data Akan Di Setujui!",
                    type: "info",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Setujui!",
                    cancelButtonText: "Tidak!",
                    closeOnConfirm: false,
                    closeOnCancel: true
                },
                function(isConfirm) {
                    if (isConfirm) {
                        var formdata = new FormData($('#frm_data')[0]);
                        $.ajax({
                            url: url_save,
                            dataType: "json",
                            type: 'POST',
                            data: formdata,
                            processData: false,
                            contentType: false,
                            success: function(msg) {
                                if (msg['save'] == '1') {
                                    swal({
                                        title: "Sukses!",
                                        text: "Data Berhasil Di Setujui",
                                        type: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    window.location.href = window.location.href;
                                } else {
                                    swal({
                                        title: "Gagal!",
                                        text: "Data Gagal Di Setujui",
                                        type: "error",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                };
                                console.log(msg);
                            },
                            error: function(msg) {
                                swal({
                                    title: "Gagal!",
                                    text: "Ajax Data Gagal Di Proses",
                                    type: "error",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                console.log(msg);
                            }
                        });
                    }
                });
        } else {
            swal(errors);
            return false;
        }
    });
    $("#btnxls").click(function() {
        $("#mytabledata").table2excel({
            exclude: ".exclass",
            name: "Request Payment Approval",
            filename: "RequestPaymentApproval.xls", // do include extension
            preserveColors: false // set to true if you want background colors and font colors preserved
        });
    });
</script>