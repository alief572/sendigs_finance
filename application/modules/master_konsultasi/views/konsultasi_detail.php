
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Paket Konsultasi</label>
            <select class="form-control" name="konsultasi" id="konsultasi">
                <?php
                if($paket->num_rows() > 0)
                {
                    $nm_paket = '';
                    foreach($paket->result() as $d){
                        if($d->id_paket == @$konsultasi_header->row()->id_paket){
                            $nm_paket = $d->nm_paket;
                        }
                    }
                    echo "<option value='".$id_paket."'>".$nm_paket."</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-12">
    <div class="table-responsive">
        <table id="my-grid" class="table table-striped table-bordered TableKonsultasi" width="100%">
            <thead>
                <tr>
                    <th width="4%">#</th>
                    <th>Aktifitas</th>
                    <th width="20%">Harga</th>
                    <th width="10%">Bobot</th>
                    <th width="10%">Mandays</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($konsultasi_detail->num_rows() > 0)
                {
                    $no = 1;
                    foreach ($konsultasi_detail->result() as $dt)
                    {
                        ?>
                        <tr>
                            <td><?php echo $no; ?></td>
                            <td style='vertical-align:middle; width:40px;'>
                                <?php echo $dt->nm_aktifitas; ?>
                            </td>
                            <td>
                                <?php echo number_format($dt->harga_aktifitas); ?>
                            </td>
                            <td><?php echo $dt->bobot; ?></td>
                            <td><?php echo $dt->mandays; ?></td>
                        </tr>

                        <?php
                        $cek_point = $this->db
                            ->select('id_chk_point, id_aktifitas, nm_chk_point')
                            ->where('id_aktifitas', $dt->id_aktifitas)
                            ->get('kons_master_check_point');
                        if($cek_point->num_rows() > 0)
                        {
                        ?>
                        <tr>
                            <td colspan="7" style="padding: 15px 15px 0px 15px;">
                                <table class="table table-bordered" width="100%">
                                    <thead>
                                        <tr style="background: #f1f1f1; font-weight: 600;">
                                            <td width="5%"><center>No.</center></td>
                                            <td>Detail Check Point - (<?php echo $dt->nm_aktifitas; ?>)</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $nomor = 1;
                                        foreach($cek_point->result() as $d)
                                        {
                                        echo "
                                            <tr>
                                                <td><center>".$nomor."</center></td>
                                                <td>".$d->nm_chk_point."</td>
                                            </tr>
                                        ";
                                        $nomor++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <?php
                        }
                        $no++;
                    }
                }
                else
                {
                    echo "
                    <tr>
                        <td colspan='5'><center>Belum ada aktifitas</center></td>
                    </tr>
                    ";
                }
                ?>
            </tbody>
        </table>
    </div>
    </div>
</div>