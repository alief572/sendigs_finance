<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Request Payment (" . date('d-m-Y') . ").xls");

$bulan_indonesia = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

/**
 * Format tanggal ke "dd NamaBulan YYYY" (contoh: 22 Mei 2026)
 */
function format_tanggal_indo($date_str, $bulan_indo)
{
    if (empty($date_str) || strtotime($date_str) === false) return '';
    return date('d-M-Y', strtotime($date_str));
}

/**
 * Get tanggal approval dari tabel tagihan berdasarkan kategori
 */
function get_tanggal_approval_tagihan($item, $CI)
{
    $tgl_approve = '';
    switch ($item->kategori) {
        case 'Kasbon':
            $row = $CI->db->select('approved_on')->get_where('tr_kasbon', ['no_doc' => $item->no_dokumen])->row();
            if ($row && !empty($row->approved_on)) {
                $tgl_approve = $row->approved_on;
            }
            break;
        case 'Transport':
            $row = $CI->db->select('approved_on')->get_where('tr_transport_req', ['no_doc' => $item->no_dokumen])->row();
            if ($row && !empty($row->approved_on)) {
                $tgl_approve = $row->approved_on;
            }
            break;
        case 'Cash':
        case 'Non-PO':
            $row = $CI->db->select('created_date')->get_where('tr_pr_non_po', ['id' => $item->id])->row();
            if ($row && !empty($row->created_date)) {
                $tgl_approve = $row->created_date;
            }
            break;
        case 'Expense':
            $row = $CI->db->select('approved_on')->get_where('tr_expense', ['no_doc' => $item->no_dokumen])->row();
            if ($row && !empty($row->approved_on)) {
                $tgl_approve = $row->approved_on;
            }
            break;
        case 'Periodik':
            $row = $CI->db->select('approved_date')->get_where('tr_pengajuan_rutin', ['no_doc' => $item->no_dokumen])->row();
            if ($row && !empty($row->approved_date)) {
                $tgl_approve = $row->approved_date;
            }
            break;
        case 'Direct Payment':
            $row = $CI->db->select('approved_on')->get_where('tr_direct_payment', ['no_doc' => $item->no_dokumen])->row();
            if ($row && !empty($row->approved_on)) {
                $tgl_approve = $row->approved_on;
            }
            break;
        // Purchase Invoice: kosongkan
        default:
            $tgl_approve = '';
            break;
    }
    return $tgl_approve;
}
?>

<style>
    table {
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #000000;
        padding: 5px;
    }

    th {
        background-color: #D9E1F2;
        font-weight: bold;
        text-align: center;
    }
</style>

<table width="100%" border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>#</th>
            <th>No. Dokumen</th>
            <th>Request By</th>
            <th>Tanggal Pengajuan</th>
            <th>Keperluan</th>
            <th>Kategori</th>
            <th>Nilai Pengajuan</th>
            <th>Tanggal di Approve</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (!empty($list_all_request_payment)) {
            $no = 0;
            $CI = &get_instance();

            foreach ($list_all_request_payment as $item) {
                $no++;

                // Request By (with Kasbon special logic)
                $nmuser = $item->request_by;
                if ($item->kategori == 'Kasbon') {
                    $get_kasbon = $CI->db->get_where('tr_kasbon', array('no_doc' => $item->no_dokumen))->row();
                    if ($get_kasbon) {
                        $check_detail = $CI->db->get_where('tr_pr_detail_kasbon', ['id_kasbon' => $item->no_dokumen])->result();
                        if (count($check_detail)) {
                            if ($get_kasbon->tipe_pr == 'pr departemen') {
                                $CI->db->select('b.nm_lengkap');
                                $CI->db->from('rutin_non_planning_header a');
                                $CI->db->join('users b', 'b.id_user = a.created_by');
                                $CI->db->where('a.no_pr', $get_kasbon->id_pr);
                                $get_single_detail = $CI->db->get()->row();
                                if ($get_single_detail) $nmuser = $get_single_detail->nm_lengkap;
                            }
                            if ($get_kasbon->tipe_pr == 'pr stok') {
                                $CI->db->select('b.nm_lengkap');
                                $CI->db->from('material_planning_base_on_produksi a');
                                $CI->db->join('users b', 'b.id_user = a.created_by');
                                $CI->db->where('a.no_pr', $get_kasbon->id_pr);
                                $get_single_detail = $CI->db->get()->row();
                                if ($get_single_detail) $nmuser = $get_single_detail->nm_lengkap;
                            }
                        }
                    }
                }

                // Tanggal Pengajuan
                $tanggal_pengajuan = format_tanggal_indo($item->tanggal, $bulan_indonesia);

                // Tanggal di Approve (dari tabel tagihan masing-masing kategori)
                $raw_tgl_approve = get_tanggal_approval_tagihan($item, $CI);
                $tgl_approve = format_tanggal_indo($raw_tgl_approve, $bulan_indonesia);

                // Nilai Pengajuan
                $nilai = (!empty($item->nilai_pengajuan)) ? number_format($item->nilai_pengajuan, 0, ',', '.') : '0';

                // Keperluan
                $keperluan = (!empty($item->keperluan)) ? $item->keperluan : '';

                echo '<tr>';
                echo '<td style="text-align: center;">' . $no . '</td>';
                echo '<td style="text-align: center;">' . $item->no_dokumen . '</td>';
                echo '<td style="text-align: center;">' . $nmuser . '</td>';
                echo '<td style="text-align: center;">' . $tanggal_pengajuan . '</td>';
                echo '<td style="text-align: left;">' . $keperluan . '</td>';
                echo '<td style="text-align: center;">' . $item->kategori . '</td>';
                echo '<td style="text-align: right;">' . $nilai . '</td>';
                echo '<td style="text-align: center;">' . $tgl_approve . '</td>';
                echo '</tr>';
            }
        }
        ?>
    </tbody>
</table>