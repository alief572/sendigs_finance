<table class="table table-bordered" width="100%">
    <?php 
    if(@$so){
    ?>
    <tr>
        <th colspan="7" class="text-center"><?php echo strtoupper($customer->nm_customer)?></th>
    </tr>
    <tr>
        <th width="1%"><center>NO</center></th>
        <th width="15%"><center>No. SO</center></th>
        <th>Nama Barang</th>
        <th width="13%"><center>Tanggal</center></th>
        <th width="10%"><center>Qty Order</center></th>
        <th width="10%"><center>Harga</center></th>
        <th width="10%"><center>Qty Supply</center></th>
        <th width="2%"><center>Action</center></th>
    </tr>
    <?php
        foreach(@$so as $ks=>$vs){
            $getitemso = $this->Detailsalesorder_model->find_all_by(array('no_so'=>$vs->no_so));
    ?>  
    <?php
        $n=1;
        foreach($getitemso as $ik=>$vi){
            $no=$n++;
    ?>
    <tr>
        <td><center><?php echo $no?></center></td>
        <td><center><?php echo $vi->no_so?></center></td>
        <td><?php echo $vi->nm_barang?></td>
        <td><center><?php echo date('d-M-Y',strtotime($vs->tanggal))?></center></td>
        <td><center><?php echo $vi->qty_order?></center></td>
        <td><center><?php echo formatnomor($vi->harga)?></center></td>
        <td><center><?php echo $vi->qty_supply?></center></td>
        <td>
            <center>
                <?php
                $disabled = '';
                $text = "Pilih";
                if($vi->proses_do != 0){
                    $disabled = 'disabled="disabled"';
                    $text = "Sudah";
                }
                ?>
                <button class="btn btn-warning btn-xs" type="button" onclick="pilihsotodo('<?php echo $vi->no_so?>','<?php echo $vi->id_barang?>','<?php echo $customer->id_customer?>','<?php echo $vi->createdby?>')" <?php echo $disabled?>><?php echo $text?></button>
            </center>
        </td>
    </tr>
    <?php } ?>
    <?php } ?>
    <?php }else{ ?>
    <p>Tidak ada data Sales Order</p>
    <?php } ?>
</table>
<script type="text/javascript">
function pilihsotodo(noso,idbrg,cus,by){
    $.ajax({
        type:"POST",
        url:siteurl+"deliveryorder/set_itemdo",
        dataType : "json",
        data:{'NOSO':noso,'IDBRG':idbrg,'CUS':cus,'BY':by},
        success:function(result){
            console.log(result.type);
            if(result.type == "error"){
                swal({
                    title: "Gagal",
                    text: result.pesan,
                    type: result.type,
                    timer: 1500,
                    showConfirmButton: false
                });
            }else{
                swal({
                    title: "Sukses",
                    text: result.pesan,
                    type: result.type,
                    timer: 1500,
                    showConfirmButton: false
                });
                setTimeout(function(){
                    $('#dialog-item-do').modal('hide');
                    //window.location.href=window.location.href;
                    window.location.reload();
                },1800);
            }
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
</script>