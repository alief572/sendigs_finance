<?php
    $ENABLE_ADD     = has_permission('Costing_rate.Add');
    $ENABLE_MANAGE  = has_permission('Costing_rate.Manage');
    $ENABLE_VIEW    = has_permission('Costing_rate.View');
    $ENABLE_DELETE  = has_permission('Costing_rate.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box">
	<div class="box-body">
        <form id="data-form" method="post">
            <table id="example1" class="table table-bordered">
                <thead>
                    <tr>
                        <th class='text-center' width="5%">#</th>
                        <!-- <th class='text-center' width="5%">Code</th> -->
                        <th class='text-center' width="25%">Element Costing</th>
                        <th class='text-center' width="12%">Rate (%)</th>
                        <th class='text-center'>Keterangan</th>
                        <th class='text-center' width="20%">Elemen COA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    
                    foreach ($results['dataList'] as $key => $value) {
                        if($value['judul'] == 'Material'){
                            echo "<tr>";
                                echo "<td class='text-center'>1</td>";
                                // echo "<td class='text-center text-bold text-primary'>".$value['code']."</td>";
                                echo "<td>".$value['element_costing']."</td>";
                                echo "<td>";
                                    echo "<input type='hidden' id='id_".$value['code']."' name='detail[".$value['code']."][code]' value='".$value['code']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][judul]' value='".$value['judul']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][element_costing]' value='".$value['element_costing']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][keterangan]' value='".$value['keterangan']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][urut]' value='".$value['urutan']."'>";
                                    $rate = ($value['rate'] > 0)?$value['rate']:'';
                                    echo "<input type='text' id='rate_".$value['code']."' name='detail[".$value['code']."][rate]' class='form-control text-center autoNumeric' value='".$rate."'>";
                                echo "</td>";
                                echo "<td>".$value['keterangan']."</td>";
                                echo "<td><input type='text' id='coa_".$value['code']."' name='detail[".$value['code']."][element_coa]' class='form-control' value='".$value['element_coa']."'></td>";
                            echo "</tr>";
                        }
                    }
                    echo "<tr>";
                        echo "<td class='text-center' rowspan='3'>2</td>";
                        // echo "<td class='text-center'></td>";
                        echo "<td class='text-left text-bold' colspan='4'>Manpower</td>";
                    echo "</tr>";
                    foreach ($results['dataList'] as $key => $value) {
                        if($value['judul'] == 'Manpower'){
                            echo "<tr>";
                                // echo "<td class='text-center text-bold text-primary'>".$value['code']."</td>";
                                echo "<td>".$value['element_costing']."</td>";
                                echo "<td>";
                                    echo "<input type='hidden' id='id_".$value['code']."' name='detail[".$value['code']."][code]' value='".$value['code']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][judul]' value='".$value['judul']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][element_costing]' value='".$value['element_costing']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][keterangan]' value='".$value['keterangan']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][urut]' value='".$value['urutan']."'>";
                                    $rate = ($value['rate'] > 0)?$value['rate']:'';
                                    echo "<input type='text' id='rate_".$value['code']."' name='detail[".$value['code']."][rate]' class='form-control text-center autoNumeric' value='".$rate."'>";
                                echo "</td>";
                                echo "<td>".$value['keterangan']."</td>";
                                echo "<td><input type='text' id='coa_".$value['code']."' name='detail[".$value['code']."][element_coa]' class='form-control' value='".$value['element_coa']."'></td>";
                            echo "</tr>";
                        }
                    }
                    echo "<tr>";
                        echo "<td class='text-center' rowspan='4'>3</td>";
                        // echo "<td class='text-center'></td>";
                        echo "<td class='text-left text-bold' colspan='4'>Mesin, cetakan, consumable</td>";
                    echo "</tr>";
                    foreach ($results['dataList'] as $key => $value) {
                        if($value['judul'] == 'Mesin, cetakan, consumable'){
                            echo "<tr>";
                                // echo "<td class='text-center text-bold text-primary'>".$value['code']."</td>";
                                echo "<td>".$value['element_costing']."</td>";
                                echo "<td>";
                                    echo "<input type='hidden' id='id_".$value['code']."' name='detail[".$value['code']."][code]' value='".$value['code']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][judul]' value='".$value['judul']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][element_costing]' value='".$value['element_costing']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][keterangan]' value='".$value['keterangan']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][urut]' value='".$value['urutan']."'>";
                                    $rate = ($value['rate'] > 0)?$value['rate']:'';
                                    echo "<input type='text' id='rate_".$value['code']."' name='detail[".$value['code']."][rate]' class='form-control text-center autoNumeric' value='".$rate."'>";
                                echo "</td>";
                                echo "<td>".$value['keterangan']."</td>";
                                echo "<td><input type='text' id='coa_".$value['code']."' name='detail[".$value['code']."][element_coa]' class='form-control' value='".$value['element_coa']."'></td>";
                            echo "</tr>";
                        }
                    }
                    echo "<tr>";
                        echo "<td class='text-center' rowspan='3'>4</td>";
                        // echo "<td class='text-center'></td>";
                        echo "<td class='text-left text-bold' colspan='4'>Logistik</td>";
                    echo "</tr>";
                    foreach ($results['dataList'] as $key => $value) {
                        if($value['judul'] == 'Logistik'){
                            echo "<tr>";
                                // echo "<td class='text-center text-bold text-primary'>".$value['code']."</td>";
                                echo "<td>".$value['element_costing']."</td>";
                                echo "<td>";
                                    echo "<input type='hidden' id='id_".$value['code']."' name='detail[".$value['code']."][code]' value='".$value['code']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][judul]' value='".$value['judul']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][element_costing]' value='".$value['element_costing']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][keterangan]' value='".$value['keterangan']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][urut]' value='".$value['urutan']."'>";
                                    $rate = ($value['rate'] > 0)?$value['rate']:'';
                                    echo "<input type='text' id='rate_".$value['code']."' name='detail[".$value['code']."][rate]' class='form-control text-center autoNumeric' value='".$rate."'>";
                                echo "</td>";
                                echo "<td>".$value['keterangan']."</td>";
                                echo "<td><input type='text' id='coa_".$value['code']."' name='detail[".$value['code']."][element_coa]' class='form-control' value='".$value['element_coa']."'></td>";
                            echo "</tr>";
                        }
                    }
                    $nomor = 4;
                    foreach ($results['dataList'] as $key => $value) { 
                        if($value['judul'] == 'Lainnya'){
                            $nomor++;
                            echo "<tr>";
                                echo "<td class='text-center'>".$nomor."</td>";
                                // echo "<td class='text-center text-bold text-primary'>".$value['code']."</td>";
                                echo "<td>".$value['element_costing']."</td>";
                                echo "<td>";
                                    echo "<input type='hidden' id='id_".$value['code']."' name='detail[".$value['code']."][code]' value='".$value['code']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][judul]' value='".$value['judul']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][element_costing]' value='".$value['element_costing']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][keterangan]' value='".$value['keterangan']."'>";
                                    echo "<input type='hidden' name='detail[".$value['code']."][urut]' value='".$value['urutan']."'>";
                                    $rate = ($value['rate'] > 0)?$value['rate']:'';
                                    echo "<input type='text' id='rate_".$value['code']."' name='detail[".$value['code']."][rate]' class='form-control text-center autoNumeric' value='".$rate."'>";
                                echo "</td>";
                                echo "<td>".$value['keterangan']."</td>";
                                echo "<td><input type='text' id='coa_".$value['code']."' name='detail[".$value['code']."][element_coa]' class='form-control' value='".$value['element_coa']."'></td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </form>
	</div>
    <div class='box-footer'>
        <button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
    </div>
	<!-- /.box-body -->
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<style>
    
</style>
<!-- page script -->
<script type="text/javascript">
    $(document).ready(function(){
        $('.autoNumeric').autoNumeric()
        $('#rate_1,#rate_2,#rate_4,#rate_5,#rate_8,#rate_15,#rate_16,#rate_19,#coa_14,#coa_15,#coa_16,#coa_17,#coa_18,#coa_19').prop('readonly', true);
    })

    $('#save').click(function(e){
        e.preventDefault();

        swal({
                title: "Are you sure?",
                text: "You will not be able to process again this data!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, Process it!",
                cancelButtonText: "No, cancel process!",
                closeOnConfirm: true,
                closeOnCancel: false
            },
            function(isConfirm) {
                if (isConfirm) {
                    var formData 	=new FormData($('#data-form')[0]);
                    var baseurl = base_url + active_controller + 'saveCostingRate';
                    $.ajax({
                        url			: baseurl,
                        type		: "POST",
                        data		: formData,
                        cache		: false,
                        dataType	: 'json',
                        processData	: false,
                        contentType	: false,
                        success		: function(data){
                            if(data.status == 1){
                                swal({
                                        title	: "Save Success!",
                                        text	: data.pesan,
                                        type	: "success",
                                        timer	: 7000
                                    });
                                window.location.href = base_url + active_controller;
                            }else{

                                if(data.status == 2){
                                    swal({
                                        title	: "Save Failed!",
                                        text	: data.pesan,
                                        type	: "warning",
                                        timer	: 7000
                                    });
                                }else{
                                    swal({
                                        title	: "Save Failed!",
                                        text	: data.pesan,
                                        type	: "warning",
                                        timer	: 7000
                                    });
                                }

                            }
                        },
                        error: function() {
                            swal({
                                title				: "Error Message !",
                                text				: 'An Error Occured During Process. Please try again..',
                                type				: "warning",
                                timer				: 7000
                            });
                        }
                    });
                } else {
                swal("Cancelled", "Data can be process again :)", "error");
                return false;
                }
        });
    });
</script>
