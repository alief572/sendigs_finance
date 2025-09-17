<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box box-primary">
  <div class="box-body">
    <div class="col-sm-12">
      <form id="form-header-so" method="post">
          <div class="form-horizontal">
            <div class="form-group">
              <label for="kursyuan" class="col-sm-4 control-label">1 - Yuan (MBR)</label>
              <div class="col-sm-4">
                  <div class="input-group">
                      <span class="input-group-addon">RMB-IDR</span>
                      <input type="text" name="kursyuan" id="kursyuan" class="form-control input-sm" value="<?php echo @$yuan->kurs?>">
                  </div>
              </div>
            </div>
            <div class="form-group">
              <label for="kursusd" class="col-sm-4 control-label">1 - US Dollar (USD)</label>
              <div class="col-sm-4">
                  <div class="input-group">
                      <span class="input-group-addon">USD-IDR</span>
                      <input type="text" name="kursusd" id="kursusd" class="form-control input-sm" value="<?php echo @$usd->kurs?>">
                  </div>
              </div>
            </div>
          </div>
          <div class="text-right col-sm-8">
            <button class="btn btn-danger" type="button">Cancel</button>
            <button class="btn btn-primary" type="button" onclick="simpankurs()">Simpan</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!--div class="box box-primary">
  <div class="box-body">
    <div class="col-sm-12">
      <button class="btn btn-sm btn-primary" type="button" onclick="edit(0)"><i class="fa fa-plus"></i> Coba Klik</button>
      <hr>
      <table class="table table-bordered" id="tabel-server-side">
        <thead>
          <tr>
            <th width="1%">NO</th>
            <th>SETTING</th>
            <th width="5%">AKSI</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div-->
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<script type="text/javascript">
jQuery(function($) {
    var dataTable = $('#tabel-server-side').DataTable({
            "order": [[ 1, "asc" ]],
            "processing": false,
            "serverSide": true,
            "columnDefs": [{
              "targets": 0,
              "orderable": false,
              "searchable": false

            }],
            "oLanguage":{
              "sSearch" : "_INPUT_",
              "sSearchPlaceholder" : "Search",
              "sLengthMenu":"Show _MENU_ data",
              "sInfo":"Page _PAGE_ of _PAGES_, Total : <b>_TOTAL_</b> data"
            },
            "ajax":{
                url : siteurl+'kurs/ajaxgetcoba',
                type: "post",
            }
        });
  });

  function edit(id){
      $('#tabel-server-side').DataTable().draw();
      var next = id+1;
      var coba = "COBA-"+next;
      $.post(siteurl+'kurs/editkurs',{'ID':id,'COBA':coba},function(result){
        console.log(result);
      });
  }
  //==============END PERCOBAAN============//
  function simpankurs(){
    var usd = $('#kursusd').val();
    var yuan = $('#kursyuan').val();
    $.post(siteurl+'kurs/simpankurs',{'USD':usd,'YUAN':yuan},function(result){
        swal({
            title: "Sukses!",
            text: result,
            type: "success",
            timer: 1500,
            showConfirmButton: false
        });
        setTimeout(function(){window.location.href=window.location.href;}, 2000);
    });
  }
</script>
