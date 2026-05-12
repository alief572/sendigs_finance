<div class="box">
    <div class="box-body">
        <table class="table table-bordered table-striped" width="100%">
            <thead class="bg-primary">
                <tr>
                    <th class="text-center">No.</th>
                    <th class="text-center">Company</th>
                    <th class="text-center">No. Penawaran</th>
                    <th class="text-center">Penjualan</th>
                    <th class="text-center">PIC</th>
                    <th class="text-center">Invoice Created</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 0;
                foreach ($list_penawaran_non_kons as $item) {
                    $no++;

                    $sts = '<div class="badge bg-yellow">Draft</div>';

                    $btn_create = '<a href="' . base_url('invoicing/create_invoice_non_konsultasi/' . $item->id_penawaran) . '" class="btn btn-sm btn-success" title="Select Quotation"><i class="fa fa-plus"></i></a>';

                    $btn_close = '';

                    $this->db->select('a.id');
                    $this->db->from('tr_invoicing a');
                    $this->db->where('a.id_penawaran', $item->id_penawaran);
                    $check_invoice = $this->db->get()->num_rows();

                    if ($check_invoice > 0) {
                        $btn_close = '<button type="button" class="btn btn-sm btn-danger close_penawaran" data-id_penawaran="' . $item->id_penawaran . '" title="Close Penawaran"><i class="fa fa-close"></i></button>';

                        $sts = '<div class="badge bg-green">Invoice Created</div>';
                    }

                    $buttons = $btn_create . ' ' . $btn_close;

                    $get_jum_invoice = $this->db->get_where('tr_invoicing', ['id_penawaran' => $item->id_penawaran, 'non_kons' => '1'])->num_rows();

                    $jum_invoice = $get_jum_invoice ?? 0;

                    echo '
                        <tr>
                            <td class="text-center">' . $no . '</td>
                            <td class="text-left">' . $item->nm_company . '</td>
                            <td class="text-center">' . $item->id_penawaran . '</td>
                            <td class="text-right">' . number_format($item->grand_total) . '</td>
                            <td class="text-center">' . $item->pic . '</td>
                            <td class="text-center">' . $jum_invoice . '</td>
                            <td class="text-center">' . $sts . '</td>
                            <td class="text-center">' . $buttons . '</td>
                        </tr>
                    ';
                }
                ?>
            </tbody>
        </table>
        <br>
        <a href="<?= base_url('invoicing') ?>" class="btn btn-sm btn-danger"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).on('click', '.close_penawaran', function() {
        var id_penawaran = $(this).data('id_penawaran');

        Swal.fire({
            title: 'Konfirmasi Penutupan',
            text: 'Penawaran ini akan ditutup secara permanen dan tidak dapat membuat Invoice kembali. Lanjutkan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Tutup Invoice!',
            cancelButtonText: 'Batal',
            allowOutsideClick: false
        }).then((next) => {
            if (next.isConfirmed) {
                Swal.fire({
                    title: 'Sedang memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'close_penawaran_non_kons',
                    data: {
                        'id_penawaran': id_penawaran
                    },
                    cache: false,
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.msg,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = 'Terjadi kesalahan sistem.';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.msg;
                        } catch (e) {
                            errorMsg = 'Gagal memproses permintaan (Error: ' + xhr.status + ')';
                        }

                        Swal.fire({
                            title: 'Oops!',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonText: 'Tutup'
                        });
                    }
                })
            }
        });
    })
</script>