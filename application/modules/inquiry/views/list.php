<?php
    $ENABLE_ADD     = has_permission('Salesorder.Add');
    $ENABLE_MANAGE  = has_permission('Salesorder.Manage');
    $ENABLE_VIEW    = has_permission('Salesorder.View');
    $ENABLE_DELETE  = has_permission('Salesorder.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box">
    <div class="box-header">
        <?php if ($ENABLE_ADD) : ?>
            <a class="btn btn-success" href="javascript:void(0)" title="Add" onclick="add_data()"><i class="fa fa-plus">&nbsp;</i>New</a>
        <?php endif; ?>

        <span class="pull-right">
                <?php //echo anchor(site_url('customer/downloadExcel'), ' <i class="fa fa-download"></i> Excel ', 'class="btn btn-primary btn-sm"'); ?>
        </span>
    </div>
    <div class="box-body">
        <table id="example1" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th width="2%">#</th>
                <th>NO. SO</th>
                <th>Nama Customer</th>
                <th>Tanggal</th>
                <th>Nama Salesman</th>
                <th>Total</th>
                <th width="5%">Status</th>
                <th width="5%">Picking</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if(@$results){ ?>
            <?php
            $n = 1;
            foreach(@$results as $kso=>$vso){
                $no = $n++;
                //$sts = "OPEN";
                $badge = "bg-green";
                $disbtn = '';
                if($vso->stsorder == "CLOSE"){
                  //$sts = "CLOSE";
                  $disbtn = 'style="cursor: not-allowed;';
                  //$disbtn = 'disabled="disabled"';
                  $badge = "bg-red";
                }else if($vso->stsorder == "CANCEL"){
                  $badge = "bg-orange";
                }else if($vso->stsorder == "PENDING"){
                  $badge = "bg-yellow";
                }

                $cancel = '-';
                if($vso->stsorder == "OPEN"){
                  $cancel = '<span class="badge bg-orange" style="cursor:pointer;" onclick="setcancelso(\''.$vso->no_so.'\')">CANCEL SO</span>';
                }else if($vso->stsorder == "CANCEL"){
                  $cancel = '<span class="badge bg-green">YA</span>';
                }
            ?>
            <tr>
                <td class="text-center"><?php echo $no?></td>
                <td class="text-center"><?php echo $vso->no_so?></td>
                <td><?php echo $vso->nm_customer?></td>
                <td class="text-center"><?php echo date('d/m/Y',strtotime($vso->tanggal))?></td>
                <td><?php echo $vso->nm_salesman?></td>
                <td class="text-right"><?php echo formatnomor($vso->total)?></td>
                <td class="text-center"><span class="badge <?php echo $badge?>"><?php echo $vso->stsorder?></span></td>
                <!--<td class="text-center"><?php //echo $cancel?></td>-->
                <td class="text-right">
                  <?php if($vso->stsorder != "CANCEL"){?>
                  <center>
                    <a href="#dialog-popup" data-toggle="modal" onclick="PickingList('<?php echo $vso->no_so?>')">
                    <span class="glyphicon glyphicon-file"></span>
                  </center>
                  <?php } ?>
                    <!--
                    <select class="form-control input-sm width-100" id="status_so" onchange="setstatusso()">
                      <option value="0">-</option>
                      <option value="1">Konfirm</option>
                      <option value="2">Pending</option>
                      <option value="3">Cancel</option>
                    </select>
                    -->
                </td>

                 <td class="text-center">
                    <?php if($vso->stsorder != "CANCEL"){ ?>
                    <?php if($ENABLE_VIEW) { ?>
                    <a href="#dialog-popup" data-toggle="modal" onclick="PreviewPdf('<?php echo $vso->no_so?>')">
                    <span class="glyphicon glyphicon-print"></span>
                    </a>
                    <?php } ?>
                    <?php if($ENABLE_MANAGE) { ?>
                    <!--<a class="text-green" href="javascript:void(0)" title="Edit" onclick="edit_data('<?php //echo $vso->no_so?>')" <?php //echo $disbtn ?>><i class="fa fa-pencil"></i>
                    </a>-->
                    <?php } ?>
                    <?php //if($ENABLE_DELETE) { ?>
                    <!--<a class="text-red" href="javascript:void(0)" title="Delete" onclick="delete_data('<?php //echo $vso->no_so?>')" <?php //echo $disbtn ?>><i class="fa fa-trash"></i>-->
                    </a>
                    <?php //} ?>
                    <?php if($vso->stsorder != "CLOSE"){ ?>
                     <a class="text-red" href="javascript:void(0)" title="Cancel SO" onclick="setcancelso('<?php echo $vso->no_so?>')"><i class="fa fa-times"></i>
                    </a>
                    <?php } ?>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
            <th width="50">#</th>
            <th>NO. SO</th>
            <th>Nama Customer</th>
            <th>Tanggal</th>
            <th>Nama Salesman</th>
            <th>Total</th>
            <th>Status</th>
            <th width="5%">Picking</th>

            <th>Aksi</th>
        </tr>
        </tfoot>
        </table>
    </div>
</div>
<div id="form-area">
<?php //$this->load->view('salesorder/salesorder_form') ?>
</div>
<!-- Modal -->
<div class="modal modal-primary" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"></h4>
      </div>
      <div class="modal-body" id="MyModalBody">
    ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="glyphicon glyphicon-remove"></span>  Tutup</button>
        </div>
    </div>
  </div>
</div>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<!-- page script -->
<script type="text/javascript">
    $(function() {
        $("#example1").DataTable();
        $("#form-area").hide();
    });
    function add_data(){
        window.location.href = siteurl+"salesorder/create";
    }
    function edit_data(noso){
        window.location.href = siteurl+"salesorder/edit/"+noso;
    }
    function delete_data(noso){
        swal({
          title: "Anda Yakin?",
          text: "Data Akan Terhapus secara Permanen!",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Ya, delete!",
          cancelButtonText: "Tidak!",
          closeOnConfirm: false,
          closeOnCancel: true
        },
        function(isConfirm){
          if(isConfirm) {
            $.ajax({
                    url: siteurl+'salesorder/hapus_header_so',
                    data :{"NO_SO":noso},
                    dataType : "json",
                    type: 'POST',
                    success: function(result){
                        if(result.delete=='1'){
                            swal({
                              title: "Terhapus!",
                              text: "Data berhasil dihapus",
                              type: "success",
                              timer: 1500,
                              showConfirmButton: false
                            });
                            setTimeout(function(){
                                window.location.reload();
                            },1600);
                        } else {
                            swal({
                              title: "Gagal!",
                              text: "Data gagal dihapus",
                              type: "error",
                              timer: 1500,
                              showConfirmButton: false
                            });
                        };
                    },
                    error: function(){
                        swal({
                          title: "Gagal!",
                          text: "Gagal Eksekusi Ajax",
                          type: "error",
                          timer: 1500,
                          showConfirmButton: false
                        });
                    }
                });
          } else {
            //cancel();
          }
        });
    }
    function setcancelso(noso){
      swal({
          title: "Peringatan!",
          text: "Yakin Cancel Sales Order "+noso+"?",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Ya, Cancel SO",
          cancelButtonText: "Tidak!",
          closeOnConfirm: true,
          closeOnCancel: true
        },
        function(isConfirm){
          if(isConfirm){
            $.ajax({
                    url: siteurl+'salesorder/set_cancel_so',
                    data :{"NO_SO":noso},
                    dataType : "json",
                    type: 'POST',
                    success: function(result){
                        if(result.cancel=='1'){
                            swal({
                              title: "Sukses!",
                              text: "Data berhasil dicancel",
                              type: "success",
                              timer: 1500,
                              showConfirmButton: false
                            });
                            //console.log
                            setTimeout(function(){
                                window.location.reload();
                            },1600);
                        } else {
                            swal({
                              title: "Gagal!",
                              text: "Data gagal dicancel",
                              type: "error",
                              timer: 1500,
                              showConfirmButton: false
                            });
                        };
                    },
                    error: function(){
                        swal({
                          title: "Gagal!",
                          text: "Gagal Eksekusi Ajax",
                          type: "error",
                          timer: 1500,
                          showConfirmButton: false
                        });
                    }
                });
          }
      });
    }
    function PreviewPdf(noso)
    {
      $('#myModalLabel').html('<span class="fa fa-file-pdf-o"></span> Sales Order (SO)');
      param=noso;
      tujuan = 'salesorder/print_request/'+param;

        $(".modal-body").html('<iframe src="'+tujuan+'" frameborder="no" width="100%" height="400"></iframe>');
    }
    function PickingList(noso)
    {
      $('#myModalLabel').html('<span class="fa fa-file-pdf-o"></span> Picking List');
      param=noso;
      tujuan = 'salesorder/print_picking_list/'+param;

        $(".modal-body").html('<iframe src="'+tujuan+'" frameborder="no" width="100%" height="400"></iframe>');
    }
</script>
