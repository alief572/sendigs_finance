<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Report Actual Plan Tagih (" . $tahun . ").xls");
?>
<h2>Report Actual Plan Tagih : <?= $tahun ?></h2>
<table width="100%" border="1">
    <thead>
        <tr>
            <th style="text-align: center;">No.</th>
            <th style="text-align: center;">Company</th>
            <th style="text-align: center;">No. SPK</th>
            <th style="text-align: center;">Customer</th>
            <th style="text-align: center;">Project</th>
            <th style="text-align: center;">Project Leader</th>
            <th style="text-align: center;">Sales</th>
            <th style="text-align: center;">Keterangan</th>
            <th style="text-align: center;">Status</th>
            <th style="text-align: center;">Bulan Tagih</th>
            <th style="text-align: center;">Tahun Tagih</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 0;
        foreach ($list_data as $item) {
            $no++;

            // $get_spk_penawaran = $this->db->get_where(DBCNL . '.kons_tr_spk_penawaran', ['id_spk_penawaran' => $item['id_spk_penawaran']])->row_array();

            $this->db->select('a.*');
            $this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
            $this->db->like('a.id_spk_penawaran', $item['id_spk_penawaran']);
            $get_spk_penawaran = $this->db->get()->row_array();

            $nm_sales = (!empty($get_spk_penawaran)) ? $get_spk_penawaran['nm_sales'] : '';

            $nm_customer = $get_spk_penawaran['nm_customer'] ?? '';
            $nm_project_leader = $get_spk_penawaran['nm_project_leader'] ?? '';

            $nm_company = $get_spk_penawaran['nm_company'];
            if (!empty($get_spk_penawaran) && !empty($item['id_penawaran'])) {
                $get_penawaran = $this->db->get_where(DBCNL . '.kons_tr_penawaran', ['id_quotation' => $item['id_penawaran']])->row_array();
                $get_company = $this->db->get_where(DBCNL . '.kons_tr_company', ['id' => $get_penawaran['company']])->row_array();

                $nm_company = (!empty($get_company)) ? $get_company['nm_company'] : '';
            }

            $status = '<div style="background-color: blue;">Waiting Actual Plan Tagih</div>';

            $check_aktual_telat = $this->db->get_where('kons_tr_actual_plan_tagih', ['id_detail_plan_tagih' => $item['id'], 'tagih_mundur' => 2])->result_array();
            if (count($check_aktual_telat) > 0) {
                $status = '<div style="background-color: red;">Mundur</div>';
            }
            $check_aktual_tagih = $this->db->get_where('kons_tr_actual_plan_tagih', ['id_detail_plan_tagih' => $item['id'], 'tagih_mundur' => 1])->result_array();
            if (count($check_aktual_tagih) > 0) {
                $status = '<div style="background-color: green;">Tagih</div>';
            }


            $this->db->select('b.nm_paket');
            $this->db->from(DBCNL . '.kons_tr_spk_penawaran a');
            $this->db->join(DBCNL . '.kons_master_konsultasi_header b', 'b.id_konsultasi_h = a.id_project', 'left');
            $this->db->like('a.id_spk_penawaran', $item['id_spk_penawaran'], 'both');
            $get_spk = $this->db->get()->row_array();

            $nm_paket = (!empty($get_spk)) ? $get_spk['nm_paket'] : '';

            $nm_project = ($item['nm_project'] == '' && $item['nm_project'] == null) ? $nm_paket : $item['nm_project'];

            $bulan_tagih = date('F', strtotime($item['tgl_plan_tagih']));
            $tahun_tagih = date('Y', strtotime($item['tgl_plan_tagih']));

            $this->db->select('a.tgl_plan_tagih, a.tanggal_actual_plan_tagih');
            $this->db->from('kons_tr_actual_plan_tagih a');
            $this->db->where('a.id_detail_plan_tagih', $item['id']);
            $this->db->order_by('a.created_date', 'desc');
            $get_actual_plan_tagih = $this->db->get()->row_array();

            if (!empty($get_actual_plan_tagih['tanggal_actual_plan_tagih']) && $get_actual_plan_tagih['tanggal_actual_plan_tagih'] !== '0000-00-00') {
                $bulan_tagih = date('F', strtotime($get_actual_plan_tagih['tanggal_actual_plan_tagih']));
                $tahun_tagih = date('Y', strtotime($get_actual_plan_tagih['tanggal_actual_plan_tagih']));
            }

            echo '<tr>';
            echo '<td style="text-align: center;">' . $no . '</td>';
            echo '<td>' . $nm_company . '</td>';
            echo '<td>' . $item['id_spk_penawaran'] . '</td>';
            echo '<td>' . $nm_customer . '</td>';
            echo '<td>' . $nm_project . '</td>';
            echo '<td>' . $nm_project_leader . '</td>';
            echo '<td>' . $nm_sales . '</td>';
            echo '<td>' . $item['desc_payment'] . '</td>';
            echo '<td>' . $status . '</td>';
            echo '<td>' . $bulan_tagih . '</td>';
            echo '<td>' . $tahun_tagih . '</td>';
            echo '</tr>';
        }
        ?>
    </tbody>
</table>