<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2017-12-28 10:14:12 --> Query error: Unknown column 'no_invoicex' in 'where clause' - Invalid query: SELECT
          mitra.nama_mitra,
          log_invoice.no_invoice,
          log_invoice.tgl_invoice,
          log_invoice.tgl_tempo,
          log_invoice.nilai_invoice,
          log_invoice.bayar,
          log_invoice.kurang_bayar,
          log_invoice.sts_inv,
          mitra.norekeningbank,
          mitra.namabank,
          log_invoice.v_no,
          log_invoice.log_no,
          kbm.nopolisi,
          log_invoice.periode_start,
          log_invoice.periode_end,
          log_invoice.kartu_kredit,
          log_invoice.tunai,
          log_invoice.total_bayar_mitra,
          log_invoice.bank_charge,
          log_invoice.biaya_sewa,
          log_invoice.management_fee
          FROM
          log_invoice
          INNER JOIN mitra ON log_invoice.mitra_id = mitra.mitra_id
          INNER JOIN kbm ON log_invoice.v_no = kbm.v_no
          WHERE no_invoicex='INV-UBI2017120003'
ERROR - 2017-12-28 10:31:21 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 45
ERROR - 2017-12-28 07:33:15 --> Severity: Parsing Error --> syntax error, unexpected ';' C:\xampp\htdocs\project\dms\application\modules\penagihan\controllers\Penagihan.php 303
ERROR - 2017-12-28 13:47:58 --> Severity: Error --> Call to a member function result() on array C:\xampp\htdocs\project\dms\application\modules\penagihan\views\print_data.php 28
ERROR - 2017-12-28 13:48:08 --> Severity: Error --> Call to a member function result() on array C:\xampp\htdocs\project\dms\application\modules\penagihan\views\print_data.php 28
ERROR - 2017-12-28 13:57:52 --> Severity: Parsing Error --> syntax error, unexpected '<' C:\xampp\htdocs\project\dms\application\modules\penagihan\views\list.php 71
ERROR - 2017-12-28 13:58:16 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 45
