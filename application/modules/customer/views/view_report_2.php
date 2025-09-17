<?php
header("Content-type: application/vnd-ms-excel");

header("Content-Disposition: attachment; filename=REPORT_CUSTOMER_".date("d-M-Y_H:i:s").".xls");

header("Pragma: no-cache");

header("Expires: 0");
?>

  <table id="example1" class="table table-bordered table-striped">
  <thead>
    <tr>
      <th width="5">#</th>
      <th>ID CUSTOMER</th>
      <th>NAMA CUSTOMER</th>
      <th>BIDANG USAHA</th>
      <th>PRODUK JUAL</th>
      <th>KREDIBILITAS</th>
      <th>ALAMAT</th>
      <th>PROVINSI</th>
      <th>KOTA/KAB</th>
      <th>KODE POS</th>
      <th>TELPON</th>
      <th>FAX</th>
      <th>NPWP</th>
      <th>ALAMAT NPWP</th>
      <th>KTP</th>
      <th>ALAMAT KTP</th>
      <th>ID MARKETING</th>
      <th>REFERENSI</th>
      <th>WEBSITE</th>
      <th>DISKON TOKO</th>
      <th>STATUS AKTIF</th>
      <th>LIMIT PIUTANG</th>
      <th>PIC NAMA</th>
      <th>PIC DIVISI</th>
      <th>PIC KONTAK</th>
      <th>NAMA TOKO</th>
      <th>ALAMAT TOKO</th>
      <th>STATUS MILIK TOKO</th>


    </tr>
  </thead>

  <tbody>
      <?php
    if($results){
      $numb=0; foreach($results AS $record)
      {
        $prov = $this->db->get_where('provinsi', array('id_prov'=>$record->provinsi))->row();
        $kota = $this->db->get_where('kabupaten', array('id_kab'=>$record->kota))->row();
        $marketing = $this->db->get_where('karyawan', array('id_karyawan'=>$record->id_marketing))->row();

        $PIC = $this->db->get_where('customer_pic', array('id_customer'=>$record->id_customer));
        $numPIC = $PIC->num_rows();
        $resPIC = $PIC->result();

        $Toko = $this->db->get_where('customer_toko', array('id_customer'=>$record->id_customer));
        $numToko = $Toko->num_rows();
        $resToko = $Toko->result();
        $rowspan = '';
        /*if ($numToko > 1 || $numPIC > 1) {
          if ($numToko>$numPIC) {
            $rowspan = 'rowspan="'.$numToko.'"';
          }else {
            $rowspan = 'rowspan="'.$numPIC.'"';
          }
        }*/

        $numb++; ?>
        <tr>
          <td <?=$rowspan?> ><?= $numb; ?></td>
          <td <?=$rowspan?> ><?= $record->id_customer ?></td>
          <td <?=$rowspan?> ><?= $record->nm_customer ?></td>
          <td <?=$rowspan?> ><?= $record->bidang_usaha ?></td>
          <td <?=$rowspan?> ><?= $record->produk_jual ?></td>
          <td <?=$rowspan?> ><?= $record->kredibilitas ?></td>
          <td <?=$rowspan?> ><?= $record->alamat ?></td>
          <td <?=$rowspan?> ><?= $prov->nama ?></td>
          <td <?=$rowspan?> ><?= $kota->nama ?></td>
          <td <?=$rowspan?> ><?= $record->kode_pos ?></td>
          <td <?=$rowspan?> ><?= $record->telpon ?></td>
          <td <?=$rowspan?> ><?= $record->fax ?></td>
          <td <?=$rowspan?> ><?= $record->npwp ?></td>
          <td <?=$rowspan?> ><?= $record->alamat_npwp ?></td>
          <td <?=$rowspan?> ><?= $record->ktp ?></td>
          <td <?=$rowspan?> ><?= $record->alamat_ktp ?></td>
          <td <?=$rowspan?> ><?= $marketing->nama_karyawan ?></td>
          <td <?=$rowspan?> ><?= $record->referensi ?></td>
          <td <?=$rowspan?> ><?= $record->website ?></td>
          <td <?=$rowspan?> ><?= $record->diskon_toko ?></td>
          <td <?=$rowspan?> ><?= $record->sts_aktif ?></td>
          <td <?=$rowspan?> ><?= $record->limit_piutang ?></td>
          <td>
          <?php foreach ($resPIC as $key => $vp): ?>
            <?php echo $vp->nm_pic." / "?>
          <?php endforeach; ?>
          </td>
          <td>
          <?php foreach ($resPIC as $key => $vp): ?>
            <?=$vp->divisi?>
          <?php endforeach; ?>
          </td>
          <td>
          <?php foreach ($resPIC as $key => $vp): ?>
            <?=$vp->hp?>
          <?php endforeach; ?>
          </td>

          <td>
          <?php foreach ($resToko as $key => $vt): ?>
            <?php echo $vt->nm_toko." / "?>
          <?php endforeach; ?>
          </td>
          <td>
          <?php foreach ($resToko as $key => $vt): ?>
            <?=$vt->alamat_toko." / "?>
          <?php endforeach; ?>
          </td>
          <td>
          <?php foreach ($resToko as $key => $vt): ?>
            <?=$vt->status_milik." / "?>
          <?php endforeach; ?>
          </td>
        </tr>
        <?php
      }
    }
      ?>
  </tbody>

  <tfoot>
    <tr>
      <th width="5">#</th>
      <th>ID CUSTOMER</th>
      <th>NAMA CUSTOMER</th>
      <th>BIDANG USAHA</th>
      <th>PRODUK JUAL</th>
      <th>KREDIBILITAS</th>
      <th>ALAMAT</th>
      <th>PROVINSI</th>
      <th>KOTA/KAB</th>
      <th>KODE POS</th>
      <th>TELPON</th>
      <th>FAX</th>
      <th>NPWP</th>
      <th>ALAMAT NPWP</th>
      <th>KTP</th>
      <th>ALAMAT KTP</th>
      <th>ID MARKETING</th>
      <th>REFERENSI</th>
      <th>WEBSITE</th>
      <th>DISKON TOKO</th>
      <th>STATUS AKTIF</th>
      <th>LIMIT PIUTANG</th>
      <th>PIC NAMA</th>
      <th>PIC DIVISI</th>
      <th>PIC KONTAK</th>
      <th>NAMA TOKO</th>
      <th>ALAMAT TOKO</th>
      <th>STATUS MILIK TOKO</th>


    </tr>
  </tfoot>
  </table>
