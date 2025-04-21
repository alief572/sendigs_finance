<?php
    $ENABLE_ADD     = has_permission('Salesorder.Add');
    $ENABLE_MANAGE  = has_permission('Salesorder.Manage');
    $ENABLE_VIEW    = has_permission('Salesorder.View');
    $ENABLE_DELETE  = has_permission('Salesorder.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box">
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
	            <th>Status</th>
	            <th>Aksi</th>
	        </tr>
        </thead>
        <tbody>
            <?php if(@$results){ ?>
            <?php 
            $n = 1;
            foreach(@$results as $kso=>$vso){ 
                $no = $n++;
            ?>
            <tr>
                <td class="text-center"><?php echo $no?></td>
                <td class="text-center"><?php echo $vso->no_so?></td>
                <td><?php echo $vso->nm_customer?></td>
                <td class="text-center"><?php echo date('d/m/Y',strtotime($vso->tanggal))?></td>
                <td><?php echo $vso->nm_salesman?></td>
                <td class="text-right"><?php echo $vso->total?></td>
                <td class="text-right">
                    <?php echo $vso->stsorder?>
                </td>
                <td class="text-center">
                    <input type="checkbox" name="set_to_do">
                </td>
            </tr>
            <?php } ?>
            <?php } ?>
        </tbody>
        </table>
    </div>
</div>
<div class="text-right">
    <button class="btn btn-primary"> Proses DO</button> 
</div>

<!-- Modal -->
<div class="modal modal-primary" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Sales Order (SO)</h4>
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
                    url: siteurl+'salesorder/hapus_item_so',
                    data :{"NO_SO":noso,"ID":id},
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
    function PreviewPdf(noso)
    {
      param=noso;
      tujuan = 'salesorder/print_request/'+param;

        $(".modal-body").html('<iframe src="'+tujuan+'" frameborder="no" width="100%" height="400"></iframe>');
    }
</script>