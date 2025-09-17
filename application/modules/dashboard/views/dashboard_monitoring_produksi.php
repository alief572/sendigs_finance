<!-- Dashboard2 -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<style>
    .text-sisa {
        color: #d64161;
    }

    .text-rilis {
        color: #82b74b;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-body">
                <div class="col-12">
                    <table class="table table-bordered" id="table_monitoring">
                        <thead class="bg-primary">
                            <tr>
                                <th class="text-center" rowspan="2" style="vertical-align: middle;">No.</th>
                                <th class="text-center" rowspan="2" style="vertical-align: middle;" width="250">Nama Produk</th>
                                <th class="text-center" rowspan="2" style="vertical-align: middle;">Actual Stock Downgrade</th>
                                <th class="text-center" rowspan="2" style="vertical-align: middle;">Actual Stock OK</th>
                                <th class="text-center" colspan="3">On Progress</th>
                            </tr>
                            <tr>
                                <th class="text-center">QC</th>
                                <th class="text-center">Produksi</th>
                                <th class="text-center">PPIC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($list_data as $item) {

                                $stock_downgrade = 0;
                                $stock_ok = 0;

                                $this->db->select('a.ng_stock, a.actual_stock');
                                $this->db->from('stock_product a');
                                $this->db->join('new_inventory_4 b', 'b.code_lv4 = a.code_lv4', 'left');
                                $this->db->where('a.code_lv4', $item->code_lv4);
                                $this->db->where('a.no_bom', $item->no_bom);
                                $this->db->where('a.id', 'MAX(a.id)');
                                $this->db->group_by('a.code_lv4', 'a.no_bom');
                                $get_stock = $this->db->get()->row();

                                if (!empty($get_stock)) {
                                    $stock_downgrade = $get_stock->ng_stock;
                                    $stock_ok = $get_stock->actual_stock;
                                }


                                $this->db->select('b.no_spk, b.qty');
                                $this->db->from('so_internal a');
                                $this->db->join('so_internal_spk b', 'b.id_so = a.id');
                                $this->db->join('so_internal_product c', 'c.kode = b.kode');
                                $this->db->where('a.code_lv4', $item->code_lv4);
                                $this->db->where('a.no_bom', $item->no_bom);
                                $this->db->where_in('b.sts_close', ['N']);
                                $this->db->group_by('b.kode');
                                $get_qc = $this->db->get()->result();

                                $this->db->select('b.id, b.no_spk, b.qty');
                                $this->db->from('so_internal a');
                                $this->db->join('so_internal_spk b', 'b.id_so = a.id', 'left');
                                $this->db->where('a.code_lv4', $item->code_lv4);
                                $this->db->where('a.no_bom', $item->no_bom);
                                $this->db->where('a.deleted_date', null);
                                $this->db->where('b.sts_request', 'Y');
                                $this->db->where('b.status_id', 1);

                                $this->db->group_by('b.kode');
                                $get_prod = $this->db->get()->result();

                                $this->db->select('a.so_number, a.propose, IF(SUM(b.qty) IS NULL, 0, SUM(b.qty)) as qty_spk');
                                $this->db->from('so_internal a');
                                $this->db->join('so_internal_spk b', 'b.id_so = a.id', 'left');
                                $this->db->where('a.code_lv4', $item->code_lv4);
                                $this->db->where('a.no_bom', $item->no_bom);
                                $this->db->where('a.deleted_date', null);
                                $this->db->where('b.sts_request', 'Y');
                                $this->db->where('b.status_id', 1);
                                $this->db->where('(SELECT SUM(aa.qty) FROM so_internal_spk aa WHERE aa.id_so = a.id) <> a.propose');
                                $this->db->group_by('a.id');
                                $get_ppic = $this->db->get()->result();

                                // if(!$get_ppic){
                                //     print_r($this->db->last_query());
                                //     exit;
                                // }                                 


                                echo '<tr>';
                                echo '<td class="text-center">' . $no . '</td>';
                                echo '<td class="text-left">' . $item->product_name . '</td>';
                                echo '<td class="text-right">' . number_format($stock_downgrade) . '</td>';
                                echo '<td class="text-right">' . number_format($stock_ok) . '</td>';
                                echo '<td class="text-left">';

                                $no_qc = 1;
                                foreach ($get_qc as $item_qc) {
                                    echo $no_qc . '. ' . $item_qc->no_spk . ' - (' . number_format($item_qc->qty) . ' Pcs)<br>';

                                    $no_qc++;
                                }

                                echo '</td>';
                                echo '<td class="text-left">';

                                $no_prod = 1;
                                foreach ($get_prod as $item_prod) {
                                    $checkCLose_prod = (!empty(checkInputProduksiQty($item_prod->id)[$item_prod->id])) ? checkInputProduksiQty($item_prod->id)[$item_prod->id] : 0;
                                    if ($item_prod->qty <> $checkCLose_prod) {
                                        echo $no_prod . '. ' . $item_prod->no_spk . ' (' . number_format($item_prod->qty) . ' Pcs)<br>';

                                        $no_prod++;
                                    }
                                }

                                echo '</td>';
                                echo '<td class="text-left">';

                                $no_ppic = 1;
                                foreach ($get_ppic as $item_ppic) {
                                    if ($item_ppic->propose !== $item_ppic->qty_spk) {
                                        echo $no_ppic . '. ' . $item_ppic->so_number . ' (<span class="text-rilis">Rilis: ' . number_format($item_ppic->qty_spk) . '</span>, <span class="text-sisa">Sisa: ' . number_format($item_ppic->propose - $item_ppic->qty_spk) . '</span>)<br>';
                                    }

                                    $no_ppic++;
                                }

                                echo '</td>';
                                echo '</tr>';

                                $no++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .mid {
        vertical-align: middle !important;
    }

    .chosen-select {
        min-width: 200px !important;
        max-width: 100% !important;
    }

    .bold {
        font-weight: bold !important;
        padding-right: 20px !important;
    }
</style>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#table_monitoring').dataTable();
    });
</script>