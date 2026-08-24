<?php

class Non_rutin_model extends BF_Model
{

    protected $hris;
    public function __construct()
    {
        parent::__construct();
        // Your own constructor code

        $this->hris = $this->load->database('hris', true);
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
                $warna = 'red';
                if ($row['sts_reject3'] == '1') {
                    $sts = 'Rejected by Management';
                } elseif ($row['sts_reject1'] == '1' || $row['sts_reject2'] == '1') {
                    $sts = 'Rejected by Finance';
                } else {
                    $sts = 'Rejected';
                }
            } else {
                if ($row['app_3'] == null) {
                    $warna = 'blue';
                    $sts = 'Waiting Approval';
                } else {
                    if ($row['sts_app'] == 'Y') {
                        $warna = 'green';
                        $sts = 'Approved';
                    }
                }
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
                $warna = 'red';
                if ($row['sts_reject3'] == '1') {
                    $sts = 'Rejected by Management';
                } elseif ($row['sts_reject1'] == '1' || $row['sts_reject2'] == '1') {
                    $sts = 'Rejected by Finance';
                } else {
                    $sts = 'Rejected';
                }
            } else {
                if ($row['app_3'] == null) {
                    $warna = 'blue';
                    $sts = 'Waiting Approval';
                } else {
                    if ($row['sts_app'] == 'Y') {
                        $warna = 'green';
                        $sts = 'Approved';
                    }
                }
            }

            $nestedData[]    = "<div align='left'><span class='badge' style='background-color: " . $warna . ";'>" . $sts . "</span></div>";
            $view        = "<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan'] . '/view') . "' class='btn btn-sm btn-warning' title='View' data-role='qtip'><i class='fa fa-eye'></i></a>";
            $edit        = "";
            $approve    = "";
            $cancel        = "";
            $print    = "&nbsp;<a href='" . base_url('non_rutin/print_pengajuan_non_rutin/' . $row['no_pengajuan']) . "' target='_blank' class='btn btn-sm btn-success' title='Print'><i class='fa fa-print'></i></a>";

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
                $warna = 'red';
                if ($row['sts_reject3'] == '1') {
                    $sts = 'Rejected by Management';
                } elseif ($row['sts_reject1'] == '1' || $row['sts_reject2'] == '1') {
                    $sts = 'Rejected by Finance';
                } else {
                    $sts = 'Rejected';
                }
            } else {
                if ($row['app_3'] == null) {
                    $warna = 'blue';
                    $sts = 'Waiting Approval';
                } else {
                    if ($row['sts_app'] == 'Y') {
                        $warna = 'green';
                        $sts = 'Approved';
                    }
                }
            }

            $nestedData[]    = "<div align='left'><span class='badge' style='background-color: " . $warna . ";'>" . $sts . "</span></div>";
            $view        = "<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan'] . '/view') . "' class='btn btn-sm btn-warning' title='View' data-role='qtip'><i class='fa fa-eye'></i></a>";
            $edit        = "";
            $approve    = "";
            $cancel        = "";
            $print    = "&nbsp;<a href='" . base_url('non_rutin/print_pengajuan_non_rutin/' . $row['no_pengajuan']) . "' target='_blank' class='btn btn-sm btn-success' title='Print'><i class='fa fa-print'></i></a>";

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

            $this->hris->select('a.id, a.name, b.name as nm_company');
            $this->hris->from('departments a');
            $this->hris->join('companies b', 'b.id = a.company_id', 'left');
            $this->hris->where('a.id', $row['id_dept']);
            $get_department = $this->hris->get()->row();

            $tanda = $requestData['tanda'];

            $nestedData     = array();
            $nestedData[]    = "<div align='center'>" . $nomor . "</div>";
            $no_pr = (!empty($row['no_pr'])) ? $row['no_pr'] : "<span class='text-red' title='No Pengajuan'>" . $row['no_pengajuan'] . "</span>";
            $nestedData[]    = "<div align='left'>" . $no_pr . "</div>";
            $nestedData[]    = "<div align='left'>" . strtoupper($get_department->name . ' - ' . $get_department->nm_company) . "</div>";

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
                $arr_tanggal[$val] = "&bull; " . date('d F Y H:i:s', strtotime($valx['created_date']));
                $arr_ket[$val] = "&bull; " . strtoupper($valx['keterangan']);
            }
            $dt_nama_barang    = implode("<br>", $arr_nmbarang);
            $dt_spec    = implode("<br>", $arr_spec);
            $dt_qty    = implode("<br>", $arr_qty);
            $dt_tanggal    = implode("<br>", $arr_tanggal);
            $dt_ket    = implode("<br>", $arr_ket);

            $nestedData[]    = "<div align='left'>" . $row['project_name'] . "</div>";
            $nestedData[]    = "<div align='left'>" . $row['nm_lengkap'] . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_tanggal . "</div>";
            $nestedData[]    = "<div align='left'>" . ucwords(strtolower($row['nm_lengkap'])) . "</div>";
            $nestedData[]    = "<div align='left'>" . (!empty($row['created_date']) ? date('d-M-Y', strtotime($row['created_date'])) : '-') . "</div>";

            $last_by     = (!empty($row['updated_by'])) ? $row['updated_by'] : $row['created_by'];
            $last_date = (!empty($row['updated_date'])) ? $row['updated_date'] : $row['created_date'];

            // $nestedData[]	= "<div align='center'>".$last_by."</div>";
            // $nestedData[]	= "<div align='right'>".date('d-M-Y H:i:s', strtotime($last_date))."</div>";

            if ($row['sts_app'] == 'N') {
                $warna     = 'blue';
                $sts     = 'WAITING APPROVAL: MANAGEMENT';
            } elseif ($row['sts_app'] == 'Y') {
                $warna     = 'green';
                $sts     = 'APPROVED';
            } else {
                $warna     = 'red';
                $sts     = 'REJECTED';
            }

            if (($row['sts_reject1'] !== null || $row['sts_reject2'] !== null || $row['sts_reject3'] !== null) && $row['rejected'] == 1) {
                $warna = 'red';
                if ($row['sts_reject3'] == '1') {
                    $sts = 'Rejected by Management';
                } elseif ($row['sts_reject1'] == '1' || $row['sts_reject2'] == '1') {
                    $sts = 'Rejected by Finance';
                } else {
                    $sts = 'Rejected';
                }
            } else {
                if ($row['app_3'] == null) {
                    $warna = 'blue';
                    $sts = 'Waiting Approval: Management';
                } else {
                    if ($row['sts_app'] == 'Y') {
                        $warna = 'green';
                        $sts = 'Approved';
                    }
                }
            }

            $tingkat_pr = $this->get_tingkat_pr2($row);
            $nestedData[] = $tingkat_pr;

            $nestedData[]    = "<div align='left'><span class='badge' style='background-color: " . $warna . ";'>" . $sts . "</span></div>";
            $view        = "<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan'] . '/view') . "' class='btn btn-sm btn-warning' title='View' data-role='qtip'><i class='fa fa-eye'></i></a>";
            $edit        = "";
            $approve    = "";
            $cancel        = "";
            $print    = "&nbsp;<a href='" . base_url('non_rutin/print_pengajuan_non_rutin/' . $row['no_pengajuan']) . "' target='_blank' class='btn btn-sm btn-success' title='Print'><i class='fa fa-print'></i></a>";

            $view = "";
            $approve = '';
            if ($ENABLE_MANAGE) {
                $check_auth = $this->check_approval_authority($row['no_pengajuan'], $this->auth->user_id());
                if (isset($check_auth['status']) && $check_auth['status']) {
                    $approve    = "&nbsp;<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan'] . '/approve/3') . "' class='btn btn-sm btn-info' title='Approve' data-role='qtip'><i class='fa fa-check'></i></a>";
                }
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

    public function get_employee_hierarchy_info($id_user)
    {
        $this->db->select('
            a.id_user,
            a.username,
            a.nm_lengkap,
            a.employee_id,
            a.department_id as user_dept_id,
            b.id as emp_id,
            b.name as employee_name,
            COALESCE(b.department_id, a.department_id) as department_id,
            b.division_id,
            b.position_id,
            c.name as position_name,
            d.name as department_name,
            e.name as division_name
        ');
        $this->db->from('users a');
        $this->db->join(HRIS . '.employees b', '((a.employee_id IS NOT NULL AND a.employee_id != \'\' AND b.id = a.employee_id) OR ((a.employee_id IS NULL OR a.employee_id = \'\') AND (b.name LIKE CONCAT(\'%\', a.nm_lengkap, \'%\') OR b.name LIKE CONCAT(\'%\', a.username, \'%\'))))', 'left', false);
        $this->db->join(HRIS . '.positions c', 'c.id = b.position_id', 'left');
        $this->db->join(HRIS . '.departments d', 'd.id = COALESCE(b.department_id, a.department_id)', 'left');
        $this->db->join(HRIS . '.divisions e', 'e.id = b.division_id', 'left');
        $this->db->where('a.id_user', $id_user);
        $user_info = $this->db->get()->row();

        if (!$user_info) {
            return null;
        }

        $pos_name = strtolower($user_info->position_name ?? '');
        $nm_lengkap = strtolower($user_info->nm_lengkap ?? '');
        $username = strtolower($user_info->username ?? '');

        // Identifikasi Level / Role
        $is_director = (
            strpos($pos_name, 'director') !== false ||
            strpos($pos_name, 'direktur') !== false ||
            strpos($pos_name, 'komisaris') !== false ||
            strpos($nm_lengkap, 'imanuel') !== false ||
            strpos($username, 'imanuel') !== false ||
            $id_user == '7' ||
            $this->auth->is_admin()
        );

        $is_staff = (
            strpos($pos_name, 'staff') !== false ||
            strpos($pos_name, 'magang') !== false
        );
        $is_head = (
            strpos($pos_name, 'head') !== false ||
            strpos($pos_name, 'manager') !== false ||
            strpos($pos_name, 'leader') !== false ||
            strpos($pos_name, 'supervisor') !== false ||
            strpos($pos_name, 'spv') !== false ||
            strpos($pos_name, 'kepala') !== false ||
            strpos($pos_name, 'penyelia') !== false
        );

        $user_info->is_director = $is_director;
        $user_info->is_staff = $is_staff;
        $user_info->is_head = $is_head;
        $user_info->is_above_staff = (!$is_staff && !empty($pos_name)) || $is_head || $is_director;

        return $user_info;
    }

    public function check_approval_authority($no_pengajuan, $approver_user_id)
    {
        $header = $this->db->get_where('rutin_non_planning_header', ['no_pengajuan' => $no_pengajuan])->row();
        if (!$header) {
            return ['status' => false, 'message' => 'Data pengajuan tidak ditemukan.'];
        }

        // Superadmin bypass
        if ($approver_user_id == '7' || $this->auth->is_admin()) {
            return ['status' => true];
        }

        $creator_user_id = $header->created_by;
        $creator_info = $this->get_employee_hierarchy_info($creator_user_id);
        $approver_info = $this->get_employee_hierarchy_info($approver_user_id);

        if (!$approver_info) {
            return ['status' => false, 'message' => 'Informasi akun approver tidak valid.'];
        }

        // Approver yang merupakan Staff / Magang TIDAK BERHAK melakukan approval
        if ($approver_info->is_staff || !$approver_info->is_above_staff) {
            return ['status' => false, 'message' => 'Anda tidak memiliki wewenang untuk melakukan approval PR. Approval hanya dapat dilakukan oleh Supervisor / Head Department terkait atau Management.'];
        }

        // User tidak boleh menyetujui PR yang dibuat oleh dirinya sendiri
        if ($approver_user_id == $creator_user_id) {
            return ['status' => false, 'message' => 'Anda tidak dapat menyetujui pengajuan PR yang Anda buat sendiri.'];
        }

        // Cek posisi pembuat
        $is_creator_staff = ($creator_info && $creator_info->is_staff);

        if ($is_creator_staff) {
            // Pembuat adalah Staff -> Di-approve oleh Head Department / SPV departemen pembuat atau Direktur
            if ($approver_info->is_director) {
                return ['status' => true];
            }

            $approver_depts = array_unique(array_filter([$approver_info->department_id, $approver_info->user_dept_id]));
            $creator_depts = array_unique(array_filter([$header->id_dept, $creator_info->department_id ?? null, $creator_info->user_dept_id ?? null]));

            $has_dept_match = !empty(array_intersect($approver_depts, $creator_depts));

            $dept_name_match = (
                !empty($approver_info->department_name) &&
                !empty($creator_info->department_name) &&
                strtolower(trim($approver_info->department_name)) === strtolower(trim($creator_info->department_name))
            );

            if (!$has_dept_match && !$dept_name_match) {
                return ['status' => false, 'message' => 'Pengajuan ini dibuat oleh Staff departemen lain dan hanya dapat disetujui oleh Supervisor / Head Department terkait.'];
            }

            return ['status' => true];
        } else {
            // Pembuat adalah > Staff (Head / Manager / SPV / dll.) -> Di-approve oleh Direktur (Imanuel Iman)
            if (!$approver_info->is_director) {
                return ['status' => false, 'message' => 'Pengajuan ini dibuat oleh posisi di atas Staff sehingga harus disetujui oleh Direktur (Imanuel Iman).'];
            }

            return ['status' => true];
        }
    }

    public function query_data_json_non_rutin_approval_management($tanda, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $current_user_id = $this->auth->user_id();
        $curr_user = $this->get_employee_hierarchy_info($current_user_id);
        $is_admin = $this->auth->is_admin();

        $where = "";
        if ($tanda == 'approval') {
            $where .= " AND a.sts_app = 'N' ";
        }

        // Filter berdasarkan hierarki approver
        if ($curr_user) {
            if ($curr_user->is_director || $current_user_id == '7' || $is_admin) {
                // Direktur / Superadmin:
                // Jika bukan superadmin, Direktur melihat PR yang dibuat oleh user dengan posisi > Staff (bukan Staff)
                if ($current_user_id != '7' && !$is_admin) {
                    $where .= " AND (LOWER(creator_pos.name) NOT LIKE '%staff%' OR creator_pos.name IS NULL) ";
                    $where .= " AND a.created_by != " . $this->db->escape($current_user_id) . " ";
                }
            } elseif ($curr_user->is_head || $curr_user->is_above_staff) {
                // Head Department / Supervisor:
                // Hanya melihat PR yang dibuat oleh Staff di departemen yang sama (dan bukan buatannya sendiri)
                $dept_filters = array_unique(array_filter([$curr_user->department_id, $curr_user->user_dept_id]));
                $dept_quoted = array_map(function($d) { return $this->db->escape($d); }, $dept_filters);

                $where .= " AND (LOWER(creator_pos.name) LIKE '%staff%' OR LOWER(creator_pos.name) LIKE '%magang%') ";
                $where .= " AND a.created_by != " . $this->db->escape($current_user_id) . " ";

                $dept_conditions = [];
                if (!empty($dept_quoted)) {
                    $dept_conditions[] = "a.id_dept IN (" . implode(',', $dept_quoted) . ")";
                    $dept_conditions[] = "creator_emp.department_id IN (" . implode(',', $dept_quoted) . ")";
                }
                if (!empty($curr_user->department_name)) {
                    $dept_conditions[] = "LOWER(creator_dept.name) = " . $this->db->escape(strtolower(trim($curr_user->department_name)));
                }

                if (!empty($dept_conditions)) {
                    $where .= " AND (" . implode(" OR ", $dept_conditions) . ") ";
                } else {
                    $where .= " AND 1=0 ";
                }
            } else {
                // User adalah Staff / Magang / bukan approver -> Tidak berhak melihat antrean approval
                $where .= " AND 1=0 ";
            }
        } else {
            if (!$is_admin && $current_user_id != '7') {
                $where .= " AND 1=0 ";
            }
        }

        $sql = "
			SELECT
				(@row:=@row+1) AS nomor,
				a.*, c.nm_lengkap,
                creator_pos.name as creator_position_name,
                creator_emp.department_id as creator_dept_id,
                creator_emp.division_id as creator_div_id
			FROM
				rutin_non_planning_detail z
				LEFT JOIN rutin_non_planning_header a ON z.no_pengajuan=a.no_pengajuan
                LEFT JOIN users c ON c.id_user = a.created_by
                LEFT JOIN " . HRIS . ".employees creator_emp ON (
                    (c.employee_id IS NOT NULL AND c.employee_id != '' AND creator_emp.id = c.employee_id)
                    OR ((c.employee_id IS NULL OR c.employee_id = '') AND (creator_emp.name LIKE CONCAT('%', c.nm_lengkap, '%') OR creator_emp.name LIKE CONCAT('%', c.username, '%')))
                )
                LEFT JOIN " . HRIS . ".positions creator_pos ON creator_pos.id = creator_emp.position_id
                LEFT JOIN " . HRIS . ".departments creator_dept ON creator_dept.id = COALESCE(creator_emp.department_id, a.id_dept)
		    WHERE 1=1 " . $where . " AND 
            a.status_id = 1 AND
            a.rejected IS NULL AND 
            a.no_pr IS NULL AND 
            a.app_1_by IS NOT NULL AND
            a.app_2_by IS NOT NULL AND
            a.close_pr IS NULL
            AND (
				a.no_pengajuan LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.tanggal LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR c.nm_lengkap LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
			GROUP BY z.no_pengajuan
		";
        // echo $sql;
        // exit;

        $data['totalData'] = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();
        $columns_order_by = array(
            0 => 'nomor',
            1 => 'no_pr',
            2 => 'b.nama'
        );

        $sql .= " ORDER BY a.tingkat_pr DESC, id DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
        $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

        $data['query'] = $this->db->query($sql);
        return $data;
    }

    public function query_data_json_non_rutin_approval_finance($tanda, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
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
				a.*, c.nm_lengkap
			FROM
				rutin_non_planning_detail z
				LEFT JOIN rutin_non_planning_header a ON z.no_pengajuan=a.no_pengajuan
                LEFT JOIN users c ON c.id_user = a.created_by
		    WHERE 1=1 " . $where . " AND 
            a.status_id = 1 AND 
            a.rejected IS NULL AND
            a.no_pr IS NULL AND 
            a.app_1_by IS NULL AND 
            a.app_2_by IS NULL AND
            a.close_pr IS NULL
            AND (
				a.no_pengajuan LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.tanggal LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
			GROUP BY z.no_pengajuan
		";
        // echo $sql;
        // exit;

        $data['totalData'] = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();
        $columns_order_by = array(
            0 => 'nomor',
            1 => 'no_pr',
            2 => 'b.nama'
        );

        $sql .= " ORDER BY a.tingkat_pr DESC, id DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
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
                OR a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
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
        // mengambil department_id user yang sedang login
        $get_user_dept_id = $this->db->select('department_id')
            ->get_where('users', ['id_user' => $this->auth->user_id()])
            ->row_array();
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
		    WHERE 1=1 " . $where . " AND a.id_dept = '" . $user_dept_id . "' AND a.status_id = 1 AND (
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

    public function server_side_non_rutin_approval_finance()
    {
        $ENABLE_ADD     = has_permission('Approval_PR_Department_Finance.Add');
        $ENABLE_MANAGE  = has_permission('Approval_PR_Department_Finance.Manage');
        $ENABLE_VIEW    = has_permission('Approval_PR_Department_Finance.View');
        $ENABLE_DELETE  = has_permission('Approval_PR_Department_Finance.Delete');

        $requestData    = $_REQUEST;
        $fetch            = $this->query_data_json_non_rutin_approval_finance(
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

            $this->hris->select('a.id, a.name, b.name as nm_company');
            $this->hris->from('departments a');
            $this->hris->join('companies b', 'b.id = a.company_id', 'left');
            $this->hris->where('a.id', $row['id_dept']);
            $get_department = $this->hris->get()->row();

            $tanda = $requestData['tanda'];

            $nestedData     = array();
            $nestedData[]    = "<div align='center'>" . $nomor . "</div>";
            $no_pr = (!empty($row['no_pr'])) ? $row['no_pr'] : "<span class='text-red' title='No Pengajuan'>" . $row['no_pengajuan'] . "</span>";
            $nestedData[]    = "<div align='left'>" . $no_pr . "</div>";
            $nestedData[]    = "<div align='left'>" . strtoupper($get_department->name . ' - ' . $get_department->nm_company) . "</div>";

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
                $arr_tanggal[$val] = "&bull; " . date('d F Y H:i:s', strtotime($valx['created_date']));
                $arr_ket[$val] = "&bull; " . strtoupper($valx['keterangan']);
            }
            $dt_nama_barang    = implode("<br>", $arr_nmbarang);
            $dt_spec    = implode("<br>", $arr_spec);
            $dt_qty    = implode("<br>", $arr_qty);
            $dt_tanggal    = implode("<br>", $arr_tanggal);
            $dt_ket    = implode("<br>", $arr_ket);

            $nestedData[]    = "<div align='left'>" . $row['project_name'] . "</div>";
            $nestedData[]    = "<div align='left'>" . $row['nm_lengkap'] . "</div>";
            $nestedData[]    = "<div align='left'>" . $dt_tanggal . "</div>";
            $nestedData[]    = "<div align='left'>" . ucwords(strtolower($row['nm_lengkap'])) . "</div>";
            $nestedData[]    = "<div align='left'>" . (!empty($row['created_date']) ? date('d-M-Y', strtotime($row['created_date'])) : '-') . "</div>";

            $last_by     = (!empty($row['updated_by'])) ? $row['updated_by'] : $row['created_by'];
            $last_date = (!empty($row['updated_date'])) ? $row['updated_date'] : $row['created_date'];

            // $nestedData[]	= "<div align='center'>".$last_by."</div>";
            // $nestedData[]	= "<div align='right'>".date('d-M-Y H:i:s', strtotime($last_date))."</div>";

            if ($row['sts_app'] == 'N') {
                $warna     = 'blue';
                $sts     = 'WAITING APPROVAL: FINANCE';
            } elseif ($row['sts_app'] == 'Y') {
                $warna     = 'green';
                $sts     = 'APPROVED';
            } else {
                $warna     = 'red';
                $sts     = 'REJECTED';
            }

            if (($row['sts_reject1'] !== null || $row['sts_reject2'] !== null || $row['sts_reject3'] !== null) && $row['rejected'] == 1) {
                $warna = 'red';
                if ($row['sts_reject3'] == '1') {
                    $sts = 'Rejected by Management';
                } elseif ($row['sts_reject1'] == '1' || $row['sts_reject2'] == '1') {
                    $sts = 'Rejected by Finance';
                } else {
                    $sts = 'Rejected';
                }
            } else {
                if ($row['app_3'] == null) {
                    $warna = 'blue';
                    $sts = 'Waiting Approval: Finance';
                } else {
                    if ($row['sts_app'] == 'Y') {
                        $warna = 'green';
                        $sts = 'Approved';
                    }
                }
            }

            $tingkat_pr = $this->get_tingkat_pr2($row);

            $nestedData[] = $tingkat_pr;

            $nestedData[]    = "<div align='left'><span class='badge' style='background-color: " . $warna . ";'>" . $sts . "</span></div>";


            $view        = "<a href='" . base_url('non_rutin/add/' . $row['no_pengajuan'] . '/view') . "' class='btn btn-sm btn-warning' title='View' data-role='qtip'><i class='fa fa-eye'></i></a>";
            $edit        = "";
            $approve    = "";
            $cancel        = "";
            $print    = "&nbsp;<a href='" . base_url('non_rutin/print_pengajuan_non_rutin/' . $row['no_pengajuan']) . "' target='_blank' class='btn btn-sm btn-success' title='Print'><i class='fa fa-print'></i></a>";

            $view = "";
            // $approve = '';
            // if ($ENABLE_MANAGE) {
            $approve    = "&nbsp;<a href='" . base_url('non_rutin/add_finance/' . $row['no_pengajuan'] . '/approve/3') . "' class='btn btn-sm btn-info' title='Approve' data-role='qtip'><i class='fa fa-check'></i></a>";


            // }
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

    private function _main_query($limit = null, $offset = 0, $where = null)
    {
        $is_admin = $this->auth->is_admin();
        $user_id = $this->auth->user_id();
        $user_dept_id = '';
        if (!$is_admin) {
            $get_emp = $this->get_employee_hierarchy_info($user_id);
            $user_dept_id = $get_emp->department_id ?? '';
        }

        $this->db->select('a.*, c.nm_lengkap, d.name as nm_dept, e.name as nm_company');
        $this->db->select('GROUP_CONCAT(DISTINCT kb.no_doc SEPARATOR ", ") as no_doc_kasbon', FALSE);
        $this->db->select('GROUP_CONCAT(DISTINCT np.no_non_po SEPARATOR ", ") as no_doc_non_po', FALSE);
        $this->db->select('GROUP_CONCAT(DISTINCT po.no_po SEPARATOR ", ") as no_doc_po', FALSE);
        $this->db->select('MIN(kb.created_on) as tgl_proses_kasbon', FALSE);
        $this->db->select('MIN(np.created_date) as tgl_proses_non_po', FALSE);
        $this->db->select('MIN(po.created_on) as tgl_proses_po', FALSE);
        $this->db->select('MIN(tp.created_at) as tgl_tracking_pembelian', FALSE);
        // Status fields dari dokumen terkait
        $this->db->select('MAX(kb.status) as kasbon_status', FALSE);
        $this->db->select('MAX(kb.sts_finance) as kasbon_sts_finance', FALSE);
        $this->db->select('MAX(kb.sts_reject) as kasbon_sts_reject', FALSE);
        $this->db->select('MAX(kb.sts_reject_manage) as kasbon_sts_reject_manage', FALSE);
        $this->db->select('MAX(po.status) as po_status', FALSE);
        $this->db->select('MAX(CASE WHEN po.reject_reason IS NOT NULL AND po.reject_reason != "" THEN 1 ELSE 0 END) as po_rejected', FALSE);
        $this->db->from('rutin_non_planning_detail z');
        $this->db->join('rutin_non_planning_header a', 'z.no_pengajuan = a.no_pengajuan', 'left');
        $this->db->join('users c', 'c.id_user = a.created_by', 'left');
        $this->db->join('departments d', 'd.id = a.id_dept', 'left');
        $this->db->join('hris_companies e', 'e.id = d.company_id', 'left');
        $this->db->join('tr_pr_detail_kasbon pdk', 'pdk.id_detail = z.id', 'left');
        $this->db->join('tr_kasbon kb', 'kb.no_doc = pdk.id_kasbon', 'left');
        $this->db->join('tr_pr_non_po np', "np.no_pr = a.no_pr AND np.jenis_pr = 'pr departemen'", 'left', FALSE);
        $this->db->join('tr_purchase_order po', 'po.no_pr = a.no_pr', 'left');
        $this->db->join('tr_tracking_pembelian tp', 'tp.no_pr = a.no_pr', 'left');
        $this->db->where('a.status_id', 1);

        if (!$is_admin) {
            $this->db->where('a.id_dept', $user_dept_id);
        }

        if (!empty($where)) {
            $arr_where = $where;

            $this->db->group_start();
            $no = 1;
            foreach ($arr_where as $key => $value) {
                if ($no == 1) : $this->db->like($key, $value);
                else : $this->db->or_like($key, $value);
                endif;
                $no++;
            }
            $this->db->group_end();
        }

        $this->db->where('a.close_pr', null);
        $this->db->group_by('z.no_pengajuan');
        $this->db->order_by('a.tingkat_pr', 'DESC');
        // Order by status: waiting (0) → rejected (1) → approved (2)
        $this->db->order_by('CASE WHEN (a.rejected IS NULL OR a.rejected != 1) AND a.app_3 IS NULL THEN 0 WHEN a.rejected = 1 THEN 1 ELSE 2 END', 'ASC', FALSE);
        // Urgent (2) before Normal (1)
        $this->db->order_by('a.no_pr IS NOT NULL', 'ASC', FALSE);
        $this->db->order_by('a.created_date', 'DESC');

        if ($limit !== null && $limit != -1) {
            $this->db->limit($limit, $offset);
        }

        $get_data = $this->db->get();

        return $get_data;
    }

    public function get_data_non_rutin($draw, $length, $start, $search, $order, $columns)
    {
        $query_all = $this->_main_query();

        if (!$query_all) {
            $error = $this->db->error();
            echo json_encode([
                'draw' => intval($draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => "SQL Error: " . $error['message'] . " | Query: " . $this->db->last_query()
            ]);
            exit;
        }

        $total_all = $query_all->num_rows();

        $arr_where = [];
        if (!empty($search['value'])) {
            $arr_where = [
                'a.no_pengajuan' => $search['value'],
                'a.no_pr' => $search['value'],
                'd.name' => $search['value'],
                'e.name' => $search['value'],
                'c.nm_lengkap' => $search['value'],
                'a.project_name' => $search['value']
            ];
        }
        $query_filter = $this->_main_query(null, null, $arr_where);

        $total_filter = $query_filter->num_rows();

        $query_final = $this->_main_query($length, $start, $arr_where);

        $hasil = [];
        $no = (0 + $start);
        foreach ($query_final->result() as $item) {
            $no++;
            $status = $this->get_status($item);
            $options = $this->get_option($item);
            $tingkat_pr = $this->get_tingkat_pr($item);
            $status_dokumen = $this->get_status_dokumen($item);

            $no_dokumen_parts = [];
            if (!empty($item->no_doc_kasbon)) {
                $no_dokumen_parts[] = $item->no_doc_kasbon;
            }
            if (!empty($item->no_doc_non_po)) {
                $no_dokumen_parts[] = $item->no_doc_non_po;
            }
            $no_dokumen = implode(', ', $no_dokumen_parts);

            // Tentukan tanggal diproses berdasarkan metode pembelian
            $tgl_diproses = '-';
            if (!empty($item->tgl_tracking_pembelian)) {
                $tgl_diproses = date('d M Y H:i', strtotime($item->tgl_tracking_pembelian));
            } elseif (!empty($item->tgl_proses_po)) {
                $tgl_diproses = date('d M Y H:i', strtotime($item->tgl_proses_po));
            } elseif (!empty($item->tgl_proses_kasbon)) {
                $tgl_diproses = date('d M Y H:i', strtotime($item->tgl_proses_kasbon));
            } elseif (!empty($item->tgl_proses_non_po)) {
                $tgl_diproses = date('d M Y H:i', strtotime($item->tgl_proses_non_po));
            }

            $hasil[] = [
                'no' => $no,
                'no_pr' => (!empty($item->no_pr)) ? $item->no_pr : '<span class="text-red">' . $item->no_pengajuan . '</span>',
                'no_dokumen' => $no_dokumen,
                'status_dokumen' => $status_dokumen,
                'departemen' => strtoupper($item->nm_dept . ' - ' . $item->nm_company),
                'keterangan' => $item->project_name,
                'tingkat_pr' => $tingkat_pr,
                'pic' => $item->nm_lengkap,
                'created_date' => date('d F Y H:i:s', strtotime($item->created_date)),
                'tgl_diproses' => $tgl_diproses,
                'status' => $status,
                'option' => $options
            ];
        }

        $response = [
            'draw' => intval($draw),
            'recordsTotal' => $total_all,
            'recordsFiltered' => $total_filter,
            'data' => $hasil
        ];

        echo json_encode($response);
    }

    public function get_status($data)
    {
        if ($data->sts_app == 'N') {
            $warna     = 'blue';
            $sts     = 'WAITING APPROVAL';
        } elseif ($data->sts_app == 'Y') {
            $warna     = 'green';
            $sts     = 'APPROVED';
        } else {
            $warna     = 'red';
            $sts     = 'REJECTED';
        }

        if (($data->sts_reject1 !== null || $data->sts_reject2 !== null || $data->sts_reject3 !== null) && $data->rejected == 1) {
            $warna = 'red';
            // Finance reject: sts_reject1 dan sts_reject2 di-set bersamaan oleh add_finance
            // Management reject: sts_reject3 di-set oleh add (tingkat 3)
            if ($data->sts_reject3 == '1') {
                $sts = 'Rejected by Management';
            } elseif ($data->sts_reject1 == '1' || $data->sts_reject2 == '1') {
                $sts = 'Rejected by Finance';
            } else {
                $sts = 'Rejected';
            }
        } else {
            if ($data->app_3 == null) {
                $warna = 'blue';
                // Tentukan waiting approval di level mana
                if (empty($data->app_1_by) && empty($data->app_2_by)) {
                    $sts = 'Waiting Approval: Finance';
                } else {
                    $sts = 'Waiting Approval: Management';
                }
            } else {
                if ($data->sts_app == 'Y') {
                    $warna = 'green';
                    $sts   = 'Approved';

                    // Jika PR sudah Approved, tampilkan status dari PO atau Kasbon yang terkait
                    $metode = $data->metode_pembelian;

                    if ($metode == '1' && !empty($data->no_doc_po)) {
                        // Metode PO: cek status dari tr_purchase_order
                        $po_status   = $data->po_status;
                        $po_rejected = $data->po_rejected;

                        if ($po_rejected == '1') {
                            $warna = 'red';
                            $sts   = 'PO: Ditolak';
                        } elseif ($po_status >= 2) {
                            $warna = 'green';
                            $sts   = 'PO: Disetujui';
                        } else {
                            $warna = 'orange';
                            $sts   = 'PO: Menunggu Persetujuan';
                        }
                    } elseif ($metode == '2' && !empty($data->no_doc_kasbon)) {
                        // Metode Kasbon: cek status dari tr_kasbon
                        // status: 0=waiting, 1/2=approved, 3=paid/close, 9=reject
                        // sts_finance: 0=waiting approval finance, 1=waiting approval management
                        $kasbon_status       = $data->kasbon_status;
                        $kasbon_sts_finance  = $data->kasbon_sts_finance;

                        if ($kasbon_status == '9') {
                            // Ditolak
                            $warna = 'red';
                            $sts   = 'Kasbon: Ditolak';
                        } elseif ($kasbon_status == '3') {
                            // Sudah dibayar / close
                            $warna = 'purple';
                            $sts   = 'Kasbon: Lunas';
                        } elseif ($kasbon_status == '1' || $kasbon_status == '2') {
                            // Approved oleh management
                            $warna = 'green';
                            $sts   = 'Kasbon: Disetujui';
                        } elseif ($kasbon_status == '0' || $kasbon_status === null) {
                            // Masih dalam proses approval
                            if ($kasbon_sts_finance == '1') {
                                $warna = 'orange';
                                $sts   = 'Kasbon: Waiting Approval Management';
                            } else {
                                $warna = 'orange';
                                $sts   = 'Kasbon: Waiting Approval Finance';
                            }
                        }
                    }
                    // Metode Cash (metode_pembelian = 3) tidak ditampilkan status lanjutan
                }
            }
        }

        return '<span class="badge" style="background-color: ' . $warna . '">' . $sts . '</span>';
    }

    public function get_status_dokumen($data)
    {
        $metode = $data->metode_pembelian;

        // Belum ditentukan metode pembelian
        if (empty($metode)) {
            return '<span class="badge bg-gray">Belum Diproses</span>';
        }

        switch ($metode) {
            case '1': // PO
                if (!empty($data->no_doc_po)) {
                    return '<span class="badge bg-green">PO Dibuat</span>';
                }
                return '<span class="badge bg-yellow">Menunggu PO</span>';

            case '2': // Kasbon
                if (!empty($data->no_doc_kasbon)) {
                    return '<span class="badge bg-green">Kasbon Dibuat</span>';
                }
                return '<span class="badge bg-yellow">Menunggu Kasbon</span>';

            case '3': // Cash / Non-PO
                if (!empty($data->no_doc_non_po)) {
                    return '<span class="badge bg-green">Pembelian Cash Dibuat</span>';
                }
                return '<span class="badge bg-yellow">Menunggu Pembelian Cash</span>';

            default:
                return '<span class="badge bg-gray">Belum Diproses</span>';
        }
    }

    public function get_option($data)
    {
        $ENABLE_ADD     = has_permission('PR_Departemen.Add');
        $ENABLE_MANAGE  = has_permission('PR_Departemen.Manage');
        $ENABLE_VIEW    = has_permission('PR_Departemen.View');
        $ENABLE_DELETE  = has_permission('PR_Departemen.Delete');

        $view        = "<a href='" . base_url('non_rutin/add/' . $data->no_pengajuan . '/view') . "' class='btn btn-sm btn-warning' title='View' data-role='qtip'><i class='fa fa-eye'></i></a>";
        $edit        = "";
        $approve    = "";
        $cancel        = "";
        $print    = "&nbsp;<a href='" . base_url('non_rutin/print_pengajuan_non_rutin/' . $data->no_pengajuan) . "' target='_blank' class='btn btn-sm btn-success' title='Print'><i class='fa fa-print'></i></a>";

        if ($data->sts_app == 'N' || $data->sts_app == '') {
            $edit    = "&nbsp;<a href='" . base_url('non_rutin/add/' . $data->no_pengajuan) . "' class='btn btn-sm btn-primary' title='Edit' data-role='qtip'><i class='fa fa-edit'></i></a>";
        }

        $close = '';
        if ($ENABLE_DELETE) {
            $close = '<button type="button" class="btn btn-sm btn-danger close_pr_modal" data-no_pengajuan="' . $data->no_pengajuan . '" title="Close PR"><i class="fa fa-close"></i></button>';
        }

        $hasil = $view . ' ' . $edit . ' ' . $approve . ' ' . $cancel . ' ' . $print . ' ' . $close;
        return $hasil;
    }

    public function get_tingkat_pr($data)
    {
        $tingkat_pr = '<span class="badge bg-blue">Normal</span>';
        if ($data->tingkat_pr == '2') {
            $tingkat_pr = '<span class="badge bg-red">Urgent</span>';
        }

        return $tingkat_pr;
    }

    public function get_tingkat_pr2($data)
    {
        $tingkat_pr = '<span class="badge bg-blue">Normal</span>';
        if ($data['tingkat_pr'] == '2') {
            $tingkat_pr = '<span class="badge bg-red">Urgent</span>';
        }

        return $tingkat_pr;
    }
}
