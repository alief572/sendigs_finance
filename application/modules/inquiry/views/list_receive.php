<div class="nav-tabs-salesorder">
    <div class="tab-content">
        <div class="tab-pane active" id="salesorder">
            <div class="box box-primary">
                <form id="form-header-mutasi" method="post">
                  <div class="form-horizontal">
                    <div class="box-body">
                          <hr>
                          <div class="col-sm-6">
                              <div class="form-group">
                                <label for="cabang_asal" class="col-sm-4 control-label">No PO </font></label>
                                <div class="col-sm-8" style="padding-top: 8px;">
                  								<?php
                  								echo"<input type='text' name='no_mutasi' id='no_mutasi' class='form-control input-sm' value='$header->no_mutasi' readonly>";
                  								?>

                                </div>
                              </div>
                              <div class="form-group ">
                                <label for="cabang_tujuan" class="col-sm-4 control-label">Tgl PO<font size="4" color="red"><B>*</B></font></label>
                                <div class="col-sm-8" style="padding-top: 8px;">
                  								<?php
                  								echo"<input type='text' name='tgl_po' id='tgl_po' class='form-control input-sm datepicker' value='".$header->tgl_mutasi."' readonly='readonly'>";
                  								?>

                                </div>
                              </div>
      						            <div class="form-group">
                                <label for="cabang_asal" class="col-sm-4 control-label">Pemesan </font></label>
                                <div class="col-sm-8" style="padding-top: 8px;">
                  								<?php
                  								$kode = $header->kdcab_asal;
                  								$nama = $header->cabang_asal;
                  								echo"<input type='text' name='cabang_pemesan' id='cabang_pemesan'  class='form-control input-sm'  value='$nama' readonly > ";
                  								echo"<input type='hidden' name='cabang_asal' id='cabang_asal' value='$header->kdcab_tujuan'>";
                  								echo"<input type='hidden' name='cabang_tujuan' id='cabang_tujuan' value='$header->kdcab_asal'>";

                  								?>

                                </div>
                              </div>
                          </div>

                		  <div class="col-sm-6">
                  			  <div class="form-group ">
                              <label for="cabang_tujuan" class="col-sm-4 control-label">Pengirim<font size="4" color="red"><B>*</B></font></label>
                              <div class="col-sm-8" style="padding-top: 8px;">
                								<?php
                								$kode2 = $header->kdcab_tujuan;
                								$nama2 = $header->cabang_tujuan;
                								echo"<input type='text' name='cabang_pengirim' id='cabang_pengirim'  class='form-control input-sm'  value='$nama2' readonly>";
                								?>

                              </div>
                              </div>
							   <div class="form-group ">
							   <label for="tgl_receive" class="col-sm-4 control-label">Tgl Receive<font size="4" color="red"><B>*</B></font></label>
                                <div class="col-sm-8" style="padding-top: 8px;">
                  								<?php
                  								echo"<input type='text' name='tgl_receive' id='tgl_receive' class='form-control input-sm datepicker' value='' >";
                  								?>

                                </div>
								 </div>
								 <div class="form-group ">
								 <label for="kendaraan" class="col-sm-4 control-label">No Kendaraan<font size="4" color="red"><B>*</B></font></label>
                                <div class="col-sm-8" style="padding-top: 8px;">
                  								<?php
                  								echo"<input type='text' name='kendaraan' id='kendaraan' class='form-control input-sm datepicker' value='' placeholder='No Kendaraan'>";
                  								?>

                                </div>
								 </div>
                		 </div>
                    </div>
                  </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="box box-default ">
	<div class="box-body">
        <form id="form-detail-mutasi" method="post">
		<table class="table table-bordered" width="100%">
			<tr class="bg-blue">
				<th colspan="8"><center><b>DETAIL PO PRODUK</b></center></th>
			</tr>
			<tr class="bg-blue">
				<th width="2%"><center>NO</center></th>
				<th width="15%">KODE PRODUK</th>
				<th>PRODUK SET</th>
				<th width="15%"><center>QTY PO</center></th>
				<th width="15%"><center>QTY ACC</center></th>
        <th width="15%"><center>QTY RECEIVE</center></th>
			</tr>
			<?php
				$n=1;
				$totalx = 0;
				foreach(@$detail as $krm=>$vrm){
					$no=$n++;
          $ambil_so = $this->db->where(array('keterangan'=>$vrm->no_mutasi,'trans_so_header_internal.no_so'=>$vrm->no_so,'id_barang'=>$vrm->id_barang))->join('trans_so_detail_internal','trans_so_detail_internal.no_so = trans_so_header_internal.no_so', 'left')->get('trans_so_header_internal')->row();
			?>
			<tr>
				<td><center><?php echo $no?></center></td>
				<td>
					<center>
					<?php echo $vrm->id_barang?>
					<input type="hidden" value="<?php echo $vrm->id_barang?>" name="data[<?php echo $no ?>][id_barang_rec_mutasi]" id="id_barang_rec_mutasi_<?php echo $no?>" style="text-align: center;" class="form-control input-sm">
					<input type="hidden" value="<?php echo $vrm->nm_barang?>" name="data[<?php echo $no ?>][nm_barang]" id="nm_barang" style="text-align: center;" class="form-control input-sm">
					<input type="hidden" value="<?php echo $vrm->kategori?>"  name="data[<?php echo $no ?>][kategori]"  id="kategori" style="text-align: center;" class="form-control input-sm">
					<input type="hidden" value="<?php echo $vrm->jenis?>"     name="data[<?php echo $no ?>][jenis]"     id="jenis" style="text-align: center;" class="form-control input-sm">
					<input type="hidden" value="<?php echo $vrm->satuan?>"    name="data[<?php echo $no ?>][satuan]"    id="satuan" style="text-align: center;" class="form-control input-sm">

					</center>
				</td>
				<td><?php echo $vrm->nm_barang?></td>

				<td>
					<center>
					<input type="text"  name="data[<?php echo $no ?>][qty_mutasi_]" id="qty_mutasi_<?php echo $no?>" style="text-align: center;" class="form-control input-sm" value="<?php echo $vrm->qty_mutasi?>" readonly>
					</center>
				</td>
				<td>
					<input type="text"  name="data[<?php echo $no ?>][qty_kirim]" id="qty_kirim_<?php echo $no?>" style="text-align: center;" class="form-control input-sm unit" readonly value="<?=$ambil_so->qty_booked?>">

				</td>
				<td>
				<input type="text"  name="data[<?php echo $no ?>][qty_receive]" id="qty_receive_<?php echo $no?>" style="text-align: right;" class="form-control input-sm amount" onkeyup="cekqtyterima('<?php echo $no?>')" value="">

				</td>
			</tr>
			<?php
			} ?>
		<table class="table table-bordered" width="100%">

		</form>
    </div>
</div>
<div class="text-right">
  <div class="box active">
    <div class="box-body">
        <button class="btn btn-danger" onclick="kembali_so()">
           <i class="fa fa-refresh"></i><b> Kembali</b>
        </button>
        <button class="btn btn-primary" type="button" onclick="savemutasi()">
            <i class="fa fa-save"></i><b> Simpan Data Receive</b>
        </button>
    </div>
  </div>
</div>



<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';
	var cabang_user			= '<?php echo $cabs_user;?>';
	var arr_cabang			= <?php echo json_encode($Arr_Cabang);?>;

	$(document).ready(function(){
		
		 $('#tgl_receive').datepicker({
              format: "yyyy-mm-dd",
              autoclose: true
              });
		
		$('#tipekirim').change(get_driver_kbm);
		$('#cabang_asal').change(function(){
			var cabang_asal = $('#cabang_asal').val();
			$('#MyModalBodyStok, #list_item_mutasi').empty();
			if(cabang_asal !='' && cabang_asal!=null){
				var cabang_tujuan	= $('#cabang_tujuan').val();
				if(cabang_asal==cabang_tujuan){
					$('#cabang_tujuan').val('');
				}

			}
		});
		$('#cabang_tujuan').change(function(){
			var cabang_tujuan = $('#cabang_tujuan').val();
			if(cabang_tujuan !='' && cabang_tujuan !=null){
				var cabang_asal	= $('#cabang_asal').val();
				if(cabang_asal==cabang_tujuan){
					swal({
						title: "Peringatan!",
						text: "Cabang Tujuan tidak boleh sama dengan cabang Asal",
						type: "warning"
					});
					$('#cabang_tujuan').val('');
				}

			}
		});
		 $("#tambah").click(function(){
			if(cabang_user !='100'){
				$('#dialog-data-stok').modal('show');
			}else{
				var cabang_asal	= $('#cabang_asal').val();
				if(cabang_asal=='' || cabang_asal==null){
					swal({
						title: "Peringatan!",
						text: "Cabang Asal belum dipilih. Mohon pilih cabang asal terlebih dahulu...",
						type: "warning"
					});
				}else{
					var kode_pecah	= cabang_asal.split('|');
					var baseurl=base_url + active_controller +'/get_stock_item';
					$.ajax({
						'url'		: baseurl,
						'type'		: 'post',
						'data'		: {'cabang':kode_pecah[0]},
						'success'	: function(data){
							$('#MyModalBodyStok').html(data);
							$('#dialog-data-stok').modal('show');
							$("#list_item_stok").DataTable({lengthMenu:[5,10,15,20]}).draw();
						},
						'error'		: function(data){
							alert('An error occured, please try again.');
						}
					});
				}
			}

		});
		$("#list_item_stok").DataTable({lengthMenu:[5,10,15,20]}).draw();

		$("#idcustomer,#idsalesman,#pic,.select2").select2({
        placeholder: "Pilih",
        allowClear: true
      });


	// $(function () {
    // $('.unit,.qty').on('change', function () {
    // var unit = $(this).hasClass('unit') ? $(this).val() : $(this).siblings('.unit').val();
    // var qty = $(this).hasClass('qty') ? $(this).val() : $(this).siblings('.qty').val();
    // unit = unit || 0;
    // qty = qty || 0;
    // var val = unit >= 1 && qty >= 1 ? parseFloat(unit * qty) : 0;
    // $(this).siblings('.amount').val(val);
    // var total = 0;
    // var update = false;
    // $('.amount').each(function () {
        // val = parseFloat($(this).val()) | 0;
        // total = val ? (parseFloat(total + val)) : total;
    // });
    // $('.result').val(total);
    // });
    // });



	$(".unit").on('change', function () {
		var self = $(this);
		var qtyVal = self.prev().val();
		self.next().val(qtyVal * self.val());
	  fnAlltotal();
	});

	function fnAlltotal(){
	  var total=0
		$(".amount").each(function(){
			 total += parseFloat($(this).val()||0);
		});
		$(".result").val(total);

	}

});





    function startmutasi(id,nm,avl,real){
       //  Cek Ada Data Gagal
	   var Cek_OK		= 1;
	   var Urut			= 1;
	   var total_row	= $('#list_item_mutasi').find('tr').length;
	   if(total_row > 0){
		  var kode_tr_akhir= $('#list_item_mutasi tr:last').attr('id');
		  var row_akhir		= kode_tr_akhir.split('_');
		  var Urut			= parseInt(row_akhir[1]) + 1;
		  $('#list_item_mutasi').find('tr').each(function(){
			  var kode_row	= $(this).attr('id');
			  var id_row	= kode_row.split('_');
			  var kode_produknya	= $('#kode_produk_'+id_row[1]).val();
			  if(id==kode_produknya){
				  Cek_OK	= 0;
			  }
		  });
	   }
	   if(Cek_OK==1){
			var idnya = "'"+id+"'";
			html='<tr id="tr_'+Urut+'">'
				+ '<td style="padding:3px;">'
				+ '<input type="text" class="form-control input-sm kode-produk" name="kode_produk[]" id="kode_produk_'+Urut+'" readonly value="'+id+'">'
				+ '</td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="nama_produk[]" id="nama_produk_'+Urut+'" readonly value="'+nm+'"></td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="stok_avl[]" id="stok_avl_'+Urut+'" style="text-align:center;" readonly value="'+avl+'"></td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="stok_real[]" id="stok_real'+Urut+'" style="text-align:center;" value="'+real+'" readonly></td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="qty_mutasi[]" id="qty_mutasi_'+Urut+'" style="text-align:center;" onkeyup="cekqtymutasi('+Urut+')"></td>'
				+ '<td style="padding:3px;"><center><div class="btn-group" style="margin:0px;">'
				+ '<button type="button" onclick="deleterow('+Urut+','+idnya+')" id="delete-row" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> Hapus</button>'
				+ '</div></center></td>'
				+ '</tr>';
			$("#tabel-detail-mutasi").append(html);
			$("#btn-"+id).removeClass('btn-warning');
			$("#btn-"+id).addClass('btn-danger');
			$("#btn-"+id).attr('disabled',true);
			$("#btn-"+id).text('Sudah');
	   }

    }

    function deleterow(tr,id){
        $('#tr_'+tr).remove();
        $("#btn-"+id).removeClass('btn-danger');
        $("#btn-"+id).addClass('btn-warning');
        $("#btn-"+id).attr('disabled',false);
        $("#btn-"+id).text('Pilih');
    }



    function savemutasi(){
		/*var asal = $('#cabang_asal').val();
        var tujuan = $('#cabang_tujuan').val();
        var supir = $('#supir_mutasi').val();
        var mobil = $('#kendaraan_mutasi').val();
        if(tujuan == "" || supir == "" || mobil == "" || asal==''){
            swal({
                title: "Peringatan!",
                text: "Data harus lengkap",
                type: "warning",
                timer: 1500,
                showConfirmButton: false
            });
			return false
    }*/

        swal({
          title: "Peringatan !",
          text: "Pastikan data sudah lengkap dan benar",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Ya, simpan!",
          cancelButtonText: "Batal!",
          closeOnConfirm: false,
          closeOnCancel: true
        },
        function(isConfirm){
			if(isConfirm) {
				var formdata = $("#form-header-mutasi,#form-detail-mutasi").serialize();
				$.ajax({
					url: siteurl+"internal/savereceive",
					dataType : "json",
					type: 'POST',
					data: formdata,
					success: function(result){
						if(result.save=='1'){
							swal({
								title: "Sukses!",
								text: result['msg'],
								type: "success",
								timer: 1500,
								showConfirmButton: false
							});
							setTimeout(function(){
								window.location.href=siteurl+"internal/po";
							},1600);
						} else {
							swal({
								title: "Gagal!",
								text: "Data Gagal Di Simpan",
								type: "error",
								timer: 1500,
								showConfirmButton: false
							});
						};
					},
					error: function(){
						swal({
							title: "Gagal!",
							text: "Ajax Data Gagal Di Proses",
							type: "error",
							timer: 1500,
							showConfirmButton: false
						});
					}
				});
			}
        });

    }
    function kembali_mutasi(){
        window.location.href = siteurl+"internal/po";
    }
    function filterAngka(a){
        if(!a.match(/^[0-9]+$/)){
            return 0;
        }else{
            return 1;
        }
    }
    function cekqtymutasi(no){
        var mutasi = parseInt($('#qty_kirim_'+no).val());
        var avl = parseInt($('#avl_'+no).val());
        if(filterAngka($('#qty_kirim_'+no).val()) == 1){
            if(mutasi > avl){
                swal({
                    title: "Peringatan!",
                    text: "Qty Mutasi tidak boleh melebihi Stok Avl",
                    type: "warning",
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#qty_kirim_'+no).val(0);
            }
        }else{
            var ang = $('#qty_kirim_'+no).val();
            $('#qty_kirim_'+no).val(ang.replace(/[^0-9]/g,''));
        }
    }
	
	function cekqtyterima(no){
        var mutasi = parseInt($('#qty_kirim_'+no).val());
        var terima = parseInt($('#qty_receive_'+no).val());
        if(filterAngka($('#qty_kirim_'+no).val()) == 1){
            if( terima > mutasi){
                swal({
                    title: "Peringatan!",
                    text: "Qty Terima tidak boleh melebihi Qty Kirim",
                    type: "warning",
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#qty_receive_'+no).val(0);
            }
        }else{
            var ang = $('#qty_receive_'+no).val();
            $('#qty_receive_'+no).val(ang.replace(/[^0-9]/g,''));
        }
    }

	function get_driver_kbm(){
		var cabang_asal		= $('#cabang_asal').val();
		var kirim			= $('#tipekirim').val();
		if(kirim=='' || kirim==null){
			$('#list_kendaraan, #list_supir').empty();
		}else{
			 var Template   ='<span class="input-group-addon"><i class="fa fa-user"></i></span>';
			 var Kendaraan	='<span class="input-group-addon"><i class="fa fa-car"></i></span>';
			if(kirim=='SENDIRI' && cabang_asal !='' && cabang_asal !=null){
				var pecah_cabang	= cabang_asal.split('|');
				// AMBIL DATA DRIVER
				var baseurl=base_url + active_controller +'/get_Driver/'+pecah_cabang[0];
				$.ajax({
					'url'		: baseurl,
					'type'		: 'get',
					'success'	: function(data){
						var datas	= $.parseJSON(data);
						Template	+='<select name="supir_do" id="supir_do" class="form-control input sm">';
						if(!$.isEmptyObject(datas)){
							  $.each(datas,function(key,value){
								  Template    +='<option value="'+key+'^_^'+value+'">'+value+'</option>';
							  });
						  }
					   Template   +='</select>';
					   $('#list_supir').html(Template);
					   $("#supir_do").select2({
						  placeholder: "Pilih",
						  allowClear: true
					   });
					},
					'error'		: function(data){
						alert('An error occured, please try again.');
					}
				});

				// AMBIL DATA KBM
				var baseurl=base_url + active_controller +'/get_Kendaraan/'+pecah_cabang[0];
				$.ajax({
					'url'		: baseurl,
					'type'		: 'get',
					'success'	: function(data){
						var datas	= $.parseJSON(data);
						Kendaraan	+='<select name="kendaraan_do" id="kendaraan_do" class="form-control input sm">';
						if(!$.isEmptyObject(datas)){
							  $.each(datas,function(key,value){
								  Kendaraan    +='<option value="'+key+'^_^'+value+'">'+value+'</option>';
							  });
						  }
					   Kendaraan   +='</select>';
					   $('#list_kendaraan').html(Kendaraan);
					   $("#kendaraan_do").select2({
						  placeholder: "Pilih",
						  allowClear: true
					   });
					},
					'error'		: function(data){
						alert('An error occured, please try again.');
					}
				});
			}else{
				Template	+='<input type="text" name="supir_do" id="supir_do" class="form-control input-sm">';
				$('#list_supir').html(Template);

			   Kendaraan   +='<input type="text" name="kendaraan_do" id="kendaraan_do" class="form-control input-sm">';
			   $('#list_kendaraan').html(Kendaraan);
			}
		}
	}

	function qty(no){

		var harga = parseFloat($('#harga_'+no).val());
        var qty   = parseFloat($('#qty_kirim_'+no).val());
        var total = harga * qty;
		$('#hargatotal_'+no).val(total);

		}



	function updateavl(no){
	var barang = $('#id_barang_rec_mutasi_'+no).val()
	var qty    = $('#qty_kirim_'+no).val()
    $.ajax({
        type:"GET",
        url:siteurl+"internal/update_avl",
        data:"barang="+barang+"&qty="+qty,
        success:function(data){
    }
    })
    }
	
	





</script>
