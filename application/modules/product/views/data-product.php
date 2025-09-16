
		<tr>
			<td>Data</td>
		</tr>
          <!-- Data Produk -->

<!-- Modal Bidus-->
<script type="text/javascript">
    $(document).ready(function() {
    //  $("#harga").val(formatCurrency($("#harga").val()));
    });

    function filterAngka1(a){
        document.getElementById(a).value = document.getElementById(a).value.replace(/[^\d]/g,"");
    }

    function formatCurrency(c){
        n = c.replace(/,/g, "");
      var s=n.split('.')[1];
      (s) ? s="."+s : s="";
      n=n.split('.')[0]
      while(n.length>3){
          s="."+n.substr(n.length-3,3)+s;
          n=n.substr(0,n.length-3)
      }
      return n+s

      }
      function formatnomor($angka)
      {
       if($angka){
           $jadi = number_format($angka,0,',','.');
           return $jadi;
          }
          else {
            return 0;
          }
      }
      function load_foto_barang(id){
      $.ajax({
          type:"GET",
          url:siteurl+"barang/load_foto_barang",
          data:"id="+id,
          success:function(html){

              $("#list_foto_barang").html(html);
          }
      })
    }

    function get_nmgroup() {
        var id_group = $('#id_group').val();
        $.ajax({
            type:"GET",
            url:siteurl+"barang/get_nmgroup",
            data:"id_group="+id_group,
            dataType : "json",
            success:function(msg){
               $("#nm_group").val(msg['nm_group']);
               //setNapro();
            }
        });
    }

    function get_nmcp() {
        var id_colly_produk = $('#id_colly_produk').val();
        $.ajax({
            type:"GET",
            url:siteurl+"barang/get_nmcp",
            data:"id_colly_produk="+id_colly_produk,
            dataType : "json",
            success:function(msg){
               $("#nm_cp").val(msg['nm_cp']);
               set_nmcolly();
            }
        });
    }

    function set_nmcolly(){
        var nm_cp = $('#nm_cp').val();
        var varian = $('#variank').val();

        var nakol = nm_cp+' '+varian;
        $('#nm_koli').val(nakol);
    }

    function setNapro(){
        var series = $('#series').val();
        var nm_group = $('#nm_group').val();
        var varian = $('#varian').val();
        var nmbar = series+' '+varian+' '+nm_group;
        //alert(series);
        $('#nm_barang').val(nmbar);
    }

    function get_cp(){
        $.ajax({
            type:"GET",
            url:siteurl+"barang/get_cp",
            success:function(html){
               $("#id_colly_produk").html(html);
            }
        });
    }

    function get_gb(){
        $.ajax({
            type:"GET",
            url:siteurl+"barang/get_gb",
            success:function(html){
               $("#id_group").html(html);
            }
        });
    }

    function get_jb(){
        $.ajax({
            type:"GET",
            url:siteurl+"barang/get_jb",
            success:function(html){
               $("#id_jenis").html(html);
            }
        });
    }

    function get_koli(id_barang){
        $.ajax({
            type:"GET",
            url:siteurl+"barang/get_koli",
            data:"id_barang="+id_barang,
            success:function(html){
               $("#id_koli_c").html(html);
            }
        });
    }

    //save_cp
    function save_cp(){

      var colly_produk=$("#colly_produk").val();
      var id_colly=$("#id_colly").val();
      if(colly_produk==''){
          swal({
          title: "Peringatan!",
          text: "Isi Data Dengan Lengkap!",
          type: "warning",
          confirmButtonText: "Ok"
          });
          //die;
      }else{
          $.ajax({
              type:"POST",
              url:siteurl+"barang/add_cp",
              data :{"id_colly":id_colly,"colly_produk":colly_produk},
              dataType : "json",
              success:function(msg){
                  $('#add_cp').modal('hide');
                  $('#form_cp')[0].reset(); // reset form on modals
                  get_cp();
                  ListCP();
              }
          });
      }

    }
    //save_gb
    function save_gb(){

      var id_group=$("#add_id_group").val();
      var nm_group=$("#add_nm_group").val();

      if(id_group=='' || nm_group==''){
          swal({
            title: "Peringatan!",
            text: "Isi Data Dengan Lengkap!",
            type: "warning",
            confirmButtonText: "Ok"
          });
          //die;
      }else{
          $.ajax({
              type:"POST",
              url:siteurl+"barang/add_gb",
              data :{"id_group":id_group,"nm_group":nm_group},
              dataType : "json",
              success:function(msg){
                  $('#add_gb').modal('hide');
                  $('#form_gb')[0].reset(); // reset form on modals
                  get_gb();
              }
          });
      }

    }

    //save_jb
    function save_jb(){

      var id_jenis=$("#add_id_jenis").val();
      var nm_jenis=$("#nm_jenis").val();

      if(id_jenis=='' || nm_jenis==''){
          swal({
            title: "Peringatan!",
            text: "Isi Data Dengan Lengkap!",
            type: "warning",
            confirmButtonText: "Ok"
          });
          //die;
      }else{
          $.ajax({
              type:"POST",
              url:siteurl+"barang/add_jb",
              data :{"id_jenis":id_jenis,"nm_jenis":nm_jenis},
              dataType : "json",
              success:function(msg){
                  $('#add_jb').modal('hide');
                  $('#form_jb')[0].reset(); // reset form on modals
                  get_jb();
              }
          });
      }

    }

    $(document).ready(function() {

        var type = $('#type').val();
        var foto = $('#id_barang').val();
            if(type=='edit'){
                ShowOtherButton();
                load_foto_barang(foto);
                //$("#id_jenis").prop("disabled", true);
                //$("#id_group").prop("disabled", true);
                //$("#series").prop("disabled", true);
                //$("#varian").prop("disabled", true);
            }else{
                HideOtherButton();
            }

        /*$("input#series").on({
          keydown: function(e) {
            if (e.which === 32)
              return false;
          },
          change: function() {
            this.value = this.value.replace(/\s/g, "");
          }
        });*/

        $(".pil_sup").select2({
            placeholder: "Pilih Supplier Produk",
            allowClear: true
        });

        $(".pil_jb").select2({
            placeholder: "Pilih Jenis Produk",
            allowClear: true
        });

        $(".pil_gb").select2({
            placeholder: "Pilih Group Produk",
            allowClear: true
        });

        $(".pil_cp").select2({
            placeholder: "Pilih Colly Produk",
            allowClear: true
        });

        $(".pil_koli").select2({
            placeholder: "Pilih Data Colly",
            allowClear: true
        });

        $("#id_koli_model,#id_koli_warna,#id_koli_varian").select2({
            placeholder: "Pilih Data Colly",
            allowClear: true
        });

        //Date picker
        $('#tanggallahir').datepicker({
          format: 'dd-mm-yyyy',
          todayHighlight: true,
          //startDate: new Date(),
          autoclose: true
        });

        $('#data').click(function(){
            var id = $('#id_barang').val();
            if(id==''){
                $("#list_foto_barang").hide();
            }else{
                load_foto_barang(id);
            }
        });

        $('#data_koli').click(function(){
            var id = $('#id_barang').val();
            if(id==''){
                $("#list_koli").hide();
            }else{
                load_koli(id);
            }
        });

        $('#data_komponen').click(function(){
            var id = $('#id_barang').val();
            if(id==''){
                $("#list_komponen").hide();
            }else{
                load_komponen(id);
                get_koli(id);
            }
        });

    });

    $('#id_koli_model,#id_koli_warna,#id_koli_varian').on('change', function(){

      var model = $("#id_koli_model option:selected").text().split(' ').join('');
      var warna = $("#id_koli_warna option:selected").text().split(' ').join('');
      var varian = $("#id_koli_varian option:selected").text().split(' ').join('');
      console.log(model);
      $("#nm_koli").val(model+' '+warna+' '+varian);
    });

    //Data Koli
    function load_koli(id_barang){
      $.ajax({
          type:"GET",
          url:siteurl+"barang/load_koli",
          data:"id_barang="+id_barang,
          success:function(html){
              $("#list_koli").html(html);
          }
      })
    }

    //Data komponen
    function load_komponen(id_barang){
      $.ajax({
          type:"GET",
          url:siteurl+"barang/load_komponen",
          data:"id_barang="+id_barang,
          success:function(html){
              $("#list_komponen").html(html);
          }
      })
    }

    function ListCP(){
      $.ajax({
          type:"GET",
          url:siteurl+"barang/ListCP",
          success:function(html){
              $("#list_cp").html(html);
          }
      })
    }

    function cancel(){
        $(".box").show();
        $("#form_barang").hide();
        window.location.reload();
        //reload_table();
    }

    //Barang
    $('#frm_barang').on('submit', function(e){
        e.preventDefault();
        $('#harga').val($('#harga').val().replace(/[^\d]/g,""));
        var formdata = $("#frm_barang").serialize();
        $.ajax({
          /*
          url: siteurl+"barang/save_data_ajax",
          dataType : "json",
          type: 'POST',
          processData:false,
          contentType:false,
          cache:false,
          async:false,
          data: new FormData(this),
          */
          url: siteurl+"barang/save_data_ajax",
          dataType : "json",
          type: 'POST',
          data: formdata,
            //alert(msg);
            success: function(msg){
                if(msg['save']=='1'){
                    var barang =msg['barang'];
                    swal({
                      title: "Sukses!",
                      text: "Data Berhasil Disimpan, Lanjutkan pengisian data Koli",
                      type: "success",
                      showCancelButton: true,
                      confirmButtonColor: "Blue",
                      confirmButtonText: "Ya, Lanjutkan",
                      cancelButtonText: "Tidak, Lain waktu saja",
                      closeOnConfirm: true,
                      closeOnCancel: true
                    },
                    function(isConfirm){
                      if (isConfirm) {
                        $('[href="#koli"]').tab('show');
                        $('#barang').val(barang);
                        load_koli(barang);
                        ShowOtherButton();
                      } else {
                        load_foto_barang(barang);
                        window.location.reload();
                        cancel();
                      }
                    });
                } else {
                    swal({
                        title: "Gagal!",
                        text: "Data Gagal Di Simpan",
                        type: "error",
                        timer: 1500,
                        showConfirmButton: false
                    });
                };//alert(msg);
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
    });

    //Koli
    $('#frm_koli').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#frm_koli").serialize();
        $.ajax({
            url: siteurl+"barang/save_data_koli",
            dataType : "json",
            type: 'POST',
            data: formdata,
            //alert(msg);
            success: function(msg){
                if(msg['save']=='1'){
                    var barang =msg['barang'];
                    swal({
                      title: "Sukses!",
                      text: "Data Berhasil Disimpan, Lanjutkan pengisian data Komponen",
                      type: "success",
                      showCancelButton: true,
                      confirmButtonColor: "Blue",
                      confirmButtonText: "Ya, Lanjutkan",
                      cancelButtonText: "Tidak, Lain waktu saja",
                      closeOnConfirm: true,
                      closeOnCancel: true
                    },
                    function(isConfirm){
                      if (isConfirm) {
                        $('[href="#komponen"]').tab('show');
                        $('#barangc').val(barang);
                        get_koli(barang);
                        load_komponen(barang);
                        ShowOtherButton();
                      } else {
                        load_koli(barang);
                        //window.location.reload();
                        //cancel();
                      }
                    });
                } else {
                    swal({
                        title: "Gagal!",
                        text: "Data Gagal Di Simpan",
                        type: "error",
                        timer: 1500,
                        showConfirmButton: false
                    });
                };//alert(msg);
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
    });

    //Komponen
    $('#frm_komponen').on('submit', function(e){
        e.preventDefault();
        var formdata = $("#frm_komponen").serialize();
        $.ajax({
            url: siteurl+"barang/save_data_komponen",
            dataType : "json",
            type: 'POST',
            processData:false,
            contentType:false,
            cache:false,
            async:false,
            data: new FormData(this),
            //alert(msg);
            success: function(msg){
                var barang = msg['barang'];
                if(msg['save']=='1'){
                    swal({
                        title: "Sukses!",
                        text: "Data Berhasil Di Simpan",
                        type: "success",
                        timer: 1500,
                        showConfirmButton: false
                    });
                    load_komponen(barang);
                    //cancel();
                    //document.getElementById("frm_biodata").reset();
                } else {
                    swal({
                        title: "Gagal!",
                        text: "Data Gagal Di Simpan",
                        type: "error",
                        timer: 1500,
                        showConfirmButton: false
                    });
                };//alert(msg);
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
    });

    function ShowOtherButton()
    {
        //after success saving then activate sumbit button on each tab
        $("#btnkoli").show();
        $("#btnkomponen").show();
    }

    function HideOtherButton()
    {
        //after success saving then activate sumbit button on each tab
        $("#btnkoli").hide();
        $("#btnkomponen").hide();
    }


    //Edit Koli
    function edit_koli(id_koli){
      if(id_koli != ""){
          $.ajax({
              type:"POST",
              url:siteurl+"barang/edit_koli",
              data:{"id_koli":id_koli},
              success:function(result){
                  var data = JSON.parse(result);
                  $('#type1').val('edit');
                  $('#id_koli').val(data.id_koli);
                  $('#nm_koli').val(data.nm_koli);
                  $('#qty_koli').val(data.qty);
                  $('#id_koli_model').val(data.id_koli_model).change();
                  $('#id_koli_warna').val(data.id_koli_warna).change();
                  $('#id_koli_varian').val(data.id_koli_varian).change();
                  $('#sts_aktif').val(data.sts_aktif);
                  $('#keterangan_kol').val(data.keterangan);
                  $('#c_netto_weight').val(data.netto_weight);
                  $('#c_cbm_each').val(data.cbm_each);
                  $('#c_gross_weight').val(data.gross_weight);
              }
          })
      }
    }

    function edit_komponen(id_komponen){
      if(id_komponen != ""){
          $.ajax({
              type:"POST",
              url:siteurl+"barang/edit_komponen",
              data:{"id_komponen":id_komponen},
              success:function(result){
                  var data = JSON.parse(result);
                  $('#type2').val('edit');
                  $('#id_koli_c').val(data.id_koli).change();
                  $('#id_komponen').val(data.id_komponen);
                  $('#nm_komponen').val(data.nm_komponen);
                  $('#qty_komponen').val(data.qty);
                  $('#sts_aktif').val(data.sts_aktif);
                  $('#keterangan_kom').val(data.keterangan);
              }
          })
      }
    }


    function edit_cp(id){
        if(id != ""){
        $.ajax({
            type:"POST",
            url:siteurl+"barang/edit_cp",
            data:{"id":id},
            success:function(result){
                var data = JSON.parse(result);
                $('#id_colly').val(data.id_colly_produk);
                $('#colly_produk').val(data.colly_produk);
            }
        })
    }
    }

    //Delete Koli
    function hapus_koli(id){
        //alert(id);
        swal({
          title: "Anda Yakin?",
          text: "Data Toko Akan Terhapus secara Permanen!",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Ya, delete!",
          cancelButtonText: "Tidak!",
          closeOnConfirm: false,
          closeOnCancel: true
        },
        function(isConfirm){
          if (isConfirm) {
            $.ajax({
                    url: siteurl+'barang/hapus_koli/'+id,
                    dataType : "json",
                    type: 'POST',
                    success: function(msg){
                        if(msg['delete']=='1'){
                            $("#dataku"+id).hide(2000);
                            //swal("Terhapus!", "Data berhasil dihapus.", "success");
                            swal({
                              title: "Terhapus!",
                              text: "Data berhasil dihapus",
                              type: "success",
                              timer: 1500,
                              showConfirmButton: false
                            });
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

    //Delete Komponen
    function hapus_komponen(id){
        //alert(id);
        swal({
          title: "Anda Yakin?",
          text: "Data Toko Akan Terhapus secara Permanen!",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Ya, delete!",
          cancelButtonText: "Tidak!",
          closeOnConfirm: false,
          closeOnCancel: true
        },
        function(isConfirm){
          if (isConfirm) {
            $.ajax({
                    url: siteurl+'barang/hapus_komponen/'+id,
                    dataType : "json",
                    type: 'POST',
                    success: function(msg){
                        if(msg['delete']=='1'){
                            $("#dataku"+id).hide(2000);
                            //swal("Terhapus!", "Data berhasil dihapus.", "success");
                            swal({
                              title: "Terhapus!",
                              text: "Data berhasil dihapus",
                              type: "success",
                              timer: 1500,
                              showConfirmButton: false
                            });
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
</script>
