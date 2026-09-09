<?php
$fcpath = defined('FCPATH') ? FCPATH : dirname(dirname(dirname(dirname(__DIR__)))) . '/';
$img_logo_ssc = '';
$path_logo_ssc = $fcpath . 'assets/images/logo_ssc.jpg';
if (file_exists($path_logo_ssc)) {
    $img_logo_ssc = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($path_logo_ssc));
} elseif (file_exists('./assets/images/logo_ssc.jpg')) {
    $img_logo_ssc = 'data:image/jpeg;base64,' . base64_encode(file_get_contents('./assets/images/logo_ssc.jpg'));
} else {
    $img_logo_ssc = function_exists('base_url') ? base_url('assets/images/logo_ssc.jpg') : '';
}

$img_logo_kemnaker = '';
$path_logo_kemnaker = $fcpath . 'assets/images/logo_kemnaker.jpg';
if (file_exists($path_logo_kemnaker)) {
    $img_logo_kemnaker = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($path_logo_kemnaker));
} elseif (file_exists('./assets/images/logo_kemnaker.jpg')) {
    $img_logo_kemnaker = 'data:image/jpeg;base64,' . base64_encode(file_get_contents('./assets/images/logo_kemnaker.jpg'));
} else {
    $img_logo_kemnaker = function_exists('base_url') ? base_url('assets/images/logo_kemnaker.jpg') : '';
}
?>
<table style="width: 100%">
    <tr>
        <th align="left" width="25%">
            <img src="<?= $img_logo_ssc ?>" alt="" width="150px" height="60px">
        </th>
        <td align="center" width="50%">
            <span style="font-size: 16px; font-weight: bold;">SENTRAL SISTEM CONSULTING</span> <br>
            <span style="font-size: 11px">Jalan Letnan Jendral M.T. Haryono KAV.10 MTH Square Lt.3A No.2, <br>Cawang, Kota Jakarta Timur, Daerah Khusus Ibukota Jakarta 13330</span> <br>
            <span style="font-size: 11px">Telp (021 2906 7201-3) Fax (021 2906 7204)</span> <br>
            <span style="font-size: 11px">info@sentralsistem.com</span>
        </td>
        <th align="right" width="25%">
            <img src="<?= $img_logo_kemnaker ?>" alt="" width="150px" height="60px">
        </th>
    </tr>
</table>
<hr style="border: 1px solid black;">
<table style="width: 100%">
    <tr>
        <th align="center">
            <span style="font-size: 16px; font-weight: bold;">SURAT PERINTAH KERJA (SPK)</span>
        </th>
    </tr>
    <tr>
        <td align="center">
            <span style="font-size: 13px; ">
                Nomor: <?= !empty($list_spk_penawaran->id_spk_penawaran) ? $list_spk_penawaran->id_spk_penawaran : '-' ?>
            </span>
        </td>
    </tr>
</table>
<br>
<h3 style="margin-bottom: 2px;">Data Client</h3>
<hr style="margin-top: 2px; margin-bottom: 5px;">
<table style="width: 100%" border="0" cellpadding="2">
    <tr>
        <th align="left" valign="top" width="15%">Customer</th>
        <th align="center" valign="top" width="2%">:</th>
        <td width="33%" valign="top"><?= !empty($list_spk_penawaran->nm_customer) ? $list_spk_penawaran->nm_customer : '-' ?></td>
        <th align="left" valign="top" width="15%">No. SPK</th>
        <th align="center" width="2%" valign="top">:</th>
        <td width="33%" valign="top"><?= !empty($list_spk_penawaran->id_spk_penawaran) ? $list_spk_penawaran->id_spk_penawaran : '-' ?></td>
    </tr>
    <tr>
        <th align="left" valign="top">Alamat</th>
        <th align="center" valign="top">:</th>
        <td valign="top"><?= !empty($list_spk_penawaran->address) ? $list_spk_penawaran->address : '-' ?></td>
        <th align="left" valign="top">No. NPWP</th>
        <th align="center" valign="top">:</th>
        <td valign="top"><?= !empty($list_spk_penawaran->npwp_cust) ? $list_spk_penawaran->npwp_cust : '-' ?></td>
    </tr>
    <tr>
        <th align="left" valign="top">PIC</th>
        <th align="center" valign="top">:</th>
        <td valign="top"><?= !empty($list_customer->nm_pic) ? $list_customer->nm_pic : '-' ?></td>
        <th align="left" valign="top">Jabatan</th>
        <th align="center" valign="top">:</th>
        <td valign="top"><?= !empty($list_customer->jabatan_pic) ? $list_customer->jabatan_pic : '-' ?></td>
    </tr>
    <tr>
        <th align="left" valign="top">Kontak PIC</th>
        <th align="center" valign="top">:</th>
        <td valign="top"><?= !empty($list_customer->no_hp_pic) ? $list_customer->no_hp_pic : '-' ?></td>
        <th colspan="3"></th>
    </tr>
</table>

<br>
<h3 style="margin-bottom: 2px;">Marketing</h3>
<hr style="margin-top: 2px; margin-bottom: 5px;">
<table style="width: 100%;" border="0" cellpadding="2">
    <tr>
        <th align="left" valign="top" width="15%">Sales</th>
        <th align="center" valign="top" width="2%">:</th>
        <td width="33%" valign="top"><?= !empty($list_marketing->nm_karyawan) ? ucfirst($list_marketing->nm_karyawan) : '-' ?></td>
        <th align="left" valign="top" width="25%">Informasi Awal Eksternal (Badan Sertifikasi)</th>
        <th align="center" width="2%" valign="top">:</th>
        <td width="23%" valign="top"><?= (!empty($list_spk_penawaran->detail_info_awal_eks) && isset($list_spk_penawaran->tipe_info_awal_eks) && $list_spk_penawaran->tipe_info_awal_eks == 'bs') ? $list_spk_penawaran->detail_info_awal_eks . ' (' . $list_spk_penawaran->cp_info_awal_eks . ')' : '-' ?></td>
    </tr>
    <tr>
        <th align="left" valign="top">Informasi Awal Internal</th>
        <th align="center" valign="top">:</th>
        <td valign="top"><?= !empty($detail_informasi_awal) ? ucfirst($detail_informasi_awal) : '-' ?></td>
        <th align="left" valign="top">Informasi Awal Eksternal (Lain - lain)</th>
        <th align="center" valign="top">:</th>
        <td valign="top"><?= (!empty($list_spk_penawaran->detail_info_awal_eks) && isset($list_spk_penawaran->tipe_info_awal_eks) && $list_spk_penawaran->tipe_info_awal_eks == 'lain') ? $list_spk_penawaran->detail_info_awal_eks . ' (' . $list_spk_penawaran->cp_info_awal_eks . ')' : '-' ?></td>
    </tr>
</table>

<br>
<h3 style="margin-bottom: 2px;">Project</h3>
<hr style="margin-top: 2px; margin-bottom: 5px;">
<table style="width: 100%;" border="0" cellpadding="2">
    <tr>
        <th align="left" valign="top" width="15%" rowspan="3">Project</th>
        <th align="center" valign="top" width="2%" rowspan="3">:</th>
        <td width="33%" valign="top" rowspan="3"><?= !empty($nm_paket) ? $nm_paket : '-' ?></td>
        <th align="left" valign="top" width="15%">Project Leader</th>
        <th align="center" width="2%" valign="top">:</th>
        <td width="33%" valign="top"><?= !empty($list_spk_penawaran->nm_project_leader) ? ucfirst($list_spk_penawaran->nm_project_leader) : '-' ?></td>
    </tr>
    <tr>
        <th align="left" valign="top">Konsultan 1</th>
        <th align="center" valign="top">:</th>
        <td valign="top"><?= !empty($list_spk_penawaran->nm_konsultan_1) ? ucfirst($list_spk_penawaran->nm_konsultan_1) : '-' ?></td>
    </tr>
    <tr>
        <th align="left" valign="top">Konsultan 2</th>
        <th align="center" valign="top">:</th>
        <td valign="top"><?= !empty($list_spk_penawaran->nm_konsultan_2) ? ucfirst($list_spk_penawaran->nm_konsultan_2) : '-' ?></td>
    </tr>
</table>

<br>
<h3 style="margin-bottom: 2px;">Detail Subcont</h3>
<hr style="margin-top: 2px; margin-bottom: 5px;">
<table width="100%" border="1" cellpadding="3" style="border-collapse: collapse;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th align="center" width="5%">No.</th>
            <th align="center" width="30%">Activity Name</th>
            <th align="center" width="15%">Mandays Subcont</th>
            <th align="center" width="15%">Mandays Rate Subcont</th>
            <th align="center" width="15%">Price</th>
            <th align="center" width="20%">Description</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no_subcont = 1;
        if (!empty($list_spk_penawaran_subcont)) {
            foreach ($list_spk_penawaran_subcont as $item) {
                echo '<tr>';
                echo '<td align="center">' . $no_subcont . '</td>';
                echo '<td>' . $item->nm_aktifitas . '</td>';
                echo '<td align="center">' . number_format($item->mandays_subcont) . '</td>';
                echo '<td align="right">' . number_format($item->price_subcont, 2) . '</td>';
                echo '<td align="right">' . number_format($item->total_subcont, 2) . '</td>';
                echo '<td>' . $item->keterangan . '</td>';
                echo '</tr>';

                $no_subcont++;
            }
        } else {
            echo '<tr><td colspan="6" align="center">- Tidak ada data subcont -</td></tr>';
        }
        ?>
    </tbody>
</table>

<br>
<h3 style="margin-bottom: 2px;">Detail Akomodasi</h3>
<hr style="margin-top: 2px; margin-bottom: 5px;">
<table width="100%" border="1" cellpadding="3" style="border-collapse: collapse;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th align="center" width="5%">No.</th>
            <th align="center" width="30%">Item</th>
            <th align="center" width="10%">Qty</th>
            <th align="center" width="15%">Price/Unit</th>
            <th align="center" width="15%">Total</th>
            <th align="center" width="25%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $biaya_akomodasi = 0;
        $no_akomodasi = 1;
        if (!empty($list_akomodasi)) {
            foreach ($list_akomodasi as $item_akomodasi) {
                echo '<tr>';
                echo '<td align="center">' . $no_akomodasi . '</td>';
                echo '<td align="left">' . $item_akomodasi->nm_biaya . '</td>';
                echo '<td align="center">' . number_format($item_akomodasi->qty) . '</td>';
                echo '<td align="right">' . number_format($item_akomodasi->price_unit, 2) . '</td>';
                echo '<td align="right">' . number_format($item_akomodasi->total, 2) . '</td>';
                echo '<td align="left">' . $item_akomodasi->keterangan . '</td>';
                echo '</tr>';

                $biaya_akomodasi += $item_akomodasi->total;
                $no_akomodasi++;
            }
        } else {
            echo '<tr><td colspan="6" align="center">- Tidak ada data akomodasi -</td></tr>';
        }
        ?>
    </tbody>
</table>

<br>
<h3 style="margin-bottom: 2px;">Detail Others</h3>
<hr style="margin-top: 2px; margin-bottom: 5px;">
<table width="100%" border="1" cellpadding="3" style="border-collapse: collapse;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th align="center" width="5%">No.</th>
            <th align="center" width="30%">Item</th>
            <th align="center" width="10%">Qty</th>
            <th align="center" width="15%">Price/Unit Budget</th>
            <th align="center" width="15%">Total Budget</th>
            <th align="center" width="25%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $biaya_others = 0;
        $no_others = 1;
        if (!empty($list_others)) {
            foreach ($list_others as $item_others) {
                echo '<tr>';
                echo '<td align="center">' . $no_others . '</td>';
                echo '<td align="left">' . $item_others->nm_biaya . '</td>';
                echo '<td align="center">' . number_format($item_others->qty) . '</td>';
                echo '<td align="right">' . number_format($item_others->price_unit_budget, 2) . '</td>';
                echo '<td align="right">' . number_format($item_others->total_budget, 2) . '</td>';
                echo '<td align="left">' . $item_others->keterangan . '</td>';
                echo '</tr>';

                $biaya_others += $item_others->total_budget;
                $no_others++;
            }
        } else {
            echo '<tr><td colspan="6" align="center">- Tidak ada data others -</td></tr>';
        }
        ?>
    </tbody>
</table>

<br>
<h3 style="margin-bottom: 2px;">Detail Lab</h3>
<hr style="margin-top: 2px; margin-bottom: 5px;">
<table width="100%" border="1" cellpadding="3" style="border-collapse: collapse;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th align="center" width="5%">No.</th>
            <th align="center" width="30%">Item</th>
            <th align="center" width="10%">Qty</th>
            <th align="center" width="15%">Price/Unit Budget</th>
            <th align="center" width="15%">Total Budget</th>
            <th align="center" width="25%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $biaya_lab = 0;
        $no_lab = 1;
        if (!empty($list_lab)) {
            foreach ($list_lab as $item_lab) {
                echo '<tr>';
                echo '<td align="center">' . $no_lab . '</td>';
                echo '<td align="left">' . $item_lab->nm_biaya . '</td>';
                echo '<td align="center">' . number_format($item_lab->qty) . '</td>';
                echo '<td align="right">' . number_format($item_lab->price_unit_budget, 2) . '</td>';
                echo '<td align="right">' . number_format($item_lab->total_budget, 2) . '</td>';
                echo '<td align="left">' . $item_lab->keterangan . '</td>';
                echo '</tr>';

                $biaya_lab += $item_lab->total_budget;
                $no_lab++;
            }
        } else {
            echo '<tr><td colspan="6" align="center">- Tidak ada data lab -</td></tr>';
        }
        ?>
    </tbody>
</table>

<br>
<h3 style="margin-bottom: 2px;">Summary</h3>
<hr style="margin-top: 2px; margin-bottom: 5px;">
<table width="100%" border="0" cellpadding="2">
    <tr>
        <th align="left" valign="top" width="15%">Waktu</th>
        <th align="center" valign="top" width="2%">:</th>
        <td width="33%" valign="top"><?= (!empty($list_spk_penawaran->waktu_from) && !empty($list_spk_penawaran->waktu_to)) ? date('d-M-Y', strtotime($list_spk_penawaran->waktu_from)) . ' - ' . date('d-M-Y', strtotime($list_spk_penawaran->waktu_to)) : '-' ?></td>
        <th align="left" valign="top" width="20%">Nilai Kontrak</th>
        <th align="center" width="2%" valign="top">:</th>
        <td width="28%" valign="top">Rp. <?= number_format(!empty($list_spk_penawaran->nilai_kontrak) ? $list_spk_penawaran->nilai_kontrak : 0, 2) ?></td>
    </tr>
    <tr>
        <th align="left" valign="top">Divisi</th>
        <th align="center" valign="top">:</th>
        <td valign="top"><?= !empty($list_spk_penawaran->nm_divisi) ? $list_spk_penawaran->nm_divisi : '-' ?></td>
        <th align="left" valign="top">Biaya Akomodasi</th>
        <th align="center" valign="top">:</th>
        <td valign="top">Rp. <?= number_format($biaya_akomodasi, 2) ?></td>
    </tr>
    <tr>
        <th align="left" valign="top">Total Mandays</th>
        <th align="center" valign="top">:</th>
        <td valign="top"><?= number_format(!empty($list_spk_penawaran->total_mandays) ? $list_spk_penawaran->total_mandays : 0) ?></td>
        <th align="left" valign="top">Biaya Subcont</th>
        <th align="center" valign="top">:</th>
        <td valign="top">Rp. <?= number_format(!empty($list_spk_penawaran->biaya_subcont) ? $list_spk_penawaran->biaya_subcont : 0, 2) ?></td>
    </tr>
    <tr>
        <th align="left" valign="top">Mandays Subcont</th>
        <th align="center" valign="top">:</th>
        <td valign="top"><?= number_format(!empty($ttl_mandays_subcont) ? $ttl_mandays_subcont : 0) ?></td>
        <th align="left" valign="top">Biaya Others</th>
        <th align="center" valign="top">:</th>
        <td valign="top">Rp. <?= number_format($biaya_others, 2) ?></td>
    </tr>
    <tr>
        <th align="left" valign="top">Mandays Internal</th>
        <th align="center" valign="top">:</th>
        <td valign="top"><?= number_format(!empty($list_spk_penawaran->mandays_internal) ? $list_spk_penawaran->mandays_internal : 0) ?></td>
        <th align="left" valign="top">Biaya Lab</th>
        <th align="center" valign="top">:</th>
        <td valign="top">Rp. <?= number_format($biaya_lab, 2) ?></td>
    </tr>
    <tr>
        <th align="left" valign="top">Mandays Rate</th>
        <th align="center" valign="top">:</th>
        <td valign="top"><?= number_format(!empty($list_spk_penawaran->mandays_rate) ? $list_spk_penawaran->mandays_rate : 0, 2) ?></td>
        <th align="left" valign="top">Biaya Tandem</th>
        <th align="center" valign="top">:</th>
        <td valign="top">Rp. <?= number_format(!empty($ttl_tandem) ? $ttl_tandem : 0, 2) ?></td>
    </tr>
    <tr>
        <td colspan="3"></td>
        <th align="left" valign="top">Biaya Subcont Tenaga Ahli</th>
        <th align="center" valign="top">:</th>
        <td valign="top">Rp. <?= number_format(!empty($list_spk_penawaran->biaya_subcont_tenaga_ahli) ? $list_spk_penawaran->biaya_subcont_tenaga_ahli : 0, 2) ?></td>
    </tr>
    <tr>
        <td colspan="3"></td>
        <th align="left" valign="top">Biaya Subcont Perusahaan</th>
        <th align="center" valign="top">:</th>
        <td valign="top">Rp. <?= number_format(!empty($list_spk_penawaran->biaya_subcont_perusahaan) ? $list_spk_penawaran->biaya_subcont_perusahaan : 0, 2) ?></td>
    </tr>
    <tr>
        <td colspan="3"></td>
        <th align="left" valign="top">Nilai Kontrak Bersih</th>
        <th align="center" valign="top">:</th>
        <td valign="top"><strong>Rp. <?= number_format(!empty($list_spk_penawaran->nilai_kontrak_bersih) ? $list_spk_penawaran->nilai_kontrak_bersih : 0, 2) ?></strong></td>
    </tr>
</table>

<br>
<h3 style="margin-bottom: 2px;">Term of Payment</h3>
<hr style="margin-top: 2px; margin-bottom: 5px;">
<table width="100%" border="1" cellpadding="3" style="border-collapse: collapse;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th align="center" width="5%">No.</th>
            <th align="center" width="25%">Term of Payment</th>
            <th align="center" width="15%">Persentase (%)</th>
            <th align="center" width="20%">Nominal (Rp.)</th>
            <th align="center" width="35%">Description</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no_top = 1;
        if (!empty($list_spk_penawaran_payment)) {
            foreach ($list_spk_penawaran_payment as $item) {
                echo '<tr>';
                echo '<td align="center">' . $no_top . '</td>';
                echo '<td align="left">' . $item->term_payment . '</td>';
                echo '<td align="center">' . number_format($item->persen_payment, 2) . '</td>';
                echo '<td align="right">' . number_format($item->nominal_payment, 2) . '</td>';
                echo '<td align="left">' . $item->desc_payment . '</td>';
                echo '</tr>';

                $no_top++;
            }
        } else {
            echo '<tr><td colspan="5" align="center">- Tidak ada data payment -</td></tr>';
        }
        ?>
    </tbody>
</table>

<br>
<h3 style="margin-bottom: 2px;">Komisi</h3>
<hr style="margin-top: 2px; margin-bottom: 5px;">
<table width="100%" border="1" cellpadding="3" style="border-collapse: collapse;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th align="center" width="25%">Komisi</th>
            <th align="center" width="30%">Nama</th>
            <th align="center" width="20%">Persentase Komisi (%)</th>
            <th align="center" width="25%">Nominal (Rp.)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td align="left">Pemberi Informasi 1</td>
            <td align="left">
                <?= !empty($list_spk_penawaran->nm_pemberi_informasi_1_komisi) ? $list_spk_penawaran->nm_pemberi_informasi_1_komisi : '-' ?>
            </td>
            <td align="center">
                <?= number_format(!empty($list_spk_penawaran->persen_pemberi_informasi_1_komisi) ? $list_spk_penawaran->persen_pemberi_informasi_1_komisi : 0, 2) ?>
            </td>
            <td align="right">
                <?= number_format(!empty($list_spk_penawaran->nominal_pemberi_informasi_1_komisi) ? $list_spk_penawaran->nominal_pemberi_informasi_1_komisi : 0, 2) ?>
            </td>
        </tr>
        <tr>
            <td align="left">Pemberi Informasi 2</td>
            <td align="left">
                <?= !empty($list_spk_penawaran->nm_pemberi_informasi_2_komisi) ? $list_spk_penawaran->nm_pemberi_informasi_2_komisi : '-' ?>
            </td>
            <td align="center">
                <?= number_format(!empty($list_spk_penawaran->persen_pemberi_informasi_2_komisi) ? $list_spk_penawaran->persen_pemberi_informasi_2_komisi : 0, 2) ?>
            </td>
            <td align="right">
                <?= number_format(!empty($list_spk_penawaran->nominal_pemberi_informasi_2_komisi) ? $list_spk_penawaran->nominal_pemberi_informasi_2_komisi : 0, 2) ?>
            </td>
        </tr>
        <tr>
            <td align="left">Sales 1</td>
            <td align="left">
                <?= !empty($list_spk_penawaran->nm_sales_1_komisi) ? $list_spk_penawaran->nm_sales_1_komisi : '-' ?>
            </td>
            <td align="center">
                <?= number_format(!empty($list_spk_penawaran->persen_sales_1_komisi) ? $list_spk_penawaran->persen_sales_1_komisi : 0, 2) ?>
            </td>
            <td align="right">
                <?= number_format(!empty($list_spk_penawaran->nominal_sales_1_komisi) ? $list_spk_penawaran->nominal_sales_1_komisi : 0, 2) ?>
            </td>
        </tr>
        <tr>
            <td align="left">Sales 2</td>
            <td align="left">
                <?= !empty($list_spk_penawaran->nm_sales_2_komisi) ? $list_spk_penawaran->nm_sales_2_komisi : '-' ?>
            </td>
            <td align="center">
                <?= number_format(!empty($list_spk_penawaran->persen_sales_2_komisi) ? $list_spk_penawaran->persen_sales_2_komisi : 0, 2) ?>
            </td>
            <td align="right">
                <?= number_format(!empty($list_spk_penawaran->nominal_sales_2_komisi) ? $list_spk_penawaran->nominal_sales_2_komisi : 0, 2) ?>
            </td>
        </tr>
    </tbody>
</table>

<br>
<h3 style="margin-bottom: 2px;">Isu Khusus dan Komitmen</h3>
<hr style="margin-top: 2px; margin-bottom: 5px;">
<table width="100%" border="0">
    <tr>
        <th align="left" style="padding-bottom: 5px;">Isu Khusus / Permintaan khusus dari customer / Tujuan Program / 3 objective utama (khusus konsultasi):</th>
    </tr>
    <tr>
        <td>
            <div style="width: 100%; min-height: 80px; border: 1px solid #999; padding: 8px; font-size: 11px;">
                <?= !empty($list_spk_penawaran->isu_khusus) ? nl2br(htmlspecialchars($list_spk_penawaran->isu_khusus)) : '-' ?>
            </div>
        </td>
    </tr>
</table>

