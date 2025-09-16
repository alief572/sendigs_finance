<div class="list_late_po_release">
    <h4>Late PO Release</h4>
    <table class="table table-bordered w-100 late_po_release_datatable">
        <thead class="bg-yellow text-white">
            <tr>
                <th class="text-center">No.</th>
                <th class="text-center">No. PR</th>
                <th class="text-center">Tipe</th>
                <th class="text-center">Department</th>
                <th class="text-center">Late</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            foreach ($list_all_approved_pr as $item1) {
                if ($item1['no_pr'] !== '') {
                    if ($item1['tipe'] == 'non depart') {
                        $check_closing = $this->db->get_where('material_planning_base_on_produksi_detail', ['so_number' => $item1['so_number'], 'status_app' => 'Y'])->result();

                        if (count($check_closing) > 0) {
                            $get_approval_data = $this->db->select('DATE_FORMAT(app_date, "%Y-%m-%d") as approval_date')->get_where('material_planning_base_on_produksi_detail', ['so_number' => $item1['so_number'], 'status_app' => 'Y'])->row_array();

                            $approval_date = $get_approval_data['approval_date'];
                            $max_po_release_date = new DateTime($approval_date);
                            $max_po_release_date->modify('+4 days');

                            $arr_id = array();
                            $arr_material_id = array();
                            $get_id_detail = $this->db->select('id, id_material')->get_where('material_planning_base_on_produksi_detail', ['so_number' => $item1['so_number']])->result_array();
                            foreach ($get_id_detail as $item_id_detail) {
                                $arr_id[] = $item_id_detail['id'];
                                $arr_material_id[] = $item_id_detail['id_material'];
                            }

                            $this->db->select('b.created_on');
                            $this->db->from('dt_trans_po a');
                            $this->db->join('tr_purchase_order b', 'b.no_po = a.no_po', 'left');
                            $this->db->where_in('a.idpr', $arr_id);
                            $this->db->where_in('a.idmaterial', $arr_material_id);
                            $this->db->where_in('a.tipe', array(null, ''));

                            $get_po = $this->db->get()->row_array();

                            $tgl_release_po = '';
                            if (!empty($get_po['created_on'])) {
                                $tgl_release_po = date('d F Y', strtotime($get_po['created_on']));
                            }

                            $late = '';
                            $kpi = '';
                            if ($tgl_release_po !== "" && $tgl_release_po !== null) {
                                $rel_po_date = new DateTime(date('Y-m-d', strtotime($tgl_release_po)));
                                $late = 'Close';

                                $kpi = $max_po_release_date->diff($rel_po_date)->format('%a Hari');
                                if (strtotime($rel_po_date->format('Y-m-d')) <= strtotime($max_po_release_date->format('Y-m-d'))) {
                                    $kpi = '0 Hari';
                                }
                            } else {
                                $rel_po_date = new DateTime(date('Y-m-d'));
                                $late = $max_po_release_date->diff($rel_po_date)->format('%a Hari');
                                $kpi = $late;

                                if (strtotime($rel_po_date->format('Y-m-d')) <= strtotime($max_po_release_date->format('Y-m-d'))) {
                                    $late = '0 Hari';
                                    $kpi = '0 Hari';
                                }
                            }

                            if ($kpi !== '0 Hari' && $late !== 'Close') {
                                echo '<tr>';
                                echo '<td class="text-center">' . $no . '</td>';
                                echo '<td class="text-center">' . $item1['no_pr'] . '</td>';
                                echo '<td class="text-center">' . strtoupper($item1['tipe_pr']) . '</td>';
                                echo '<td class="text-center">PPIC</td>';
                                echo '<td class="text-center">' . $kpi . '</td>';
                                echo '</tr>';

                                $no++;
                            }
                        }
                    } else {
                        $check_closing = $this->db->get_where('rutin_non_planning_header', ['no_pengajuan' => $item1['so_number']])->result();


                        if (count($check_closing) > 0 && $item1['no_pr'] !== "" && $item1['no_pr'] !== null) {

                            $get_approval_data = $this->db->select('DATE_FORMAT(sts_app_date, "%Y-%m-%d") as approval_date')->get_where('rutin_non_planning_header', ['no_pengajuan' => $item1['so_number']])->row_array();



                            $approval_date = $get_approval_data['approval_date'];
                            $max_po_release_date = new DateTime($approval_date);
                            $max_po_release_date->modify('+4 days');

                            // if($item1['no_pr'] == "PRN24030007"){
                            //     print_r($max_po_release_date->format('d F Y'));
                            // }

                            $arr_id = array();
                            $get_id_detail = $this->db->select('id')->get_where('rutin_non_planning_detail', ['no_pengajuan' => $item1['so_number']])->result_array();
                            foreach ($get_id_detail as $item_id_detail) {
                                $arr_id[] = $item_id_detail['id'];
                            }

                            $this->db->select('b.created_on');
                            $this->db->from('dt_trans_po a');
                            $this->db->join('tr_purchase_order b', 'b.no_po = a.no_po', 'left');
                            $this->db->where_in('a.idpr', $arr_id);
                            $this->db->where('a.tipe', 'pr depart');

                            $get_po = $this->db->get()->row_array();

                            $tgl_release_po = '';
                            if (!empty($get_po['created_on'])) {
                                $tgl_release_po = date('d F Y', strtotime($get_po['created_on']));
                            }

                            $late = '';
                            $kpi = '';
                            if ($tgl_release_po !== "" && $tgl_release_po !== null) {
                                $rel_po_date = new DateTime(date('Y-m-d', strtotime($tgl_release_po)));
                                $late = 'Close';
                                $kpi = $rel_po_date->diff($max_po_release_date)->format('%a Hari');
                                if (strtotime($rel_po_date->format('Y-m-d')) <= strtotime($max_po_release_date->format('Y-m-d'))) {
                                    $kpi = '0 Hari';
                                }
                            } else {
                                $rel_po_date = new DateTime(date('Y-m-d'));
                                $late = $max_po_release_date->diff($rel_po_date)->format('%a Hari');
                                $kpi = $late;
                                if (strtotime($rel_po_date->format('Y-m-d')) <= strtotime($max_po_release_date->format('Y-m-d'))) {
                                    $kpi = '0 Hari';
                                    $late = '0 Hari';
                                }
                            }

                            $this->db->select('b.nama');
                            $this->db->from('rutin_non_planning_header a');
                            $this->db->join('ms_department b', 'b.id = a.id_dept', 'left');
                            $this->db->where('a.no_pr', $item1['no_pr']);
                            $get_nm_department = $this->db->get()->row_array();

                            $nm_department = (!empty($get_nm_department) && $get_nm_department['nama'] !== null) ? $get_nm_department['nama'] : null;

                            if ($kpi !== '0 Hari' && $late !== 'Close') {
                                echo '<tr>';
                                echo '<td class="text-center">' . $no . '</td>';
                                echo '<td class="text-center">' . $item1['no_pr'] . '</td>';
                                echo '<td class="text-center">' . strtoupper($item1['tipe_pr']) . '</td>';
                                echo '<td class="text-center">' . ucfirst($nm_department) . '</td>';
                                echo '<td class="text-center">' . $kpi . '</td>';
                                echo '</tr>';

                                $no++;
                            }
                        }
                    }
                }
            }
            ?>
        </tbody>
    </table>

    <br><br>

    <table class="table table-bordered w-100 late_detail_po_release_datatable">
        <thead class="bg-yellow text-white">
            <tr>
                <th class="text-center">No.</th>
                <th class="text-center">No. PR</th>
                <th class="text-center">Tipe PR</th>
                <th class="text-center">No. PO</th>
                <th class="text-center">No. Incoming</th>
                <th class="text-center">PR Date</th>
                <th class="text-center">PO Release (H+4)</th>
                <th class="text-center">Today</th>
                <th class="text-center">Aktual</th>
                <th class="text-center">Late</th>
                <th class="text-center">KPI</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $arr_summ = array();
            foreach ($list_all_approved_pr as $item1) {
                if ($item1['no_pr'] !== '') {
                    if ($item1['tipe'] == 'non depart') {
                        $check_closing = $this->db->get_where('material_planning_base_on_produksi_detail', ['so_number' => $item1['so_number'], 'status_app' => 'Y'])->result();

                        if (count($check_closing) > 0) {
                            $get_approval_data = $this->db->select('DATE_FORMAT(app_date, "%Y-%m-%d") as approval_date')->get_where('material_planning_base_on_produksi_detail', ['so_number' => $item1['so_number'], 'status_app' => 'Y'])->row_array();

                            $approval_date = $get_approval_data['approval_date'];
                            $max_po_release_date = new DateTime($approval_date);
                            $max_po_release_date->modify('+4 days');

                            $arr_id = array();
                            $arr_material_id = array();
                            $get_id_detail = $this->db->select('id, id_material')->get_where('material_planning_base_on_produksi_detail', ['so_number' => $item1['so_number']])->result_array();
                            foreach ($get_id_detail as $item_id_detail) {
                                $arr_id[] = $item_id_detail['id'];
                                $arr_material_id[] = $item_id_detail['id_material'];
                            }

                            $this->db->select('b.created_on');
                            $this->db->from('dt_trans_po a');
                            $this->db->join('tr_purchase_order b', 'b.no_po = a.no_po', 'left');
                            $this->db->where_in('a.idpr', $arr_id);
                            $this->db->where_in('a.idmaterial', $arr_material_id);
                            $this->db->where_in('a.tipe', array(null, ''));

                            $get_po = $this->db->get()->row_array();

                            $tgl_release_po = '';
                            if ($get_po['created_on'] !== '' && $get_po['created_on'] !== '0000-00-00' && $get_po['created_on'] !== null) {
                                $tgl_release_po = date('d F Y', strtotime($get_po['created_on']));
                            }

                            $late = '';
                            $kpi = '';
                            if ($tgl_release_po !== "" && $tgl_release_po !== null) {
                                $rel_po_date = new DateTime(date('Y-m-d', strtotime($tgl_release_po)));
                                $late = 'Close';

                                $kpi = $max_po_release_date->diff($rel_po_date)->format('%a Hari');
                                if (strtotime($rel_po_date->format('Y-m-d')) <= strtotime($max_po_release_date->format('Y-m-d'))) {
                                    $kpi = '0 Hari';
                                }
                            } else {
                                $rel_po_date = new DateTime(date('Y-m-d'));
                                $late = $max_po_release_date->diff($rel_po_date)->format('%a Hari');
                                $kpi = $late;

                                if (strtotime($rel_po_date->format('Y-m-d')) <= strtotime($max_po_release_date->format('Y-m-d'))) {
                                    $late = '0 Hari';
                                    $kpi = '0 Hari';
                                }
                            }

                            if ($late == 'Close') {
                                $arr_summ[] = [
                                    'no_pr' => $item1['no_pr'],
                                    'max_po_release_date' => $max_po_release_date->format('Y-m-d'),
                                    'kpi' => $kpi,
                                    'late' => $late
                                ];
                            }

                            // if($item1['no_pr'] == 'PR240400009'){
                            //     print_r($arr_material_id);
                            // }

                            $no_po = [];
                            $no_incoming = [];
                            if ($tgl_release_po !== '') {
                                $detailIdArr = [];

                                $this->db->select('b.id');
                                $this->db->from('material_planning_base_on_produksi a');
                                $this->db->join('material_planning_base_on_produksi_detail b', 'b.so_number = a.so_number');
                                $this->db->where('a.no_pr', $item1['no_pr']);
                                $get_detail_pr = $this->db->get()->result();

                                foreach ($get_detail_pr as $detail_pr) {
                                    $detailIdArr[] = $detail_pr->id;
                                }

                                if (!empty($detailIdArr)) {
                                    $detailIdArr = implode(',', $detailIdArr);

                                    $get_no_po = $this->db->query("SELECT a.no_surat, a.no_po FROM tr_purchase_order a JOIN dt_trans_po b ON b.no_po = a.no_po WHERE b.idpr IN ('" . str_replace(",", "','", $detailIdArr) . "') AND (a.tipe IS NULL OR a.tipe = '') GROUP BY a.no_surat")->result();

                                    foreach ($get_no_po as $item_po) {
                                        $no_po[] = $item_po->no_surat;

                                        $this->db->select('a.kode_trans');
                                        $this->db->from('tr_incoming_check a');
                                        $this->db->like('a.no_ipp', $item_po->no_po);
                                        $get_incoming = $this->db->get()->result();
                                        foreach ($get_incoming as $item_incoming) {
                                            $no_incoming[] = $item_incoming->kode_trans;
                                        }

                                        $this->db->select('a.kode_trans');
                                        $this->db->from('warehouse_adjustment_detail a');
                                        $this->db->like('a.no_ipp', $item_po->no_po);
                                        $get_incoming_warehouse = $this->db->get()->result();
                                        foreach ($get_incoming_warehouse as $item_incoming_wa) {
                                            $no_incoming[] = $item_incoming_wa->kode_trans;
                                        }
                                    }

                                    $no_po = implode(', ', $no_po);
                                    $no_incoming = implode(', ', $no_incoming);
                                }
                            } else {
                                $no_po = '';
                            }

                            if (empty($no_po)) {
                                $no_po = '';
                            }
                            if (empty($no_incoming)) {
                                $no_incoming = '';
                            }

                            $list_id_detail_pr = [];
                            // $this->db->select('a.id');
                            // $this->db->from('rutin_non_planning_detail a');
                            // $this->db->join('rutin_non_planing_header b', 'b.no_pengajuan = a.no_pengajuan');
                            // $this->db->where('a.no_pr', $item1['no_pr']);
                            // $data_pr = $this->db->get()->result();

                            echo '<tr>';
                            echo '<td class="text-center">' . $no . '</td>';
                            echo '<td class="text-center">' . $item1['no_pr'] . '</td>';
                            echo '<td class="text-center">' . strtoupper($item1['tipe_pr']) . '</td>';
                            echo '<td class="text-center">' . $no_po . '</td>';
                            echo '<td class="text-center">' . $no_incoming . '</td>';
                            echo '<td class="text-center">' . date('d F Y', strtotime($item1['pr_date'])) . '</td>';
                            echo '<td class="text-center">' . $max_po_release_date->format('d F Y') . '</td>';
                            echo '<td class="text-center">' . date('d F Y') . '</td>';
                            echo '<td class="text-center">' . $tgl_release_po . '</td>';
                            echo '<td class="text-center">' . $late . '</td>';
                            echo '<td class="text-center">' . $kpi . '</td>';
                            echo '</tr>';

                            $no++;
                        }
                    } else {
                        $check_closing = $this->db->get_where('rutin_non_planning_header', ['no_pengajuan' => $item1['so_number']])->result();


                        if (count($check_closing) > 0 && $item1['no_pr'] !== "" && $item1['no_pr'] !== null) {

                            $get_approval_data = $this->db->select('DATE_FORMAT(sts_app_date, "%Y-%m-%d") as approval_date')->get_where('rutin_non_planning_header', ['no_pengajuan' => $item1['so_number'], 'app_post' => 4])->row_array();



                            $approval_date = $get_approval_data['approval_date'];
                            $max_po_release_date = new DateTime($approval_date);
                            $max_po_release_date->modify('+4 days');

                            // if($item1['no_pr'] == "PRN24030007"){
                            //     print_r($max_po_release_date->format('d F Y'));
                            // }

                            $arr_id = array();
                            $get_id_detail = $this->db->select('id')->get_where('rutin_non_planning_detail', ['no_pengajuan' => $item1['so_number']])->result_array();
                            foreach ($get_id_detail as $item_id_detail) {
                                $arr_id[] = $item_id_detail['id'];
                            }

                            $this->db->select('b.created_on');
                            $this->db->from('dt_trans_po a');
                            $this->db->join('tr_purchase_order b', 'b.no_po = a.no_po', 'left');
                            $this->db->where_in('a.idpr', $arr_id);
                            $this->db->where('a.tipe', 'pr depart');

                            $get_po = $this->db->get()->row_array();

                            $tgl_release_po = '';
                            if (!empty($get_po['created_on'])) {
                                $tgl_release_po = date('d F Y', strtotime($get_po['created_on']));
                            }

                            $late = '';
                            $kpi = '';
                            if ($tgl_release_po !== "" && $tgl_release_po !== null) {
                                $rel_po_date = new DateTime(date('Y-m-d', strtotime($tgl_release_po)));
                                $late = 'Close';
                                $kpi = $rel_po_date->diff($max_po_release_date)->format('%a Hari');
                                if (strtotime($rel_po_date->format('Y-m-d')) <= strtotime($max_po_release_date->format('Y-m-d'))) {
                                    $kpi = '0 Hari';
                                }
                            } else {
                                $rel_po_date = new DateTime(date('Y-m-d'));
                                $late = $max_po_release_date->diff($rel_po_date)->format('%a Hari');
                                $kpi = $late;
                                if (strtotime($rel_po_date->format('Y-m-d')) <= strtotime($max_po_release_date->format('Y-m-d'))) {
                                    $kpi = '0 Hari';
                                    $late = '0 Hari';
                                }
                            }

                            if ($late == 'Close') {
                                $arr_summ[] = [
                                    'no_pr' => $item1['no_pr'],
                                    'max_po_release_date' => $max_po_release_date->format('Y-m-d'),
                                    'kpi' => $kpi,
                                    'late' => $late
                                ];
                            }

                            $no_po = [];
                            $no_incoming = [];
                            if ($tgl_release_po !== '') {
                                $detailIdArr = [];

                                $this->db->select('b.id');
                                $this->db->from('rutin_non_planning_header a');
                                $this->db->join('rutin_non_planning_detail b', 'b.no_pengajuan = a.no_pengajuan');
                                $this->db->where('a.no_pr', $item1['no_pr']);
                                $get_detail_pr = $this->db->get()->result();

                                foreach ($get_detail_pr as $detail_pr) {
                                    $detailIdArr[] = $detail_pr->id;
                                }

                                if (!empty($detailIdArr)) {
                                    $detailIdArr = implode(',', $detailIdArr);

                                    $get_no_po = $this->db->query("SELECT a.no_surat, a.no_po FROM tr_purchase_order a JOIN dt_trans_po b ON b.no_po = a.no_po WHERE b.idpr IN ('" . str_replace(",", "','", $detailIdArr) . "') AND a.tipe = 'pr depart' GROUP BY a.no_surat")->result();

                                    foreach ($get_no_po as $item_po) {
                                        $no_po[] = $item_po->no_surat;

                                        $this->db->select('a.kode_trans');
                                        $this->db->from('tr_incoming_check a');
                                        $this->db->like('a.no_ipp', $item_po->no_po);
                                        $get_incoming = $this->db->get()->result();
                                        foreach ($get_incoming as $item_incoming) {
                                            $no_incoming[] = $item_incoming->kode_trans;
                                        }
                                    }

                                    $no_po = implode(', ', $no_po);
                                    $no_incoming = implode(', ', $no_incoming);
                                }
                            } else {
                                $no_po = '';
                            }

                            if (empty($no_po)) {
                                $no_po = '';
                            }
                            if (empty($no_incoming)) {
                                $no_incoming = '';
                            }

                            if (count($get_approval_data) > 0) {
                                echo '<tr>';
                                echo '<td class="text-center">' . $no . '</td>';
                                echo '<td class="text-center">' . $item1['no_pr'] . '</td>';
                                echo '<td class="text-center">' . strtoupper($item1['tipe_pr']) . '</td>';
                                echo '<td class="text-center">' . $no_po . '</td>';
                                echo '<td class="text-center">' . $no_incoming . '</td>';
                                echo '<td class="text-center">' . date('d F Y', strtotime($item1['pr_date'])) . '</td>';
                                echo '<td class="text-center">' . $max_po_release_date->format('d F Y') . '</td>';
                                echo '<td class="text-center">' . date('d F Y') . '</td>';
                                echo '<td class="text-center">' . $tgl_release_po . '</td>';
                                echo '<td class="text-center">' . $late . '</td>';
                                echo '<td class="text-center">' . $kpi . '</td>';
                                echo '</tr>';
                            }

                            $no++;
                        }
                    }
                }
            }
            ?>
        </tbody>
    </table>

    <br><br>

    <h2>Ontime PO Release</h2>
    <table class="table table-bordered w-100">
        <thead class="bg-yellow text-white">
            <tr>
                <th class="text-center">Months</th>
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    echo '<th class="text-center">' . date('F', strtotime('01-' . $i . '-' . date('Y'))) . '</th>';
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">Percentage</td>
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    $late_summ = 0;
                    $all_late_summ = 0;
                    foreach ($arr_summ as $summ) {
                        if (date('m', strtotime($summ['max_po_release_date'])) == sprintf('%02d', $i)) {
                            if ($summ['kpi'] !== '0 Hari') {
                                $late_summ += 1;
                                $all_late_summ += 1;
                            } else {
                                $all_late_summ += 1;
                            }
                        }
                    }

                    $summary = 0;
                    if ($all_late_summ > 0) {
                        $summary = ceil(($late_summ / $all_late_summ) * 100);
                        if ($late_summ <= 0 && $all_late_summ > 0) {
                            $summary =  100;
                        }
                    }
                    echo '<td class="text-center">' . number_format($summary) . '%</td>';
                }
                ?>
            </tr>
        </tbody>
    </table>
</div>