<!-- Dashboard2 -->
<?php
$no = 1;
$count_late_po = 0;
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
                    $count_late_po += 1;
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
                    $count_late_po += 1;
                }
            }
        }
    }
}
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-body">
                <div class="col-md-4" style="margin-top: 2vh;">
                    <div class="panel panel-default">
                        <div class="panel-heading bg-green">Late PR Approval</div>
                        <div class="panel-body">
                            <h2><?= count($list_late_pr_approval) ?></h2>
                        </div>
                        <div class="panel-footer w-100">
                            <button type="button" class="btn btn-sm btn-primary btn_view_req" style="width: 100%;" data-val="late_pr_approval"><i class="fa fa-eye"></i> View</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" style="margin-top: 2vh;">
                    <div class="panel panel-default">
                        <div class="panel-heading bg-yellow">Late PO Release</div>
                        <div class="panel-body">
                            <h2><?= $count_late_po ?></h2>
                        </div>
                        <div class="panel-footer w-100">
                            <button type="button" class="btn btn-sm btn-primary btn_view_req" style="width: 100%;" data-val="late_po_release"><i class="fa fa-eye"></i> View</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-12" id="div_list_detail">


                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .mid {
        vertical-align: middle !important;
    }

    .chosen-select {
        min-width: 200px !important;
        max-width: 100% !important;
    }

    .bold {
        font-weight: bold !important;
        padding-right: 20px !important;
    }
</style>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script type="text/javascript">
    var url_list_approval_pr = siteurl + active_controller + 'list_approval_pr';
    var url_list_po_release = siteurl + active_controller + 'list_po_release';

    $(document).on('click', '.btn_view_req', function() {
        var val = $(this).data('val');
        if (val == 'late_pr_approval') {
            var url_detail = url_list_approval_pr;
        } else {
            var url_detail = url_list_po_release;
        }

        $("#div_list_detail").html('');
        $.ajax({
            type: "GET",
            url: url_detail,
            dataType: 'html',
            success: function(result) {
                $("#div_list_detail").html(result);
                if (val == 'late_pr_approval') {
                    $('.late_pr_approval_datatable').dataTable();
                    $('.late_detail_pr_approval_datatable').dataTable();
                } else {
                    $('.late_po_release_datatable').dataTable();
                    $('.late_detail_po_release_datatable').dataTable();
                }
            }
        });

    });
</script>