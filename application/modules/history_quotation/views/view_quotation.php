<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<form id="form-header-mutasi" method="post">
    <div class="nav-tabs-salesorder">
        <div class="tab-content">
            <div class="tab-pane active" id="salesorder">
                <div class="box box-primary">
                    <?php //print_r($kode_customer)
                    ?>
                    <div class="box-body">
                        <div class="col-sm-12 form-horizontal">
                            <div class="row">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="col-sm-6">
                                            <div class="form-group row">
                                                <div class="col-md-4">
                                                    <label for="customer">Quotation By :</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <select name="quote_by" id="" class="form-control" disabled>
                                                        <option value="ORINDO" <?= (isset($results['data_penawaran']) && $results['data_penawaran']->quote_by == 'ORINDO') ? 'selected' : null ?>>ORINDO</option>
                                                        <option value="ORIGA" <?= (isset($results['data_penawaran']) && $results['data_penawaran']->quote_by == 'ORIGA') ? 'selected' : null ?>>ORIGA</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-4">
                                                    <label for="customer">Quotation No :</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control no_surat" id="" required name="no_surat" readonly placeholder="Quotation No" value="<?= (isset($results['data_penawaran'])) ? $results['data_penawaran']->no_penawaran : null ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group row">
                                                <div class="col-md-4">
                                                    <label for="customer">Quotation Date</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="date" class="form-control" id="tanggal" onkeyup required name="tanggal" value="<?= (isset($results['data_penawaran'])) ? $results['data_penawaran']->tgl_penawaran : date('Y-m-d') ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="col-sm-6">
                                            <div class="form-group row">
                                                <div class="col-md-4">
                                                    <label for="id_customer">Customer</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <select id="id_customer" name="id_customer" class="form-control select2 get_data_customer" disabled>
                                                        <option value="">--Pilih--</option>
                                                        <?php foreach ($results['customers'] as $customers) { ?>
                                                            <option value="<?= $customers->id_customer ?>" <?= (isset($results['data_penawaran']) && $customers->id_customer) ? 'selected' : null ?>><?= ucfirst($customers->nm_customer) ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group row">
                                                <div class="col-md-4">
                                                    <label for="id_category_supplier">Sales/Marketing</label>
                                                </div>
                                                <div id="sales_slot">
                                                    <div class='col-md-8' hidden>
                                                        <input type='text' class='form-control' id='nama_sales' required name='nama_sales' readonly placeholder='Sales Marketing' value="">
                                                    </div>
                                                    <div class='col-md-8'>
                                                        <input type='text' class='form-control' id='id_sales' required name='id_sales' readonly placeholder='Sales Marketing' value="<?= (isset($results['data_penawaran'])) ? $results['data_penawaran']->nm_lengkap : $results['nm_sales'] ?>" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class='col-sm-6'>
                                            <div class='form-group row'>
                                                <div class='col-md-4'>
                                                    <label for='id_category_supplier'>PIC Customer</label>
                                                </div>
                                                <div class='col-md-8' id="pic_slot">
                                                    <select id='pic_customer' name='pic_customer' class='form-control select2' disabled>
                                                        <option value=''>--Pilih--</option>
                                                        <?php
                                                        foreach ($results['pic_cust'] as $pic) {
                                                            $selected = '';
                                                            if (isset($results['data_penawaran']) && $results['data_penawaran']->pic_customer == $pic->id_pic) {
                                                                $selected = 'selected';
                                                            }
                                                            echo '<option value="' . $pic->id_pic . '" ' . $selected . '>' . ucfirst($pic->nm_pic) . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-sm-6'>
                                            <div class='form-group row'>
                                                <div class='col-md-4'>
                                                    <label for='email_customer'>Email</label>
                                                </div>
                                                <div class='col-md-8' id="email_slot">
                                                    <input type='email' class='form-control' id='email_customer' required name='email_customer' value="<?= (isset($results['data_penawaran'])) ? $results['data_penawaran']->email_customer : null ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class='col-sm-6'>
                                            <div class='form-group row'>
                                                <div class='col-md-4'>
                                                    <label for='email_customer'>Term Of Payment</label>
                                                </div>
                                                <div class='col-md-8'>

                                                    <select name="term_of_payment" id="" class="form-control" disabled>
                                                        <option value="">- Pilih TOP -</option>
                                                        <?php
                                                        foreach ($results['list_top'] as $top) {
                                                            $selected = '';
                                                            if (isset($results['data_penawaran']) && $results['data_penawaran']->top == $top->id) {
                                                                $selected = 'selected';
                                                            }
                                                            echo '<option value="' . $top->id . '" ' . $selected . '>' . $top->name . '</option>';
                                                        }
                                                        ?>
                                                    </select>

                                                    <textarea name="term_of_payment_custom" id="" cols="30" rows="5" class="form-control" style="margin-top: 2rem;" readonly><?= (isset($results['data_penawaran'])) ? $results['data_penawaran']->top_custom : null ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-sm-6'>
                                            <div class='form-group row'>
                                                <div class='col-md-4'>
                                                    <label for='email_customer'>Project</label>
                                                </div>
                                                <div class='col-md-8' id="">
                                                    <input type="text" name="project" id="" class="form-control" value="<?= (isset($results['data_penawaran'])) ? $results['data_penawaran']->project : null ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mt-5">
                                        <h4>Additional Information</h4>
                                    </div>

                                    <div class="col-md-12">
                                        <div class='col-sm-6'>
                                            <div class='form-group row'>
                                                <div class='col-md-4'>
                                                    <label for='email_customer'>Subject</label>
                                                </div>
                                                <div class='col-md-8'>
                                                    <input type="text" name="subject" id="" class="form-control" value="<?= (isset($results['data_penawaran'])) ? $results['data_penawaran']->subject : null ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-sm-6'>
                                            <div class='form-group row'>
                                                <div class='col-md-4'>
                                                    <label for='email_customer'>Time of Delivery</label>
                                                </div>
                                                <div class='col-md-8' id="">
                                                    <input type="text" name="time_delivery" id="" class="form-control" value="<?= (isset($results['data_penawaran'])) ? $results['data_penawaran']->time_delivery : null ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class='col-sm-6'>
                                            <div class='form-group row'>
                                                <div class='col-md-4'>
                                                    <label for='email_customer'>Offer Period</label>
                                                </div>
                                                <div class='col-md-8'>
                                                    <input type="text" name="offer_period" id="" class="form-control" value="<?= (isset($results['data_penawaran'])) ? $results['data_penawaran']->offer_period : null ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-sm-6'>
                                            <div class='form-group row'>
                                                <div class='col-md-4'>
                                                    <label for='email_customer'>Delivery Term</label>
                                                </div>
                                                <div class='col-md-8' id="">
                                                    <input type="text" name="delivery_term" id="" class="form-control" value="<?= (isset($results['data_penawaran'])) ? $results['data_penawaran']->delivery_term : null ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class='col-sm-6'>
                                            <div class='form-group row'>
                                                <div class='col-md-4'>
                                                    <label for='email_customer'>Warranty</label>
                                                </div>
                                                <div class='col-md-8'>
                                                    <input type="text" name="warranty" id="" class="form-control" value="<?= (isset($results['data_penawaran'])) ? $results['data_penawaran']->warranty : null ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    if (isset($results['data_penawaran'])) {
                                        if ($results['data_penawaran']->keterangan_app1 !== null && $results['data_penawaran']->keterangan_app1 !== "") {
                                    ?>
                                            <div class="col-md-12">
                                                <div class='col-sm-6'>
                                                    <div class='form-group row'>
                                                        <div class='col-md-4'>
                                                            <label for='email_customer'>Keterangan Reject</label>
                                                        </div>
                                                        <div class='col-md-8' id="">
                                                            <textarea name="" id="" cols="30" rows="5" class="form-control" readonly><?= $results['data_penawaran']->keterangan_app1 ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                        <?php
                                        }
                                        if ($results['data_penawaran']->keterangan_app2 !== null && $results['data_penawaran']->keterangan_app2 !== "") {
                                        ?>

                                            <div class="col-md-12">
                                                <div class='col-sm-6'>
                                                    <div class='form-group row'>
                                                        <div class='col-md-4'>
                                                            <label for='email_customer'>Keterangan Reject</label>
                                                        </div>
                                                        <div class='col-md-8' id="">
                                                            <textarea name="" id="" cols="30" rows="5" class="form-control" readonly><?= $results['data_penawaran']->keterangan_app2 ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        <?php
                                        }
                                        if ($results['data_penawaran']->keterangan_app3 !== null && $results['data_penawaran']->keterangan_app3 !== "") {
                                        ?>

                                            <div class="col-md-12">
                                                <div class='col-sm-6'>
                                                    <div class='form-group row'>
                                                        <div class='col-md-4'>
                                                            <label for='email_customer'>Keterangan Reject</label>
                                                        </div>
                                                        <div class='col-md-8' id="">
                                                            <textarea name="" id="" cols="30" rows="5" class="form-control" readonly><?= $results['data_penawaran']->keterangan_app3 ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                    <?php
                                        }
                                    }
                                    ?>

                                    <!-- <div class="col-md-12">
										<div class='col-sm-6'>
											<div class='form-group row'>
												<div class='col-md-4'>
													<label for='email_customer'>PPN/NON PPN</label>
												</div>
												<div class='col-md-8'>
													<select id="ppn_nonppn" name="ppn_nonppn" class="form-control select" required>
														<option value="">--Pilih--</option>
														<option value="1">PPN</option>
														<option value="0">NON PPN</option>
													</select>
												</div>
											</div>
										</div>
										<div class='col-sm-6'>
											<div class='form-group row' id='skb'>
												<div class='col-md-4'>
													<label for='upload_skb'>UPLOAD SKB</label>
												</div>
												<div class='col-md-8'>
													<input type="file" class="form-control" id="upload_skb" required name="upload_skb" readonly placeholder="Upload SKB">
												</div>
											</div>
										</div>
									</div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box box-default ">
                <div class="box-header">
                    <div class="nav-tabs-custom">
                        <div class="box active ">
                            <ul class="nav nav-tabs">
                                <!-- <li class=""><a href="javascript:void(0);" class="add_item_modal">Add Item</a></li>
                                <li class="createunlocated"><a href="javascript:void(0);" data-toggle="tab" id="createunlocated">Request New Product</a></li> -->
                                <!--<li class="lebihbayar"><a href="#" data-toggle="tab" id="lebihbayar">Add Lebih Bayar</a></li>-->
                            </ul>
                        </div>
                        <div id="scroll">
                            <div class="box box-primary" id="data">
                            </div>
                        </div>
                    </div>
                    <!--<div class="box-tools">
			<button class="btn btn-sm btn-success add" id="tambah2" type="button" style="width: 100%;">
				<i class="fa fa-plus"></i> Add Invoice
			</button>
			<button class="btn btn-sm btn-success createunlocated " id="createunlocated" type="button" style="width: 100%;">
				<i class="fa fa-plus"></i> Create Unlocated
			</button>
			<button class="btn btn-sm btn-success lebih " id="lebih" type="button" style="width: 100%;">
				<i class="fa fa-plus"></i> Lebih Bayar
			</button>
		</div>-->
                </div>
                <div class="box-body">
                    <table class="table table-bordered" width="100%" id="tabel-detail-mutasi">
                        <thead>
                            <tr class="bg-blue">
                                <th class="text-center">Product Name</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Price List</th>
                                <th class="text-center">Free Stock</th>
                                <th class="text-center">Discount (%)</th>
                                <th class="text-center">Price Unit After Discount</th>
                                <th class="text-center">Total Price</th>
                                <!-- <th class="text-center">Aksi</th> -->
                            </tr>
                        </thead>
                        <tbody id="list_item_mutasi">
                            <?php
                            $total_all = 0;
                            $total_price_before_discount = 0;
                            $total_nilai_discount = 0;
                            if (isset($results['data_penawaran_detail'])) {
                                foreach ($results['data_penawaran_detail'] as $penawaran_detail) {

                                    $harga_x_qty = ($penawaran_detail->harga_satuan * $penawaran_detail->qty);
                                    $price_after_disc = (($penawaran_detail->harga_satuan) - $penawaran_detail->diskon_nilai);
                                    $total_harga = ($penawaran_detail->total_harga);

                                    $total_price_before_discount += ($harga_x_qty);
                                    $total_all += $penawaran_detail->total_harga;
                                    $total_nilai_discount += ($penawaran_detail->diskon_nilai * $penawaran_detail->qty);

                                    echo '
                                            <tr>
                                                <td>
                                                    <span>' . $penawaran_detail->nama_produk . '</span> <br><br>
                                                    <table class="table">
                                                        <tr>
                                                            <td>Ukuran Potongan</td>
                                                            <td width="2" class="text-center">:</td>
                                                            <td>
                                                                <input type="text" name="ukuran_potong_'.$penawaran_detail->id_penawaran_detail.'" id="" class="form-control form-control-sm ukuran_potong_'.$penawaran_detail->id_penawaran_detail.'" value="'.$penawaran_detail->ukuran_potongan.'" placeholder="- Ukuran Potong -" readonly>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td>
                                                    <input type="number" name="qty_' . $penawaran_detail->id_penawaran_detail . '" value="' . $penawaran_detail->qty . '" class="form-control text-right qty qty_' . $penawaran_detail->id_penawaran_detail . '" onchange="hitung_all(' . $penawaran_detail->id_penawaran_detail . ')" readonly>
                                                </td>
                                                <td class="text-right">(' . $results['curr'] . ') ' . number_format($penawaran_detail->harga_satuan) . '</td>
                                                <td class="text-right">' . number_format($penawaran_detail->stok_tersedia) . '</td>
                                                <td>
                                                    <table class="w-100">
                                                        <tr>
                                                            <td>(%)</td>
                                                            <td>
                                                                <input type="text" name="diskon_persen_' . $penawaran_detail->id_penawaran_detail . '" id="" class="form-control diskon_persen_' . $penawaran_detail->id_penawaran_detail . '" placeholder="Input (%)" value="' . $penawaran_detail->diskon_persen . '%" onchange="hitung_all(' . $penawaran_detail->id_penawaran_detail . ')" readonly>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>(' . $results['curr'] . ')</td>
                                                            <td>
                                                                <input type="text" class="form-control diskon_nilai diskon_nilai_' . $penawaran_detail->id_penawaran_detail . '" name="diskon_nilai_' . $penawaran_detail->id_penawaran_detail . '" id="" value="' . ($penawaran_detail->diskon_nilai) . '" onchange="hitung_all(' . $penawaran_detail->id_penawaran_detail . ')" readonly>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td class="text-right">
                                                (' . $results['curr'] . ') ' . number_format($price_after_disc) . '
                                                </td>
                                                <td class="text-right">
                                                (' . $results['curr'] . ') ' . number_format($total_harga) . '
                                                </td>
                                            </tr>
                                        ';
                                }
                            } else {
                                foreach ($results['list_penawaran_detail'] as $penawaran_detail) {

                                    $harga_x_qty = ($penawaran_detail->harga_satuan * $penawaran_detail->qty);
                                    $price_after_disc = (($penawaran_detail->harga_satuan) - $penawaran_detail->diskon_nilai);
                                    $total_harga = ($penawaran_detail->total_harga);

                                    $total_price_before_discount += ($harga_x_qty);
                                    $total_all += $penawaran_detail->total_harga;
                                    $total_nilai_discount += ($penawaran_detail->diskon_nilai * $penawaran_detail->qty);

                                    echo '
                                    <tr>
                                        <td>
                                            <span>' . $penawaran_detail->nama_produk . '</span> <br><br>
                                            <table class="table">
                                                <tr>
                                                    <td>Ukuran Potongan</td>
                                                    <td width="2" class="text-center">:</td>
                                                    <td>
                                                        <input type="text" name="ukuran_potong_'.$penawaran_detail->id_penawaran_detail.'" id="" class="form-control form-control-sm ukuran_potong_'.$penawaran_detail->id_penawaran_detail.'" value="'.$penawaran_detail->ukuran_potongan.'" placeholder="- Ukuran Potong -" readonly>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td>
                                            <input type="number" name="qty_' . $penawaran_detail->id_penawaran_detail . '" value="' . $penawaran_detail->qty . '" class="form-control text-right qty qty_' . $penawaran_detail->id_penawaran_detail . '" onchange="hitung_all(' . $penawaran_detail->id_penawaran_detail . ')" readonly>
                                        </td>
                                        <td class="text-right">(' . $results['curr'] . ') ' . number_format($penawaran_detail->harga_satuan) . '</td>
                                        <td class="text-right">' . number_format($penawaran_detail->stok_tersedia) . '</td>
                                        <td>
                                            <table class="w-100">
                                                <tr>
                                                    <td>(%)</td>
                                                    <td>
                                                        <input type="text" name="diskon_persen_' . $penawaran_detail->id_penawaran_detail . '" id="" class="form-control diskon_persen_' . $penawaran_detail->id_penawaran_detail . '" placeholder="Input (%)" value="' . $penawaran_detail->diskon_persen . '%" onchange="hitung_all(' . $penawaran_detail->id_penawaran_detail . ')" readonly>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>(' . $results['curr'] . ')</td>
                                                    <td>
                                                        <input type="text" class="form-control diskon_nilai diskon_nilai_' . $penawaran_detail->id_penawaran_detail . '" name="diskon_nilai_' . $penawaran_detail->id_penawaran_detail . '" id="" value="' . ($penawaran_detail->diskon_nilai) . '" onchange="hitung_all(' . $penawaran_detail->id_penawaran_detail . ')" readonly>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td class="text-right">
                                        (' . $results['curr'] . ') ' . number_format($price_after_disc) . '
                                        </td>
                                        <td class="text-right">
                                        (' . $results['curr'] . ') ' . number_format($total_harga) . '
                                        </td>
                                    </tr>
                                ';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-right">
                <div class="box active">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center bg-blue">Information</th>
                                            <th class="text-center bg-blue">Value</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list_other_cost">
                                        <?php
                                        $total_other_cost = 0;
                                        foreach ($results['list_other_cost'] as $other_cost) {
                                            echo '
                                                <tr>
                                                    <td class="text-left">' . $other_cost->keterangan . '</td>
                                                    <td class="text-right">
                                                        <input type="hidden" class="nilai_other_cost" value="' . $other_cost->nilai . '">
                                                        <span>(' . $other_cost->curr . ') ' . number_format($other_cost->nilai) . '</span>
                                                    </td>
                                                   
                                                </tr>
                                            ';

                                            $total_other_cost += $other_cost->nilai;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group " style="padding-top:15px;">
                                    <label class="col-sm-4 control-label">Total price before discount (<?= $results['curr'] ?>)</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="total_price_before_discount" class="form-control input-sm text-right total_price_before_discount" id="" value="<?= number_format($total_price_before_discount) ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6"></div>
                            <div class="col-lg-6">
                                <div class="form-group " style="padding-top:15px;">
                                    <label class="col-sm-4 control-label">Discount (<?= $results['curr'] ?>)</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="ttl_discount" class="form-control input-sm text-right ttl_discount" id="" value="<?= number_format($total_nilai_discount) ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6"></div>
                            <div class="col-lg-6">
                                <div class="form-group " style="padding-top:15px;">
                                    <label class="col-sm-4 control-label">% Discount</label>
                                    <div class="col-sm-6">
                                        <?php 
                                            $persen_disc = 0;
                                            if($total_price_before_discount > 0){
                                                $persen_disc = (($total_price_before_discount - $total_all) / $total_price_before_discount * 100);
                                            }
                                        ?>
                                        <input type="text" name="ttl_persen_discount" class="form-control input-sm text-right ttl_persen_discount" id="" value="<?= number_format($persen_disc, 2) ?>%" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6"></div>
                            <div class="col-lg-6">
                                <div class="form-group " style="padding-top:15px;">
                                    <label class="col-sm-4 control-label">Total price after discount (<?= $results['curr'] ?>)</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="total" class="form-control input-sm text-right" id="total" value="<?= number_format($total_all) ?>" readonly tabindex="-1" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6"></div>
                            <div class="col-lg-6">
                                <div class="form-group " style="padding-top:15px;">
                                    <label class="col-sm-4 control-label">Total Other Cost (<?= $results['curr'] ?>)</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="total_other_cost" class="form-control input-sm text-right total_other_cost" id="" value="<?= number_format($total_other_cost) ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6"></div>
                            <div class="col-lg-6">
                                <div class="form-group " style="padding-top:15px;">
                                    <?php
                                    $total_all = ($total_all + $total_other_cost);
                                    $grand_total = $total_all;
                                    if (isset($results['data_penawaran'])) {
                                        $grand_total = ($total_all + ($total_all * $results['data_penawaran']->ppn / 100));
                                    } else {
                                        $grand_total = ($total_all + ($total_all * 11 / 100));
                                    }
                                    ?>
                                    <label class="col-sm-4 control-label">PPN</label>
                                    <div class="col-sm-6 text-center">
                                        <div class="form-group">
                                            <span style="padding-right: 40px;">
                                                <input type="radio" name="ppn_check" id="" class="ppn_check" value="11" <?= (!isset($results['data_penawaran']) || (isset($results['data_penawran']) && $results['data_penawaran']->ppn == '11')) ? 'checked' : 'checked' ?> disabled> Yes
                                            </span>
                                            <span>
                                                <input type="radio" name="ppn_check" id="" class="ppn_check ml-5" value="0" <?= (isset($results['data_penawaran']) && $results['data_penawaran']->ppn == '0') ? 'checked' : null ?> disabled> No
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6"></div>
                            <div class="col-lg-6">
                                <div class="form-group " style="padding-top:15px;">
                                    <label class="col-sm-4 control-label">Grand Total (<?= $results['curr'] ?>)</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="grand_total" class="form-control input-sm text-right grand_total" id="" value="<?= number_format($grand_total) ?>" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-lg-12">
                                <a href="<?= base_url() ?>quotation" class="btn btn-danger">
                                    <i class="fa fa-refresh"></i><b> Back</b>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal modal-primary" id="dialog-data-stok" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Data Invoice</h4>
                        </div>
                        <div class="modal-body" id="MyModalBodyStok" style="background: #FFF !important;color:#000 !important;">
                            <table class="table table-bordered" width="100%" id="list_item_stok">
                                <thead>
                                    <tr>
                                        <th width="30%">No Invoice</th>
                                        <th width="30%">Nama Customer</th>
                                        <th width="30%">Total Invoice</th>
                                        <th width="30%">Sisa Invoice</th>
                                        <th width="2%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $cust = $inv->id_customer;
                                    $invoice = $this->db->query("SELECT
					(@row:=@row+1) AS nomor,
					a.*,
					b.nama AS nama_level4,
					d.variant_product,
					c.nama AS nama_level1
				FROM
					product_price a 
					LEFT JOIN new_inventory_4 b ON a.code_lv4=b.code_lv4
					LEFT JOIN new_inventory_1 c ON b.code_lv1=c.code_lv1
					LEFT JOIN bom_header d ON a.no_bom=d.no_bom,
					(SELECT @row:=0) r
				WHERE 1=1 AND a.deleted_date IS NULL")->result();
                                    if ($invoice) {
                                        foreach ($invoice as $ks => $vs) {
                                    ?>
                                            <tr>
                                                <td><?php echo $vs->no_invoice ?></td>
                                                <td>
                                                    <center><?php echo $vs->nm_customer ?></center>
                                                </td>
                                                <td>
                                                    <center><?php echo number_format($vs->grand_total) ?></center>
                                                </td>
                                                <td>
                                                    <center><?php echo number_format($vs->sisa_invoice_idr) ?></center>
                                                </td>
                                                <td>
                                                    <center>
                                                        <button id="btn-<?php echo $vs->no_invoice ?>" class="btn btn-warning btn-sm" type="button" onclick="startmutasi('<?php echo $vs->no_invoice ?>','<?php echo $vs->nm_customer ?>','<?php echo $vs->total_invoice_idr ?>','<?php echo $vs->sisa_invoice_idr ?>')">
                                                            Pilih
                                                        </button>
                                                    </center>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                <span class="glyphicon glyphicon-remove"></span> Tutup</button>
                        </div>
                    </div>
                </div>
            </div>



            <div class="modal modal-primary" id="dialog-data-lebihbayar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Data Lebih Bayar</h4>
                        </div>
                        <div class="modal-body" id="MyModalBodyLebihbayar" style="background: #FFF !important;color:#000 !important;">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                <span class="glyphicon glyphicon-remove"></span> Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Tutup</span></button>
                            <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;List Invoice</h4>
                        </div>
                        <div class="modal-body" id="ModalView">
                            ...
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">
                                <span class="glyphicon glyphicon-remove"></span> Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal modal-default fade" id="sales_product_price_list_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Tutup</span></button>
                            <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;List Invoice</h4>
                        </div>
                        <div class="modal-body" id="ModalViewSPPLM">
                            ...
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">
                                <span class="glyphicon glyphicon-remove"></span> Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
            <script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
            <script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
            <script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
            <script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
            <script>
                $(document).ready(function() {
                    $('.select2').select2();
                    swal.close();
                    $('#incomplete').hide();
                    $('#pakailebihbayar').hide();
                    $("#list_item_unlocated").DataTable({
                        lengthMenu: [10, 155, 30]
                    }).draw();
                    $(".divide").divide();

                    $('.nilai_ppn').autoNumeric('init', {
                        aSep: ',',
                        aDec: '.',
                        mDec: '0'
                    });
                    $('.diskon_nilai').autoNumeric('init', {
                        aSep: ',',
                        aDec: '.',
                        mDec: '0'
                    });
                    $('.qty').autoNumeric('init', {
                        aSep: ',',
                        aDec: '.',
                        mDec: '0'
                    });
                });


                var no_surat = $('.no_surat').val();

                function savemutasi() {

                    // if ($('#tgl_bayar').val() == "") {
                    // 	swal({
                    // 		title: "TANGGAL BAYAR TIDAK BOLEH KOSONG!",
                    // 		text: "ISI TANGGAL INVOICE!",
                    // 		type: "warning",
                    // 		timer: 3000,
                    // 		showCancelButton: false,
                    // 		showConfirmButton: false,
                    // 		allowOutsideClick: false
                    // 	});
                    // } else if ($('#control').val() != "0") {
                    // 	swal({
                    // 		title: "Perhatian",
                    // 		text: "Kontrol harus 0!",
                    // 		type: "warning",
                    // 		timer: 3000,
                    // 		showCancelButton: false,
                    // 		showConfirmButton: false,
                    // 		allowOutsideClick: false
                    // 	});
                    // } else if ($('#bank').val() == "") {
                    // 	swal({
                    // 		title: "BANK TIDAK BOLEH KOSONG!",
                    // 		text: "ISI TANGGAL INVOICE!",
                    // 		type: "warning",
                    // 		timer: 3000,
                    // 		showCancelButton: false,
                    // 		showConfirmButton: false,
                    // 		allowOutsideClick: false
                    // 	});
                    // } else if ($('#total_bank').val() != $('#total_terima').val()) {
                    // 	swal({
                    // 		title: "JUMLAH BAYAR DAN PENERIMAAN BANK TIDAK SAMA!",
                    // 		text: "SILAHKAN PERBAIKI DATA ANDA!",
                    // 		type: "warning",
                    // 		timer: 3000,
                    // 		showCancelButton: false,
                    // 		showConfirmButton: false,
                    // 		allowOutsideClick: false
                    // 	});
                    // } else {

                    // }

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
                        function(isConfirm) {
                            if (isConfirm) {
                                $('#simpanpenerimaan').hide();
                                var formdata = $("#form-header-mutasi").serialize();
                                $.ajax({
                                    url: siteurl + active_controller + "save_penerimaan",
                                    dataType: "json",
                                    type: 'POST',
                                    data: formdata,
                                    success: function(data) {
                                        if (data.status == 1) {
                                            swal({
                                                title: "Save Success!",
                                                text: data.pesan,
                                                type: "success",
                                                timer: 15000,
                                                showCancelButton: false,
                                                showConfirmButton: false,
                                                allowOutsideClick: false
                                            });
                                            window.location.href = base_url + active_controller + 'modal_detail_invoice/' + no_surat;
                                        } else {

                                            if (data.status == 2) {
                                                swal({
                                                    title: "Save Failed!",
                                                    text: data.pesan,
                                                    type: "warning",
                                                    timer: 10000,
                                                    showCancelButton: false,
                                                    showConfirmButton: false,
                                                    allowOutsideClick: false
                                                });
                                            } else {
                                                swal({
                                                    title: "Save Failed!",
                                                    text: data.pesan,
                                                    type: "warning",
                                                    timer: 10000,
                                                    showCancelButton: false,
                                                    showConfirmButton: false,
                                                    allowOutsideClick: false
                                                });
                                            }

                                        }
                                    },
                                    error: function() {
                                        swal({
                                            title: "Gagal!",
                                            text: "Batal Proses, Data bisa diproses nanti",
                                            type: "error",
                                            timer: 1500,
                                            showConfirmButton: false
                                        });
                                    }
                                });
                            }
                        });
                }

                function kembali_inv() {

                    window.location.href = base_url + active_controller;
                }

                function cekall() {
                    var total_bank = $("#total_bank").val();
                    var total_invoice = $("#total_invoice").val();
                    var selisih = (parseFloat(total_bank) - parseFloat(total_invoice));
                    $("#selisih").val(selisih);
                    var biaya_adm = $("#biaya_adm").val();
                    var biaya_pph = $("#biaya_pph").val();
                    var tambah_lebih_bayar = $("#tambah_lebih_bayar").val();
                    var control = (parseFloat(selisih) + parseFloat(biaya_adm) + parseFloat(biaya_pph) - parseFloat(tambah_lebih_bayar));
                    $("#control").val(control);
                    var total_terima = (parseFloat(total_invoice) - parseFloat(biaya_adm) - parseFloat(biaya_pph) + parseFloat(tambah_lebih_bayar));
                    $("#total_terima").val(total_terima);
                }
                // $(document).on('blur', '#total_bank', function(){
                // var dataTotal	  = $(this).val().split(",").join("");
                // var adm			  = parseFloat($('#biaya_adm').val().split(",").join(""));
                // var pph			  =	parseFloat($('#biaya_pph').val().split(",").join(""));
                // var totalBank     = parseFloat(dataTotal).toFixed(0);
                // var Total         = parseFloat(dataTotal-adm-pph).toFixed(0);
                // $('#total_bank').val(num2(totalBank));
                // $('#total_terima').val(num2(Total));
                // });

                /*
                	$(document).on('keyup', '#biaya_adm, #total_bank, #biaya_pph, #tambah_lebih_bayar', function(){
                		var pakai_lebih_bayar   = parseFloat($('#pakai_lebih_bayar').val().split(",").join(""))
                		var tambah_lebih_bayar   = parseFloat($('#tambah_lebih_bayar').val().split(",").join(""))
                	    var biaya_adm   = parseFloat($('#biaya_adm').val().split(",").join(""))
                		var total_bank	= parseFloat($('#total_bank').val().split(",").join(""));
                        var biaya_pph	= parseFloat($('#biaya_pph').val().split(",").join(""));
                		var Total       = parseInt(biaya_adm)+parseInt(total_bank)+parseInt(biaya_pph)+parseInt(pakai_lebih_bayar)-parseInt(tambah_lebih_bayar);
                		$('#total_terima').val(number_format(Total));
                	});
                */
                // $(document).on('blur', '#biaya_pph', function(){
                // var dataTotal	  = $(this).val().split(",").join("");
                // var bank		    = $('#total_bank').val().split(",").join("");
                // var adm			    =	$('#biaya_adm').val().split(",").join("");

                // var totalBank     = parseFloat(dataTotal).toFixed(0);
                // var Total         = parseInt(bank)+parseInt(dataTotal)+parseInt(adm).toFixed(0);
                // $('#biaya_pph').val(num2(totalBank));
                // $('#total_terima').val(num2(Total));
                // });

                // $(document).on('blur', '#total_terima', function(){

                // var dataTotal	  = $(this).val().split(",").join("");
                // var totalBank     = parseFloat(dataTotal).toFixed(0);
                // $('#total_terima').val(num2(totalBank));
                // });

                $("#tambah").click(function() {
                    $('#dialog-data-stok').modal('show');
                    //        $("#list_item_unlocated").DataTable({lengthMenu:[10,15,25,30]}).draw();
                });

                function startmutasi(id, surat, nm, avl, real) {
                    var avl2 = numx(avl);
                    var real2 = numx(real);


                    //  Cek Ada Data Gagal
                    var Cek_OK = 1;
                    var Urut = 1;
                    var total_row = $('#list_item_mutasi').find('tr').length;
                    if (total_row > 0) {
                        var kode_tr_akhir = $('#list_item_mutasi tr:last').attr('id');
                        var row_akhir = kode_tr_akhir.split('_');
                        var Urut = parseInt(row_akhir[1]) + 1;
                        $('#list_item_mutasi').find('tr').each(function() {
                            var kode_row = $(this).attr('id');
                            var id_row = kode_row.split('_');
                            var kode_produknya = $('#kode_produk_' + id_row[1]).val();
                            if (id == kode_produknya) {
                                Cek_OK = 0;
                            }
                        });
                    }
                    if (Cek_OK == 1) {
                        var idnya = "'" + id + "'";
                        html = '<tr id="tr_' + Urut + '">' +
                            '<td style="padding:3px;">' +
                            '<input type="text" class="form-control input-sm kode-produk" name="kode_produk[]" id="kode_produk_' + Urut + '" readonly value="' + id + '">' +
                            '</td>' +
                            '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="no_surat[]" id="no_surat' + Urut + '" readonly value="' + surat + '"></td>' +
                            '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="nm_customer2[]" id="nm_customer2' + Urut + '" readonly value="' + nm + '"></td>' +
                            '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="jml_invoice[]" id="jml_invoice' + Urut + '" style="text-align:center;" readonly value="' + avl2 + '"></td>' +
                            '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="sisa_invoice[]" id="sisa_invoice' + Urut + '" style="text-align:center;" readonly value="' + real2 + '"></td>' +
                            '<td style="padding:3px;"><input type="text" class="form-control input-sm sum_change_bayar divide" name="jml_bayar[]" id="jml_bayar' + Urut + '" style="text-align:right;" value="' + number_format(real) + '" onchange="cekall()" ></td>' +
                            '<td style="padding:3px;"><input type="text" class="form-control input-sm sum_change_pph  hidden" name="pph[]" id="pph' + Urut + '" style="text-align:right;" value="0" data-decimal="." data-thousand="" data-precision="0" data-allow-zero=""></td>' +
                            '<td style="padding:3px;"><center><div class="btn-group" style="margin:0px;">' +
                            '<button type="button" onclick="deleterow(' + Urut + ',' + idnya + ')" id="delete-row" class="btn btn-sm btn-danger delete_bayar"><i class="fa fa-trash"></i> Hapus</button>' +
                            '</div></center></td>' +
                            '</tr>';
                        $("#tabel-detail-mutasi").append(html);
                        $("#btn-" + id).removeClass('btn-warning');
                        $("#btn-" + id).addClass('btn-danger');
                        $("#btn-" + id).attr('disabled', true);
                        $("#btn-" + id).text('Sudah');
                        sumchangebayar();
                    }
                }

                function deleterow(tr, id) {
                    $('#tr_' + tr).remove();
                    $("#btn-" + id).removeClass('btn-danger');
                    $("#btn-" + id).addClass('btn-warning');
                    $("#btn-" + id).attr('disabled', false);
                    $("#btn-" + id).text('Pilih');
                    sumchangebayar();
                }

                //ARWANT
                $(document).on('keyup', '.sum_change_bayar', function() {
                    var jumlah_bayar = 0;
                    $(".sum_change_bayar").each(function() {
                        jumlah_bayar += getNum($(this).val().split(",").join(""));
                    });
                    $('#total_invoice').val(number_format(jumlah_bayar));
                });

                //SYAM
                $(document).on('keyup', '.sum_change_pph', function() {
                    var jumlah_bayar = 0;
                    $(".sum_change_pph").each(function() {
                        jumlah_bayar += getNum($(this).val().split(",").join(""));
                    });
                    $('#biaya_pph').val(number_format(jumlah_bayar));
                    //totalterima();
                });

                function sumchangebayar() {
                    var jumlah_bayar = 0;
                    $(".sum_change_bayar").each(function() {
                        jumlah_bayar += getNum($(this).val().split(",").join(""));
                    });
                    $('#total_invoice').val(number_format(jumlah_bayar));
                }

                function getNum(val) {
                    if (isNaN(val) || val == '') {
                        return 0;
                    }
                    return parseFloat(val);
                }

                function num(n) {
                    return (n).toFixed(0).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                }

                function num2(n) {
                    return (n).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                }

                function num3(n) {
                    return (n).toFixed(0);
                }

                function numx(n) {
                    return (n).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                }

                function number_format(number, decimals, dec_point, thousands_sep) {
                    // Strip all characters but numerical ones.
                    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
                    var n = !isFinite(+number) ? 0 : +number,
                        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                        s = '',
                        toFixedFix = function(n, prec) {
                            var k = Math.pow(10, prec);
                            return '' + Math.round(n * k) / k;
                        };
                    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
                    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
                    if (s[0].length > 3) {
                        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
                    }
                    if ((s[1] || '').length < prec) {
                        s[1] = s[1] || '';
                        s[1] += new Array(prec - s[1].length + 1).join('0');
                    }
                    return s.join(dec);
                }

                $(document).on('change', '#bank', function() {
                    var dataCoa = $(this).val();
                    if (dataCoa == '2101-07-01') {
                        $('#incomplete').show();
                    } else {
                        $('#incomplete').hide();
                    }
                });

                $("#incomplete").click(function() {
                    $('#dialog-data-incomplete').modal('show');
                    //        $("#list_item_stok").DataTable({lengthMenu:[10,15,25,30]}).draw();
                });

                $("#lebihbayar-1").click(function() {
                    $('#dialog-data-lebihbayar').modal('show');
                    $('#pakailebihbayar').show();
                    //        $("#list_item_stok").DataTable({lengthMenu:[10,15,25,30]}).draw();
                });

                function startunlocated(id, value) {

                    $("#total_bank").val(value);
                    $("#id_unlocated").val(id);
                    $("#btn-" + id).removeClass('btn-warning');
                    $("#btn-" + id).addClass('btn-danger');
                    $("#btn-" + id).attr('disabled', true);
                    $("#btn-" + id).text('Sudah');
                    var totalBank = parseFloat(value).toFixed(0);
                    $('#total_bank').val(number_format(totalBank));
                    //		totalterima();
                    cekall();
                }

                function startlebihbayar(id, value) {

                    $("#pakai_lebih_bayar").val(value);
                    $("#id_lebihbayar").val(id);
                    $("#btn-" + id).removeClass('btn-warning');
                    $("#btn-" + id).addClass('btn-danger');
                    $("#btn-" + id).attr('disabled', true);
                    $("#btn-" + id).text('Sudah');
                    var totalBank = parseFloat(value).toFixed(0);
                    $('#pakai_lebih_bayar').val(number_format(totalBank));
                    //		totalterima();
                    cekall();
                }

                function add_product_price(id) {
                    var no_surat_product_list = $('.no_surat').val();
                    $.ajax({
                        type: 'post',
                        url: siteurl + active_controller + 'add_product_price',
                        data: {
                            'id': id,
                            'no_surat_product_list': no_surat_product_list
                        },
                        cache: false,
                        success: function(result) {
                            // $('.select_product_price_' + id).html('<i class="fa fa-plus"></i> Select');
                            $('.select_product_price_' + id).attr('disabled', true);

                            cek_detail_penawaran(no_surat_product_list);
                        }
                    });
                }

                function hitung_total() {
                    var persen_ppn = $('.persen_ppn').val();
                    persen_ppn = persen_ppn.split(',').join('');

                    var nilai_ppn = $('.nilai_ppn').val();
                    nilai_ppn = nilai_ppn.split(',').join('');

                    $.ajax({
                        type: 'post',
                        url: siteurl + active_controller + 'hitung_total',
                        data: {
                            'id': no_surat,
                            'persen_ppn': persen_ppn,
                            'nilai_ppn': nilai_ppn
                        },
                        cache: false,
                        success: function(result) {
                            $('.grand_total').val(number_format(result));
                        }
                    });
                }

                function cek_detail_penawaran(id) {
                    // var id = '';
                    var persen_ppn = $('.persen_ppn').val();
                    persen_ppn = persen_ppn.split(',').join('');
                    persen_ppn = parseFloat(persen_ppn);

                    var nilai_ppn = $('.nilai_ppn').val();
                    nilai_ppn = nilai_ppn.split(',').join('');
                    nilai_ppn = parseFloat(nilai_ppn);

                    $.ajax({
                        type: 'post',
                        url: siteurl + active_controller + 'cek_detail_penawaran',
                        data: {
                            'id': id,
                            'no_surat': no_surat,
                            'persen_ppn': persen_ppn,
                            'nilai_ppn': nilai_ppn
                        },
                        cache: false,
                        dataType: 'JSON',
                        success: function(result) {
                            $('#list_item_mutasi').html(result.hasil);


                            $('.total_price_before_discount').val(number_format(result.total_price_before_discount));
                            $('.ttl_discount').val(number_format(result.total_nilai_discount));
                            $('.ttl_persen_discount').val(number_format(result.ttl_persen_discount, 2) + '%');
                            $('#total').val(number_format(result.total));
                            $('.nilai_ppn').val(number_format(result.nilai_ppn));
                            $('.grand_total').val(number_format(result.grand_total));

                            $('.nilai_ppn').autoNumeric('destroy');
                            $('.diskon_nilai').autoNumeric('destroy');
                            $('.qty').autoNumeric('destroy');

                            $('.nilai_ppn').autoNumeric('init', {
                                aSep: ',',
                                aDec: '.',
                                mDec: '0'
                            });
                            $('.diskon_nilai').autoNumeric('init', {
                                aSep: ',',
                                aDec: '.',
                                mDec: '0'
                            });
                            $('.qty').autoNumeric('init', {
                                aSep: ',',
                                aDec: '.',
                                mDec: '0'
                            });
                        }
                    });
                }

                function hitung_all(id) {

                    var qty_penawaran = $('.qty_' + id).val();
                    qty_penawaran = qty_penawaran.split(',').join('');
                    qty_penawaran = parseFloat(qty_penawaran);

                    var diskon_persen = $('.diskon_persen_' + id).val();
                    diskon_persen = diskon_persen.split(',').join('');
                    diskon_persen = parseFloat(diskon_persen);

                    var diskon_nilai = $('.diskon_nilai_' + id).val();
                    diskon_nilai = diskon_nilai.split(',').join('');
                    diskon_nilai = parseFloat(diskon_nilai);


                    $.ajax({
                        type: 'post',
                        url: siteurl + active_controller + 'hitung_all',
                        data: {
                            'id': id,
                            'no_surat': no_surat,
                            'qty': qty_penawaran,
                            'diskon_persen': diskon_persen,
                            'diskon_nilai': diskon_nilai
                        },
                        cache: false,
                        success: function(result) {
                            cek_detail_penawaran(no_surat);
                        }
                    });
                }

                function del_product_price(id) {
                    $.ajax({
                        type: 'post',
                        url: siteurl + active_controller + 'del_product_price',
                        data: {
                            'id': id
                        },
                        cache: false,
                        success: function(result) {
                            cek_detail_penawaran(no_surat);
                        }
                    });
                }

                $(document).on('click', '.add', function() {
                    var id_customer = $("#customer").val();

                    if (id_customer == "") {
                        swal({
                            title: "CUSTOMER TIDAK BOLEH KOSONG!",
                            text: "ISI CUSTOMER INVOICE!",
                            type: "warning",
                            timer: 3000,
                            showCancelButton: false,
                            showConfirmButton: false,
                            allowOutsideClick: false
                        });
                    } else {

                        $("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Invoice</b>");
                        $.ajax({
                            type: 'POST',
                            url: siteurl + 'penerimaan/TambahInvoice/' + id_customer,
                            data: {
                                'id_customer': id_customer
                            },
                            success: function(data) {
                                $("#dialog-popup").modal();
                                $("#ModalView").html(data);
                            }
                        })
                    }
                });

                $(document).on('change', '.persen_ppn', function() {
                    var ppn_persen = $('.persen_ppn').val();

                    $.ajax({
                        type: 'post',
                        url: siteurl + active_controller + 'ubah_persen_ppn',
                        data: {
                            'id': no_surat,
                            'ppn_persen': ppn_persen
                        },
                        cache: false,
                        dataType: 'json',
                        success: function(result) {
                            $('.nilai_ppn').val(number_format(result.hasil));
                            $('.persen_ppn').val(number_format(ppn_persen));

                            hitung_total();
                        }
                    });
                });

                $(document).on('change', '.nilai_ppn', function() {
                    var nilai_ppn = $('.nilai_ppn').val();
                    nilai_ppn = nilai_ppn.split(',').join('');
                    nilai_ppn = parseFloat(nilai_ppn);

                    $.ajax({
                        type: 'post',
                        url: siteurl + active_controller + 'ubah_nilai_ppn',
                        data: {
                            'id': no_surat,
                            'nilai_ppn': nilai_ppn
                        },
                        cache: false,
                        dataType: 'json',
                        success: function(result) {
                            // alert(result.hasil);
                            $('.persen_ppn').val(number_format(Math.round(result.hasil)));
                            $('.nilai_ppn').val(number_format(nilai_ppn));

                            hitung_total();
                        }
                    });
                });

                $(document).on('click', '#lebihbayar', function() {
                    // $('#dialog-data-lebihbayar').modal('show');
                    $('#pakailebihbayar').show();
                    var id_customer = $("#customer").val();
                    $("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Lebih Bayar</b>");
                    $.ajax({
                        type: 'POST',
                        url: siteurl + 'penerimaan/TambahLebihBayar/' + id_customer,
                        data: {
                            'id_customer': id_customer
                        },
                        success: function(data) {
                            $("#dialog-data-lebihbayar").modal();
                            $("#MyModalBodyLebihbayar").html(data);
                        }
                    })
                });

                $(document).on('click', '.lebih', function() {
                    var id_customer = $("#customer").val();
                    $("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Lebih Bayar</b>");
                    $.ajax({
                        type: 'POST',
                        url: siteurl + 'penerimaan/lebihbayar',
                        data: {
                            'id_customer': id_customer
                        },
                        success: function(data) {
                            $("#dialog-popup").modal();
                            $("#ModalView").html(data);
                        }
                    })
                });

                $(document).on('change', '#customer', function() {
                    var id_customer = $("#customer").val();
                    $("#id_customer").val(id_customer);
                });

                function totalterima() {
                    cekall();
                    /*
		var pakai_lebih_bayar   = parseFloat($('#pakai_lebih_bayar').val().split(",").join(""))
		var tambah_lebih_bayar   = parseFloat($('#tambah_lebih_bayar').val().split(",").join(""))
	    var biaya_adm   = parseFloat($('#biaya_adm').val().split(",").join(""))
		var total_bank	= parseFloat($('#total_bank').val().split(",").join(""));
        var biaya_pph	= parseFloat($('#biaya_pph').val().split(",").join(""));
		var Total       = parseInt(biaya_adm)+parseInt(total_bank)+parseInt(biaya_pph)+parseInt(pakai_lebih_bayar)-parseInt(tambah_lebih_bayar);
		$('#total_terima').val(number_format(Total));
		*/
                }

                $(document).on('click', '.createunlocated', function() {
                    var id_customer = $("#customer").val();
                    $("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Unlocated</b>");
                    $.ajax({
                        type: 'POST',
                        url: siteurl + 'penerimaan/createunlocated',
                        data: {
                            'id_customer': id_customer
                        },
                        success: function(data) {
                            $("#dialog-popup").modal();
                            $("#ModalView").html(data);
                        }
                    })
                });

                $(document).on('click', '.add_item_modal', function() {
                    var no_surat = $('#no_surat').val();

                    $.ajax({
                        type: 'post',
                        url: siteurl + 'quotation/add_item_modal',
                        data: {
                            'no_surat': no_surat
                        },
                        cache: false,
                        success: function(result) {
                            $('#ModalViewSPPLM').html(result);
                            $('#sales_product_price_list_modal').modal('show');
                        }
                    });
                });

                $(document).on('change', '.get_data_customer', function() {
                    var id_customer = $(this).val();

                    $.ajax({
                        type: 'post',
                        url: siteurl + active_controller + 'get_data_customer',
                        data: {
                            'id_customer': id_customer
                        },
                        cache: false,
                        dataType: 'json',
                        success: function(result) {
                            $('#pic_customer').html(result.list_pic);
                            $('#email_customer').val(result.email_pic);
                        }
                    });
                });

                // $('#tgl_bayar').datepicker({
                // format: 'yyyy-mm-dd',
                // todayHighlight: true
                // });
            </script>