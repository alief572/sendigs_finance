
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="col-md-12">
    <b>Material Name : <?= $results['nm_material'] ?></b>
    <br><br>
    <table class="table table-bordered">
        <thead class="bg-blue">
            <tr>
                <th class="text-center">Tgl</th>
                <th class="text-center">Transaksi</th>
                <th class="text-center">Qty Transaksi</th>
                <th class="text-center">Qty</th>
                <th class="text-center">Nilai In</th>
                <th class="text-center">CostBook</th>
                <th class="text-center">Value Transaksi</th>
                <th class="text-center">Value Neraca</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                foreach($results['list_data'] as $item){
                    echo '<tr>'; 
                    echo '<td class="text-center">'.date('d F Y', strtotime($item->tgl)).'</td>';
                    echo '<td class="text-left">'.$item->jenis_transaksi.'</td>';
                    echo '<td class="text-right">'.number_format($item->qty_transaksi, 2).'</td>';
                    echo '<td class="text-right">'.number_format($item->qty, 2).'</td>';
                    echo '<td class="text-right">'.number_format($item->nilai_beli, 2).'</td>';
                    echo '<td class="text-right">'.number_format($item->costbook, 2).'</td>';
                    echo '<td class="text-right">'.number_format($item->value_transaksi, 2).'</td>';
                    echo '<td class="text-right">'.number_format($item->value_neraca, 2).'</td>';
                    echo '</tr>';
                }
            ?>
        </tbody>
    </table>
</div>
<!-- awal untuk modal dialog -->
<!-- Modal -->


<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        $('#example1').dataTable();
    });
</script>