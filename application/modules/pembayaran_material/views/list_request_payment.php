<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
    <div class="box-header">
        <a href="<?= base_url('pembayaran_material/payment_list') ?>" class="btn btn-sm btn-danger"><i class="fa fa-arrow-left"></i> Back</a>
        <button type="button" class="btn btn-sm btn-warning clear_choosed_payment"><i class="fa fa-refresh"></i> Clear Checked Payment</button>
        <button type="button" class="btn btn-sm btn-success proses_payment"><i class="fa fa-check"></i> Proses</button>
    </div>
    <div class="box-body">
        <table class="table table-bordered" id="table_list_req_payment" data-jenis-payment="<?= htmlspecialchars((string) $jenis_payment, ENT_QUOTES, 'UTF-8') ?>">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">No. Dokumen</th>
                    <th class="text-center">Tgl</th>
                    <th class="text-center">Company</th>
                    <th class="text-center">Keperluan</th>
                    <th class="text-center">Currency</th>
                    <th class="text-center">Total Invoice</th>
                    <th class="text-center">Requestor</th>
                    <th class="text-center">Option</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>

<script src="<?= base_url('assets/js/pembayaran_material/payment_selection.js') ?>"></script>
