<?php

class Non_rutin_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();
        // Your own constructor code
    }

    public function get_data_json_non_rutin()
    {

        $requestData    = $_REQUEST;
        $fetch            = $this->query_data_json_non_rutin(
            $requestData['tanda'],
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );
        $totalData        = $fetch['totalData'];
        $totalFiltered    = $fetch['totalFiltered'];
        $query            = $fetch['query'];

        $data    = array();
        $urut1  = 1;
        $urut2  = 0;
        foreach ($query->result_array() as $row) {
            $total_data     = $totalData;
            $start_dari     = $requestData['start'];
            $asc_desc       = $requestData['order'][0]['dir'];
            if ($asc_desc == 'asc') {
                $nomor = $urut1 + $start_dari;
            }
            if ($asc_desc == 'desc') {
                $nomor = ($total_data - $start_dari) - $urut2;
            }

            $tanda = $requestData['tanda'];

            $nestedData     = array();
            $nestedData[]    = "<div align='center'>" . $nomor . "</div>";
            $no_pr = (!empty($row['no_pr'])) ? $row['no_pr'] : "<span class='text-red' title='No Pengajuan'>" . $row['no_pengajuan'] . "</span>";
            $nestedData[]    = "<div align='left'>" . $no_pr . "</div>";
            $nestedData[]    = "<div align='left'>" . strtoupper($row['nama']) . "</div>";

            $list_barang    = $this->db->get_where('rutin_non_planning_detail', array('no_pengajuan' => $row['no_pengajuan']))->result_array();
            $arr_nmbarang = array();
            $arr_spec = array();
            $arr_qty = array();
            $arr_tanggal = array();
            $arr_ket = array();
            foreach ($list_barang as $val => $valx) {
                $get_satuan = $this->db->get_where('ms_satuan', array('id' => $valx['satuan']))->result();
                $nm_satuan = (!empty($get_satuan)) ? strtolower($get_satuan[0]->code) : '';
                $arr_nmbarang[$val] = "&bull; " . strtoupper($valx['nm_barang']);
                $arr_spec[$val] = "&bull; " . strtoupper($valx['spec']);
                $arr_qty[$val] = "&bull; " . floatval($valx['qty']) . ' ' . $nm_satuan;
                $tgl_dibutuhkan = ($valx['tanggal'] <> '0000-00-00' and $valx['tanggal'] != NULL) ? date('d-M-Y', strtotime($valx['tanggal'])) : 'not set';
                $arr_tanggal[$val] = "&bull; " . $tgl_dibutuhkan;
                $arr_ket[$val] = "&bull; " . strtoupper($valx['keterangan']);
            }
            $dt_nama_barang    = implode("<br>", $arr_nmbarang);
            $dt_spec    = implode("<br>", $arr_spec);
            $dt_qty    = implode("<br>", $arr_qty);
            $dt_tanggal    = implode("<br>", $arr_tanggal);
            $dt_ket    = implode("<br>", $arr_ket);

            $nestedData[]    = "<div align='left'>" . $dt_nama_barang . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_spec . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_qty . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_tanggal . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_ket . "</div>";

            $last_by     = (!empty($row['updated_by'])) ? $row['updated_by'] : $row['created_by'];
            $last_date = (!empty($row['updated_date'])) ? $row['updated_date'] : $row['created_date'];

            // $nestedData[]	= "<div align='center'>".$last_by."</div>";
            // $nestedData[]	= "<div align='right'>".date('d-M-Y H:i:s', strtotime($last_date))."</div>";

            if ($row['sts_app'] == 'N') {
                $warna     = 'blue';
                $sts     = 'WAITING APPROVAL';
            } elseif ($row['sts_app'] == 'Y') {
                $warna     = 'green';
                $sts     = 'APPROVED';
            } else {
                $warna     = 'red';
                $sts     = 'REJECTED';
            }

            if (($row['sts_reject1'] !== null || $row['sts_reject2'] !== null || $row['sts_reject3'] !== null) && $row['rejected'] == 1) {
                if ($row['sts_reject1'] == "1") :
                    $warna = "red";
                    $sts = "Rejected By Head Department";
                elseif ($row['sts_reject2'] == "1") :
                    $warna = "red";
                    $sts = "Rejected By Cost Control";
                elseif ($row['sts_reject3'] == "1") :
                    $warna = "red";
                    $sts = "Rejected By Management";
                endif;
            } else {
                if ($row['app_1'] == null && $row['app_2'] == null && $row['app_3'] == null) :
                    $warna = "blue";
                    $sts = "Waiting Approval Head Department";
                elseif ($row['app_1'] !== null && $row['app_2'] == null && $row['app_3'] == null) :
                    $warna = "blue";
                    $sts = "Waiting Approval Cost Control";
                elseif ($row['app_1'] !== null && $row['app_2'] !== null && $row['app_3'] == null) :
                    $warna = "blue";
                    $sts = "Waiting Approval Management";
                else :
                    if ($row['sts_app'] == "Y") :
                        $warna = "green";
                        $sts = "Approved";
                    else :
                        $warna = "blue";
                        $sts = "Waiting Approval Head Department";
                    endif;
                endif;
            }

            $nestedData[]    = "<div align='left'><span class='badge' style='background-color: " . $warna . ";'>" . $sts . "</span></div>";
            $view        = "<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan'] . '/view') . "' class='btn btn-sm btn-warning' title='View' data-role='qtip'><i class='fa fa-eye'></i></a>";
            $edit        = "";
            $approve    = "";
            $cancel        = "";
            $print    = "&nbsp;<a href='" . base_url('non_rutin/print_pengajuan_non_rutin/' . $row['no_pengajuan']) . "' target='_blank' class='btn btn-sm btn-success' title='Print'><i class='fa fa-print'></i></a>";


            if ($tanda <> 'approval') {
                // if ($Arr_Akses['update'] == '1') {
                if ($row['sts_app'] == 'N' || $row['sts_app'] == '') {
                    $edit    = "&nbsp;<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan']) . "' class='btn btn-sm btn-primary' title='Edit' data-role='qtip'><i class='fa fa-edit'></i></a>";
                }
                // }
            }

            if ($tanda == 'approval') {
                $view        = "";
                // if ($Arr_Akses['approve'] == '1') {
                if ($row['sts_app'] == 'N') {
                    $approve    = "&nbsp;<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan'] . '/approve') . "' class='btn btn-sm btn-info' title='Approve' data-role='qtip'><i class='fa fa-check'></i></a>";
                }
                // }
            }
            $nestedData[]    = "<div align='left'>
									" . $view . "
                                    " . $edit . "
									" . $approve . "
									" . $cancel . "
									" . $print . "
									</div>";
            $data[] = $nestedData;
            $urut1++;
            $urut2++;
        }

        $json_data = array(
            "draw"                => intval($requestData['draw']),
            "recordsTotal"        => intval($totalData),
            "recordsFiltered"     => intval($totalFiltered),
            "data"                => $data
        );

        echo json_encode($json_data);
    }

    public function get_data_json_non_rutin_approval_head()
    {
        $ENABLE_ADD     = has_permission('Approval_PR_Depart_Head.Add');
        $ENABLE_MANAGE  = has_permission('Approval_PR_Depart_Head.Manage');
        $ENABLE_VIEW    = has_permission('Approval_PR_Depart_Head.View');
        $ENABLE_DELETE  = has_permission('Approval_PR_Depart_Head.Delete');

        $requestData    = $_REQUEST;
        $fetch            = $this->query_data_json_non_rutin_approval_head(
            $requestData['tanda'],
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );
        $totalData        = $fetch['totalData'];
        $totalFiltered    = $fetch['totalFiltered'];
        $query            = $fetch['query'];

        $data    = array();
        $urut1  = 1;
        $urut2  = 0;
        foreach ($query->result_array() as $row) {
            $total_data     = $totalData;
            $start_dari     = $requestData['start'];
            $asc_desc       = $requestData['order'][0]['dir'];
            if ($asc_desc == 'asc') {
                $nomor = $urut1 + $start_dari;
            }
            if ($asc_desc == 'desc') {
                $nomor = ($total_data - $start_dari) - $urut2;
            }

            $tanda = $requestData['tanda'];

            $nestedData     = array();
            $nestedData[]    = "<div align='center'>" . $nomor . "</div>";
            $no_pr = (!empty($row['no_pr'])) ? $row['no_pr'] : "<span class='text-red' title='No Pengajuan'>" . $row['no_pengajuan'] . "</span>";
            $nestedData[]    = "<div align='left'>" . $no_pr . "</div>";
            $nestedData[]    = "<div align='left'>" . strtoupper($row['nama']) . "</div>";

            $list_barang    = $this->db->get_where('rutin_non_planning_detail', array('no_pengajuan' => $row['no_pengajuan']))->result_array();
            $arr_nmbarang = array();
            $arr_spec = array();
            $arr_qty = array();
            $arr_tanggal = array();
            $arr_ket = array();
            foreach ($list_barang as $val => $valx) {
                $get_satuan = $this->db->get_where('ms_satuan', array('id' => $valx['satuan']))->result();
                $nm_satuan = (!empty($get_satuan)) ? strtolower($get_satuan[0]->code) : '';
                $arr_nmbarang[$val] = "&bull; " . strtoupper($valx['nm_barang']);
                $arr_spec[$val] = "&bull; " . strtoupper($valx['spec']);
                $arr_qty[$val] = "&bull; " . floatval($valx['qty']) . ' ' . $nm_satuan;
                $tgl_dibutuhkan = ($valx['tanggal'] <> '0000-00-00' and $valx['tanggal'] != NULL) ? date('d-M-Y', strtotime($valx['tanggal'])) : 'not set';
                $arr_tanggal[$val] = "&bull; " . $tgl_dibutuhkan;
                $arr_ket[$val] = "&bull; " . strtoupper($valx['keterangan']);
            }
            $dt_nama_barang    = implode("<br>", $arr_nmbarang);
            $dt_spec    = implode("<br>", $arr_spec);
            $dt_qty    = implode("<br>", $arr_qty);
            $dt_tanggal    = implode("<br>", $arr_tanggal);
            $dt_ket    = implode("<br>", $arr_ket);

            $nestedData[]    = "<div align='left'>" . $dt_nama_barang . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_spec . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_qty . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_tanggal . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_ket . "</div>";

            $last_by     = (!empty($row['updated_by'])) ? $row['updated_by'] : $row['created_by'];
            $last_date = (!empty($row['updated_date'])) ? $row['updated_date'] : $row['created_date'];

            // $nestedData[]	= "<div align='center'>".$last_by."</div>";
            // $nestedData[]	= "<div align='right'>".date('d-M-Y H:i:s', strtotime($last_date))."</div>";

            if ($row['sts_app'] == 'N') {
                $warna     = 'blue';
                $sts     = 'WAITING APPROVAL';
            } elseif ($row['sts_app'] == 'Y') {
                $warna     = 'green';
                $sts     = 'APPROVED';
            } else {
                $warna     = 'red';
                $sts     = 'REJECTED';
            }

            if (($row['sts_reject1'] !== null || $row['sts_reject2'] !== null || $row['sts_reject3'] !== null) && $row['rejected'] == 1) {
                if ($row['sts_reject1'] == "1") :
                    $warna = "red";
                    $sts = "Rejected By Head Department";
                elseif ($row['sts_reject2'] == "1") :
                    $warna = "red";
                    $sts = "Rejected By Cost Control";
                elseif ($row['sts_reject3'] == "1") :
                    $warna = "red";
                    $sts = "Rejected By Management";
                endif;
            } else {
                if ($row['app_1'] == null && $row['app_2'] == null && $row['app_3'] == null) :
                    $warna = "blue";
                    $sts = "Waiting Approval Head Department";
                elseif ($row['app_1'] !== null && $row['app_2'] == null && $row['app_3'] == null) :
                    $warna = "blue";
                    $sts = "Waiting Approval Cost Control";
                elseif ($row['app_1'] !== null && $row['app_2'] !== null && $row['app_3'] == null) :
                    $warna = "blue";
                    $sts = "Waiting Approval Management";
                else :
                    if ($row['sts_app'] == "Y") :
                        $warna = "green";
                        $sts = "Approved";
                    else :
                        $warna = "blue";
                        $sts = "Waiting Approval Head Department";
                    endif;
                endif;
            }

            $nestedData[]    = "<div align='left'><span class='badge' style='background-color: " . $warna . ";'>" . $sts . "</span></div>";
            $view        = "<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan'] . '/view') . "' class='btn btn-sm btn-warning' title='View' data-role='qtip'><i class='fa fa-eye'></i></a>";
            $edit        = "";
            $approve    = "";
            $cancel        = "";
            $print    = "&nbsp;<a href='" . base_url('non_rutin/print_pengajuan_non_rutin/' . $row['no_pengajuan']) . "' target='_blank' class='btn btn-sm btn-success' title='Print'><i class='fa fa-print'></i></a>";


            // if ($tanda <> 'approval') {
            //     // if ($Arr_Akses['update'] == '1') {
            //     if ($row['sts_app'] == 'N') {
            //         $edit    = "&nbsp;<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan']) . "' class='btn btn-sm btn-primary' title='Edit' data-role='qtip'><i class='fa fa-edit'></i></a>";
            //     }
            //     // }
            // }

            // if ($tanda == 'approval') {
            //     $view        = "";
            //     // if ($Arr_Akses['approve'] == '1') {
            //     if ($row['sts_app'] == 'N') {
            //         $approve    = "&nbsp;<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan'] . '/approve') . "' class='btn btn-sm btn-info' title='Approve' data-role='qtip'><i class='fa fa-check'></i></a>";
            //     }
            //     // }
            // }
            $view = "";
            $approve = '';
            if ($ENABLE_MANAGE) {
                $approve    = "&nbsp;<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan'] . '/approve/1') . "' class='btn btn-sm btn-info' title='Approve' data-role='qtip'><i class='fa fa-check'></i></a>";
            }
            $nestedData[]    = "<div align='left'>
									" . $view . "
                                    " . $edit . "
									" . $approve . "
									" . $cancel . "
									" . $print . "
									</div>";
            $data[] = $nestedData;
            $urut1++;
            $urut2++;
        }

        $json_data = array(
            "draw"                => intval($requestData['draw']),
            "recordsTotal"        => intval($totalData),
            "recordsFiltered"     => intval($totalFiltered),
            "data"                => $data
        );

        echo json_encode($json_data);
    }

    public function get_data_json_non_rutin_approval_cost_control()
    {
        $ENABLE_ADD     = has_permission('Approval_PR_Depart_Cost_Control.Add');
        $ENABLE_MANAGE  = has_permission('Approval_PR_Depart_Cost_Control.Manage');
        $ENABLE_VIEW    = has_permission('Approval_PR_Depart_Cost_Control.View');
        $ENABLE_DELETE  = has_permission('Approval_PR_Depart_Cost_Control.Delete');

        $requestData    = $_REQUEST;
        $fetch            = $this->query_data_json_non_rutin_approval_cost_control(
            $requestData['tanda'],
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );
        $totalData        = $fetch['totalData'];
        $totalFiltered    = $fetch['totalFiltered'];
        $query            = $fetch['query'];

        $data    = array();
        $urut1  = 1;
        $urut2  = 0;
        foreach ($query->result_array() as $row) {
            $total_data     = $totalData;
            $start_dari     = $requestData['start'];
            $asc_desc       = $requestData['order'][0]['dir'];
            if ($asc_desc == 'asc') {
                $nomor = $urut1 + $start_dari;
            }
            if ($asc_desc == 'desc') {
                $nomor = ($total_data - $start_dari) - $urut2;
            }

            $tanda = $requestData['tanda'];

            $nestedData     = array();
            $nestedData[]    = "<div align='center'>" . $nomor . "</div>";
            $no_pr = (!empty($row['no_pr'])) ? $row['no_pr'] : "<span class='text-red' title='No Pengajuan'>" . $row['no_pengajuan'] . "</span>";
            $nestedData[]    = "<div align='left'>" . $no_pr . "</div>";
            $nestedData[]    = "<div align='left'>" . strtoupper($row['nama']) . "</div>";

            $list_barang    = $this->db->get_where('rutin_non_planning_detail', array('no_pengajuan' => $row['no_pengajuan']))->result_array();
            $arr_nmbarang = array();
            $arr_spec = array();
            $arr_qty = array();
            $arr_tanggal = array();
            $arr_ket = array();
            foreach ($list_barang as $val => $valx) {
                $get_satuan = $this->db->get_where('ms_satuan', array('id' => $valx['satuan']))->result();
                $nm_satuan = (!empty($get_satuan)) ? strtolower($get_satuan[0]->code) : '';
                $arr_nmbarang[$val] = "&bull; " . strtoupper($valx['nm_barang']);
                $arr_spec[$val] = "&bull; " . strtoupper($valx['spec']);
                $arr_qty[$val] = "&bull; " . floatval($valx['qty']) . ' ' . $nm_satuan;
                $tgl_dibutuhkan = ($valx['tanggal'] <> '0000-00-00' and $valx['tanggal'] != NULL) ? date('d-M-Y', strtotime($valx['tanggal'])) : 'not set';
                $arr_tanggal[$val] = "&bull; " . $tgl_dibutuhkan;
                $arr_ket[$val] = "&bull; " . strtoupper($valx['keterangan']);
            }
            $dt_nama_barang    = implode("<br>", $arr_nmbarang);
            $dt_spec    = implode("<br>", $arr_spec);
            $dt_qty    = implode("<br>", $arr_qty);
            $dt_tanggal    = implode("<br>", $arr_tanggal);
            $dt_ket    = implode("<br>", $arr_ket);

            $nestedData[]    = "<div align='left'>" . $dt_nama_barang . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_spec . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_qty . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_tanggal . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_ket . "</div>";

            $last_by     = (!empty($row['updated_by'])) ? $row['updated_by'] : $row['created_by'];
            $last_date = (!empty($row['updated_date'])) ? $row['updated_date'] : $row['created_date'];

            // $nestedData[]	= "<div align='center'>".$last_by."</div>";
            // $nestedData[]	= "<div align='right'>".date('d-M-Y H:i:s', strtotime($last_date))."</div>";

            if ($row['sts_app'] == 'N') {
                $warna     = 'blue';
                $sts     = 'WAITING APPROVAL';
            } elseif ($row['sts_app'] == 'Y') {
                $warna     = 'green';
                $sts     = 'APPROVED';
            } else {
                $warna     = 'red';
                $sts     = 'REJECTED';
            }

            if (($row['sts_reject1'] !== null || $row['sts_reject2'] !== null || $row['sts_reject3'] !== null) && $row['rejected'] == 1) {
                if ($row['sts_reject1'] == "1") :
                    $warna = "red";
                    $sts = "Rejected By Head Department";
                elseif ($row['sts_reject2'] == "1") :
                    $warna = "red";
                    $sts = "Rejected By Cost Control";
                elseif ($row['sts_reject3'] == "1") :
                    $warna = "red";
                    $sts = "Rejected By Management";
                endif;
            } else {
                if ($row['app_1'] == null && $row['app_2'] == null && $row['app_3'] == null) :
                    $warna = "blue";
                    $sts = "Waiting Approval Head Department";
                elseif ($row['app_1'] !== null && $row['app_2'] == null && $row['app_3'] == null) :
                    $warna = "blue";
                    $sts = "Waiting Approval Cost Control";
                elseif ($row['app_1'] !== null && $row['app_2'] !== null && $row['app_3'] == null) :
                    $warna = "blue";
                    $sts = "Waiting Approval Management";
                else :
                    if ($row['sts_app'] == "Y") :
                        $warna = "green";
                        $sts = "Approved";
                    else :
                        $warna = "blue";
                        $sts = "Waiting Approval Head Department";
                    endif;
                endif;
            }

            $nestedData[]    = "<div align='left'><span class='badge' style='background-color: " . $warna . ";'>" . $sts . "</span></div>";
            $view        = "<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan'] . '/view') . "' class='btn btn-sm btn-warning' title='View' data-role='qtip'><i class='fa fa-eye'></i></a>";
            $edit        = "";
            $approve    = "";
            $cancel        = "";
            $print    = "&nbsp;<a href='" . base_url('non_rutin/print_pengajuan_non_rutin/' . $row['no_pengajuan']) . "' target='_blank' class='btn btn-sm btn-success' title='Print'><i class='fa fa-print'></i></a>";


            // if ($tanda <> 'approval') {
            //     // if ($Arr_Akses['update'] == '1') {
            //     if ($row['sts_app'] == 'N') {
            //         $edit    = "&nbsp;<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan']) . "' class='btn btn-sm btn-primary' title='Edit' data-role='qtip'><i class='fa fa-edit'></i></a>";
            //     }
            //     // }
            // }

            // if ($tanda == 'approval') {
            //     $view        = "";
            //     // if ($Arr_Akses['approve'] == '1') {
            //     if ($row['sts_app'] == 'N') {
            //         $approve    = "&nbsp;<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan'] . '/approve') . "' class='btn btn-sm btn-info' title='Approve' data-role='qtip'><i class='fa fa-check'></i></a>";
            //     }
            //     // }
            // }
            $view = "";
            $approve = '';
            if ($ENABLE_MANAGE) {
                $approve    = "&nbsp;<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan'] . '/approve/2') . "' class='btn btn-sm btn-info' title='Approve' data-role='qtip'><i class='fa fa-check'></i></a>";
            }
            $nestedData[]    = "<div align='left'>
									" . $view . "
                                    " . $edit . "
									" . $approve . "
									" . $cancel . "
									" . $print . "
									</div>";
            $data[] = $nestedData;
            $urut1++;
            $urut2++;
        }

        $json_data = array(
            "draw"                => intval($requestData['draw']),
            "recordsTotal"        => intval($totalData),
            "recordsFiltered"     => intval($totalFiltered),
            "data"                => $data
        );

        echo json_encode($json_data);
    }

    public function get_data_json_non_rutin_approval_management()
    {
        $ENABLE_ADD     = has_permission('Approval_PR_Depart_Management.Add');
        $ENABLE_MANAGE  = has_permission('Approval_PR_Depart_Management.Manage');
        $ENABLE_VIEW    = has_permission('Approval_PR_Depart_Management.View');
        $ENABLE_DELETE  = has_permission('Approval_PR_Depart_Management.Delete');

        $requestData    = $_REQUEST;
        $fetch            = $this->query_data_json_non_rutin_approval_management(
            $requestData['tanda'],
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );
        $totalData        = $fetch['totalData'];
        $totalFiltered    = $fetch['totalFiltered'];
        $query            = $fetch['query'];

        $data    = array();
        $urut1  = 1;
        $urut2  = 0;
        foreach ($query->result_array() as $row) {
            $total_data     = $totalData;
            $start_dari     = $requestData['start'];
            $asc_desc       = $requestData['order'][0]['dir'];
            if ($asc_desc == 'asc') {
                $nomor = $urut1 + $start_dari;
            }
            if ($asc_desc == 'desc') {
                $nomor = ($total_data - $start_dari) - $urut2;
            }

            $tanda = $requestData['tanda'];

            $nestedData     = array();
            $nestedData[]    = "<div align='center'>" . $nomor . "</div>";
            $no_pr = (!empty($row['no_pr'])) ? $row['no_pr'] : "<span class='text-red' title='No Pengajuan'>" . $row['no_pengajuan'] . "</span>";
            $nestedData[]    = "<div align='left'>" . $no_pr . "</div>";
            $nestedData[]    = "<div align='left'>" . strtoupper($row['nama']) . "</div>";

            $list_barang    = $this->db->get_where('rutin_non_planning_detail', array('no_pengajuan' => $row['no_pengajuan']))->result_array();
            $arr_nmbarang = array();
            $arr_spec = array();
            $arr_qty = array();
            $arr_tanggal = array();
            $arr_ket = array();
            foreach ($list_barang as $val => $valx) {
                $get_satuan = $this->db->get_where('ms_satuan', array('id' => $valx['satuan']))->result();
                $nm_satuan = (!empty($get_satuan)) ? strtolower($get_satuan[0]->code) : '';
                $arr_nmbarang[$val] = "&bull; " . strtoupper($valx['nm_barang']);
                $arr_spec[$val] = "&bull; " . strtoupper($valx['spec']);
                $arr_qty[$val] = "&bull; " . floatval($valx['qty']) . ' ' . $nm_satuan;
                $tgl_dibutuhkan = ($valx['tanggal'] <> '0000-00-00' and $valx['tanggal'] != NULL) ? date('d-M-Y', strtotime($valx['tanggal'])) : 'not set';
                $arr_tanggal[$val] = "&bull; " . $tgl_dibutuhkan;
                $arr_ket[$val] = "&bull; " . strtoupper($valx['keterangan']);
            }
            $dt_nama_barang    = implode("<br>", $arr_nmbarang);
            $dt_spec    = implode("<br>", $arr_spec);
            $dt_qty    = implode("<br>", $arr_qty);
            $dt_tanggal    = implode("<br>", $arr_tanggal);
            $dt_ket    = implode("<br>", $arr_ket);

            $nestedData[]    = "<div align='left'>" . $dt_nama_barang . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_spec . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_qty . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_tanggal . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_ket . "</div>";

            $last_by     = (!empty($row['updated_by'])) ? $row['updated_by'] : $row['created_by'];
            $last_date = (!empty($row['updated_date'])) ? $row['updated_date'] : $row['created_date'];

            // $nestedData[]	= "<div align='center'>".$last_by."</div>";
            // $nestedData[]	= "<div align='right'>".date('d-M-Y H:i:s', strtotime($last_date))."</div>";

            if ($row['sts_app'] == 'N') {
                $warna     = 'blue';
                $sts     = 'WAITING APPROVAL';
            } elseif ($row['sts_app'] == 'Y') {
                $warna     = 'green';
                $sts     = 'APPROVED';
            } else {
                $warna     = 'red';
                $sts     = 'REJECTED';
            }

            if (($row['sts_reject1'] !== null || $row['sts_reject2'] !== null || $row['sts_reject3'] !== null) && $row['rejected'] == 1) {
                if ($row['sts_reject1'] == "1") :
                    $warna = "red";
                    $sts = "Rejected By Head Department";
                elseif ($row['sts_reject2'] == "1") :
                    $warna = "red";
                    $sts = "Rejected By Cost Control";
                elseif ($row['sts_reject3'] == "1") :
                    $warna = "red";
                    $sts = "Rejected By Management";
                endif;
            } else {
                if ($row['app_1'] == null && $row['app_2'] == null && $row['app_3'] == null) :
                    $warna = "blue";
                    $sts = "Waiting Approval Head Department";
                elseif ($row['app_1'] !== null && $row['app_2'] == null && $row['app_3'] == null) :
                    $warna = "blue";
                    $sts = "Waiting Approval Cost Control";
                elseif ($row['app_1'] !== null && $row['app_2'] !== null && $row['app_3'] == null) :
                    $warna = "blue";
                    $sts = "Waiting Approval Management";
                else :
                    if ($row['sts_app'] == "Y") :
                        $warna = "green";
                        $sts = "Approved";
                    else :
                        $warna = "blue";
                        $sts = "Waiting Approval Head Department";
                    endif;
                endif;
            }

            $nestedData[]    = "<div align='left'><span class='badge' style='background-color: " . $warna . ";'>" . $sts . "</span></div>";
            $view        = "<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan'] . '/view') . "' class='btn btn-sm btn-warning' title='View' data-role='qtip'><i class='fa fa-eye'></i></a>";
            $edit        = "";
            $approve    = "";
            $cancel        = "";
            $print    = "&nbsp;<a href='" . base_url('non_rutin/print_pengajuan_non_rutin/' . $row['no_pengajuan']) . "' target='_blank' class='btn btn-sm btn-success' title='Print'><i class='fa fa-print'></i></a>";


            // if ($tanda <> 'approval') {
            //     // if ($Arr_Akses['update'] == '1') {
            //     if ($row['sts_app'] == 'N') {
            //         $edit    = "&nbsp;<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan']) . "' class='btn btn-sm btn-primary' title='Edit' data-role='qtip'><i class='fa fa-edit'></i></a>";
            //     }
            //     // }
            // }

            // if ($tanda == 'approval') {
            //     $view        = "";
            //     // if ($Arr_Akses['approve'] == '1') {
            //     if ($row['sts_app'] == 'N') {
            //         $approve    = "&nbsp;<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan'] . '/approve') . "' class='btn btn-sm btn-info' title='Approve' data-role='qtip'><i class='fa fa-check'></i></a>";
            //     }
            //     // }
            // }
            $view = "";
            $approve = '';
            if ($ENABLE_MANAGE) {
                $approve    = "&nbsp;<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan'] . '/approve/3') . "' class='btn btn-sm btn-info' title='Approve' data-role='qtip'><i class='fa fa-check'></i></a>";
            }
            $nestedData[]    = "<div align='left'>
									" . $view . "
                                    " . $edit . "
									" . $approve . "
									" . $cancel . "
									" . $print . "
									</div>";
            $data[] = $nestedData;
            $urut1++;
            $urut2++;
        }

        $json_data = array(
            "draw"                => intval($requestData['draw']),
            "recordsTotal"        => intval($totalData),
            "recordsFiltered"     => intval($totalFiltered),
            "data"                => $data
        );

        echo json_encode($json_data);
    }

    public function query_data_json_non_rutin_approval_management($tanda, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {

        $get_user_dept_id = $this->db->select('department_id')->get_where('users', ['id_user' => $this->auth->user_id()])->row_array();
        $user_dept_id = '';
        if (!empty($get_user_dept_id)) {
            $user_dept_id = $get_user_dept_id['department_id'];
        }

        $where = "";
        if ($tanda == 'approval') {
            $where = "AND a.sts_app = 'N' ";
        }
        $sql = "
			SELECT
				(@row:=@row+1) AS nomor,
				a.*,
				b.nama
			FROM
				rutin_non_planning_detail z
				LEFT JOIN rutin_non_planning_header a ON z.no_pengajuan=a.no_pengajuan
				LEFT JOIN ms_department b ON a.id_dept=b.id,
				(SELECT @row:=0) r
		    WHERE 1=1 " . $where . " AND 
            a.status_id = 1 AND 
            a.no_pr IS NULL AND 
            a.app_post = '3' AND
            a.close_pr IS NULL
            AND (
				a.no_pengajuan LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.tanggal LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR b.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
			GROUP BY z.no_pengajuan
		";
        // echo $sql; exit;

        $data['totalData'] = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();
        $columns_order_by = array(
            0 => 'nomor',
            1 => 'no_pr',
            2 => 'b.nama'
        );

        $sql .= " ORDER BY id DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
        $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

        $data['query'] = $this->db->query($sql);
        return $data;
    }

    public function query_data_json_non_rutin_approval_cost_control($tanda, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {

        $get_user_dept_id = $this->db->select('department_id')->get_where('users', ['id_user' => $this->auth->user_id()])->row_array();
        $user_dept_id = '';
        if (!empty($get_user_dept_id)) {
            $user_dept_id = $get_user_dept_id['department_id'];
        }

        $where = "";
        if ($tanda == 'approval') {
            $where = "AND a.sts_app = 'N' ";
        }
        $sql = "
			SELECT
				(@row:=@row+1) AS nomor,
				a.*,
				b.nama
			FROM
				rutin_non_planning_detail z
				LEFT JOIN rutin_non_planning_header a ON z.no_pengajuan=a.no_pengajuan
				LEFT JOIN ms_department b ON a.id_dept=b.id,
				(SELECT @row:=0) r
		    WHERE 1=1 " . $where . " AND 
            a.status_id = 1 AND 
            a.no_pr IS NULL AND 
            a.app_post = '2' AND
            a.close_pr IS NULL
            AND (
				a.no_pengajuan LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.tanggal LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR b.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
			GROUP BY z.no_pengajuan
		";
        // echo $sql; exit;

        $data['totalData'] = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();
        $columns_order_by = array(
            0 => 'nomor',
            1 => 'no_pr',
            2 => 'b.nama'
        );

        $sql .= " ORDER BY id DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
        $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

        $data['query'] = $this->db->query($sql);
        return $data;
    }
    public function query_data_json_non_rutin_approval_head($tanda, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {

        $get_user_dept_id = $this->db->select('department_id')->get_where('users', ['id_user' => $this->auth->user_id()])->row_array();
        $user_dept_id = '';
        if (!empty($get_user_dept_id)) {
            $user_dept_id = $get_user_dept_id['department_id'];
        }

        $where = "";
        if ($tanda == 'approval') {
            $where = "AND a.sts_app = 'N' ";
        }
        $sql = "
			SELECT
				(@row:=@row+1) AS nomor,
				a.*,
				b.nama
			FROM
				rutin_non_planning_detail z
				LEFT JOIN rutin_non_planning_header a ON z.no_pengajuan=a.no_pengajuan
				LEFT JOIN ms_department b ON a.id_dept=b.id,
				(SELECT @row:=0) r
		    WHERE 1=1 AND 
            a.status_id = 1 AND 
            a.id_dept = '" . $user_dept_id . "' AND 
            a.no_pr IS NULL AND 
            a.rejected IS NULL AND 
            a.app_post IS NULL AND
            a.close_pr IS NULL
            AND (
				a.no_pengajuan LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.tanggal LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR b.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
			GROUP BY z.no_pengajuan
		";
        // echo $sql; exit;

        $data['totalData'] = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();
        $columns_order_by = array(
            0 => 'nomor',
            1 => 'no_pr',
            2 => 'b.nama'
        );

        $sql .= " ORDER BY id DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
        $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

        $data['query'] = $this->db->query($sql);
        return $data;
    }
    public function query_data_json_non_rutin($tanda, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {

        $where = "";
        if ($tanda == 'approval') {
            $where = "AND a.sts_app = 'N' ";
        }
        $sql = "
			SELECT
				(@row:=@row+1) AS nomor,
				a.*,
				b.nama
			FROM
				rutin_non_planning_detail z
				LEFT JOIN rutin_non_planning_header a ON z.no_pengajuan=a.no_pengajuan
				LEFT JOIN ms_department b ON a.id_dept=b.id,
				(SELECT @row:=0) r
		    WHERE 1=1 " . $where . " AND a.status_id = 1 AND (
				a.no_pengajuan LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.tanggal LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR b.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
			GROUP BY z.no_pengajuan
		";
        // echo $sql; exit;

        $data['totalData'] = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();
        $columns_order_by = array(
            0 => 'nomor',
            1 => 'no_pr',
            2 => 'b.nama'
        );

        $sql .= " ORDER BY id DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
        $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

        $data['query'] = $this->db->query($sql);
        return $data;
    }
}
