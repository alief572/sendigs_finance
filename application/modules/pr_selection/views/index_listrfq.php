<?php
    $ENABLE_ADD     = has_permission('List_RFQ.Add');
    $ENABLE_MANAGE  = has_permission('List_RFQ.Manage');
    $ENABLE_VIEW    = has_permission('List_RFQ.View');
    $ENABLE_DELETE  = has_permission('List_RFQ.Delete');
?>
<style>
.modal-dialog{
	width:80%;
}
</style>
<div id='tampil'>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box">    	
	<div class="box-header">
		<div class="nav-tabs-custom">
			<div class="box active ">
				<ul class="nav nav-tabs">
					<li class=""><a href="#" data-toggle="tab" id="asset">RFQ Asset</a></li>
					<li class=""><a href="#" data-toggle="tab" id="rutin">RFQ Stock</a></li>
					<li class=""><a href="#" data-toggle="tab" id="nonrutin">RFQ Departement</a></li>
				</ul> 
			</div>			
			<div id="scroll">	
				<div class="box box-primary" id="data">
				</div>
            </div> 			
		</div>	
	</div>
	<!-- /.box-header -->
	<div class="box-body"> 	   
	</div>
	<!-- /.box-body -->
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;RFQ</h4>
      </div>
      <div class="modal-body" id="ModalView">
		...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">
        <span class="glyphicon glyphicon-remove"></span>  Close</button>
        </div>
    </div>
  </div>
</div>
</div>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
		$("#aset").hide();	
	});

	$(document).on('click', '#asset', function(){
		$("#aset").show();	
		DataAset()		
	});

	$(document).on('click', '#add_aset_po', function(){		
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Create PO</b>");
		$.ajax({
			type:'POST',
			url:siteurl+'purchase_order/po_aset',
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);				
			}
		})
	});

	function DataAset(){		
		 $("#data").hide();		 
		 $.ajax({
			type:'POST',
			url:siteurl+'pr_selection/list_rfq_aset',
			success:function(data){				
				$("#data").html(data);
				$("#data").fadeIn(500);	
                $("#add_aset").fadeIn(500);				
			}
		})		
	}

	$(document).on('click', '#rutin', function(){
		DataRutin();
	});
	function DataRutin(){
		 $("#data").hide();		 
		 $.ajax({
			type:'POST',
			url:siteurl+'pr_selection/list_rfq_rutin',
			success:function(data){
				$("#data").html(data);
				$("#data").fadeIn(500);
			}
		})
	}

	$(document).on('click', '#nonrutin', function(){
		DataNonRutin();
	});
	function DataNonRutin(){
		 $("#data").hide();		 
		 $.ajax({
			type:'POST',
			url:siteurl+'pr_selection/list_rfq_nonrutin',
			success:function(data){
				$("#data").html(data);
				$("#data").fadeIn(500);
			}
		})
	}
</script>
