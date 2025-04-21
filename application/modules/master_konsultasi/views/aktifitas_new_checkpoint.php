

    <div class="col-12">
    <?php
    $form_id = 'FormCheckPoint';
    echo form_open(site_url('master_aktifitas/aktifitas_check_point/?'.$variables), array('id' => $form_id));
    ?>
        <div class="table-responsive">
            <table id="my-grid" class="table table-striped table-bordered table-hover table-condensed TableCheckPoint" width="100%">
                <thead>
                    <tr>
                        <th width="50px">#</th>
                        <th>Detail Check Point</th>
                        <th width="6%">Hapus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($cek_point->num_rows() > 0){
                        $no = 1;
                        foreach($cek_point->result() as $d){
                            echo "
                            <tr>
                                <td>
                                    ".$no."
                                </td>
                                <td>
                                    <input type='text' class='form-control' name='check_point[]' id='check_point' value='".$d->nm_chk_point."'>
                                    <input type='hidden' name='id_chk_point[]' value='".$d->id_chk_point."'>
                                    <input type='hidden' name='unik_id[]' value='".$d->unique_id."'>
                                </td>
                                <td align='center' style='padding-top:13px'>
                                ";
                                if($no > 1){
                                    ?>
                                    <a href='#' class="btn btn-xs btn-danger" id='RemoveCheck' title='Hapus Baris'>
                                        <i class="fa fa-trash"></i>
                                    </a>
                                    <?php 
                                }
                                echo "
                                </td>
                            </tr>
                            ";
                        $no++;
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-success" id="NewLine">
            <i class="fa fa-plus"></i> Tambah Data
        </button>
        <br>
        <br>
        <div id="Notification_<?php echo $form_id; ?>"></div>
    <?php echo form_close(); ?>
    </div>


<script type="text/javascript">
$(document).ready(function(){
    var btnSave  = "<button type='button' class='btn btn-sm btn-primary' id='SaveFormPoint' data-form-id='<?php echo $form_id; ?>'><i class='fa fa-save'></i> Simpan</button>";
    var btnClose = "<button type='button' class='btn btn-sm btn-danger' data-dismiss='modal'><i class='fa fa-remove'></i> Close</button>";
    $('#modal-footer').html(btnSave + btnClose);

    var JmlData = $('.TableCheckPoint tbody tr').length;
    if(JmlData < 1){
        AppendCheckPoint();
    }
});
</script>
