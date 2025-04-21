
<div class="box-body">
 <form id="data-form" method="post" autocomplete="off"><br>
   <div class="form-group row">
     <div class="col-md-3">
       <label for="customer">Daycode Before</label>
     </div>
     <div class="col-md-9">
       <input type="text" name="daycode_before" id="daycode_before"  class="form-control input-md" value="<?= ucfirst(strtolower(get_name('report_produksi_daily_detail', 'code', 'id', $results['id']))) ?>" readonly>
       <input type="hidden" name="id" id="id"  class="form-control input-md" value="<?= $results['id'];?>" readonly>
     </div>
    </div>
   <div class="form-group row">
     <div class="col-md-3">
       <label for="customer">Daycode New <span class="text-red">*</span></label>
     </div>
     <div class="col-md-9">
       <select id="daycode_new" name="daycode_new" class="form-control input-md chosen-select" width='100%'>
         <option value="0">Select An Daycode</option>
         <?php foreach (get_daycode() AS $val => $valx){ ?>
                 <option value="<?= $valx['code'];?>"><?= strtoupper($valx['code']);?></option>
         <?php } ?>
       </select>
     </div>
   </div>
   <div class="form-group row">
     <div class="col-md-3">
     </div>
     <div class="col-md-9">
      <button type="button" class="btn btn-primary" name="save" id="save_daycode"><i class="fa fa-save"></i> Save</button>
     </div>
   </div>
 </form>
</div>
