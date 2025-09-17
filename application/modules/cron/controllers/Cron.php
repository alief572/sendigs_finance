<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends Admin_Controller
{
   
   public function __construct()
    {
        parent::__construct();
    }

    public function insert_stok_material_product_perday(){
      //Material
      $getData = $this->db->get_where('warehouse_stock')->result_array();
      $ArrInsert = [];
      foreach ($getData as $key => $value) {
        $ArrInsert[$key]['id_material'] = $value['id_material'];
        $ArrInsert[$key]['nm_material'] = $value['nm_material'];
        $ArrInsert[$key]['id_gudang'] = $value['id_gudang'];
        $ArrInsert[$key]['qty_stock'] = $value['qty_stock'];
        $ArrInsert[$key]['qty_booking'] = $value['qty_booking'];
        $ArrInsert[$key]['qty_rusak'] = $value['qty_rusak'];
        $ArrInsert[$key]['hist_date'] = date('Y-m-d H:i:s');
      }

      //Product
      $getData2 = $this->db->get_where('stock_product')->result_array();
      $ArrInsert2 = [];
      foreach ($getData2 as $key => $value) {
        $ArrInsert2[$key]['code_lv4'] = $value['code_lv4'];
        $ArrInsert2[$key]['no_bom'] = $value['no_bom'];
        $ArrInsert2[$key]['product_name'] = $value['product_name'];
        $ArrInsert2[$key]['actual_stock'] = $value['actual_stock'];
        $ArrInsert2[$key]['booking_stock'] = $value['booking_stock'];
        $ArrInsert2[$key]['hist_date'] = date('Y-m-d H:i:s');
      }

      //Gudang WIP
      $getData3 = $this->db
                      ->select('b.*, COUNT(a.id) AS qty_propose')
                      ->group_by('b.id')
                      ->join('so_internal_spk b','a.id_key_spk=b.id','left')
                      ->get_where('so_internal_product a',array('a.qc_date'=>NULL))->result_array();
      $ArrInsert3 = [];
      foreach ($getData3 as $key => $value) {
        $ArrInsert3[$key]['id_uniq'] = $value['id'];
        $ArrInsert3[$key]['kode_det'] = $value['kode_det'];
        $ArrInsert3[$key]['no_spk'] = $value['no_spk'];
        $ArrInsert3[$key]['id_so'] = $value['id_so'];
        $ArrInsert3[$key]['tanggal'] = $value['tanggal'];
        $ArrInsert3[$key]['qty'] = $value['qty'];
        $ArrInsert3[$key]['qty_propose'] = $value['qty_propose'];
        $ArrInsert3[$key]['id_gudang'] = $value['id_gudang'];
        $ArrInsert3[$key]['id_costcenter'] = $value['id_costcenter'];
        $ArrInsert3[$key]['tanggal_close'] = $value['tanggal_close'];
        $ArrInsert3[$key]['close_by'] = $value['close_by'];
        $ArrInsert3[$key]['close_date'] = $value['close_date'];
        $ArrInsert3[$key]['hist_date'] = date('Y-m-d H:i:s');
      }

      // echo '<pre>';
      // print_r($ArrInsert);
      // print_r($ArrInsert2);
      // exit;

      $this->db->insert_batch('warehouse_stock_per_day',$ArrInsert);
      $this->db->insert_batch('stock_product_per_day',$ArrInsert2);
      $this->db->insert_batch('gudang_wip_per_day',$ArrInsert3);
    }

    
}

?>
