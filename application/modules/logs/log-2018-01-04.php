<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2018-01-04 03:07:37 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\project\dms\application\modules\users\views\login.php 5
ERROR - 2018-01-04 03:07:39 --> 404 Page Not Found: /index
ERROR - 2018-01-04 03:07:39 --> 404 Page Not Found: /index
ERROR - 2018-01-04 03:07:39 --> 404 Page Not Found: /index
ERROR - 2018-01-04 09:07:56 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 45
ERROR - 2018-01-04 09:17:31 --> Query error: Unknown column 'undefined' in 'where clause' - Invalid query: SELECT
                log_penggunaan_kbm.v_no,
                log_penggunaan_kbm.log_no             
                FROM
                mitra
                INNER JOIN log_penggunaan_kbm ON log_penggunaan_kbm.mitra_id = mitra.mitra_id
                WHERE
                    status_aktif = '1' AND log_penggunaan_kbm.status='on' and mitra.mitra_id=undefined
ERROR - 2018-01-04 03:27:52 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\project\dms\application\modules\users\views\login.php 5
ERROR - 2018-01-04 03:27:53 --> 404 Page Not Found: /index
ERROR - 2018-01-04 03:27:53 --> 404 Page Not Found: /index
ERROR - 2018-01-04 03:27:53 --> 404 Page Not Found: /index
ERROR - 2018-01-04 10:25:08 --> Query error: Table 'dms.log_pembayaran' doesn't exist - Invalid query: SELECT `log_pembayaran`.`no_bayar`, `log_pembayaran`.`tgl_bayar`, `log_pembayaran`.`nilai_bayar`, `log_pembayaran`.`kurang_bayar`, `log_pembayaran`.`no_invoice`, `log_pembayaran`.`v_no`, `mitra`.`nama_mitra`
FROM `log_pembayaran`
JOIN `log_invoice` ON `log_pembayaran`.`no_invoice` = `log_invoice`.`no_invoice`
JOIN `mitra` ON `log_pembayaran`.`mitra_id` = `mitra`.`mitra_id`
ERROR - 2018-01-04 10:31:36 --> Query error: Table 'dms.log_pembayaran' doesn't exist - Invalid query: SELECT `log_pembayaran_ht`.`no_bayar`, `log_pembayaran_ht`.`tgl_bayar`, `log_pembayaran_ht`.`total_bayar`, `log_pembayaran_ht`.`keterangan`, `log_pembayaran_dt`.`mitra_id`, `mitra`.`nama_mitra`
FROM `log_pembayaran`
JOIN `log_pembayaran_dt` ON `log_pembayaran_ht`.`no_bayar` = `log_pembayaran_dt`.`no_bayar`
JOIN `mitra` ON `log_pembayaran_dt`.`mitra_id` = `mitra`.`mitra_id`
ERROR - 2018-01-04 10:32:37 --> Query error: Table 'dms.log_pembayaran' doesn't exist - Invalid query: SELECT `log_pembayaran_ht`.`no_bayar`, `log_pembayaran_ht`.`tgl_bayar`, `log_pembayaran_ht`.`total_bayar`, `log_pembayaran_ht`.`keterangan`, `log_pembayaran_dt`.`mitra_id`, `mitra`.`nama_mitra`
FROM `log_pembayaran`
JOIN `log_pembayaran_dt` ON `log_pembayaran_ht`.`no_bayar` = `log_pembayaran_dt`.`no_bayar`
JOIN `mitra` ON `log_pembayaran_dt`.`mitra_id` = `mitra`.`mitra_id`
ERROR - 2018-01-04 10:32:38 --> Query error: Table 'dms.log_pembayaran' doesn't exist - Invalid query: SELECT `log_pembayaran_ht`.`no_bayar`, `log_pembayaran_ht`.`tgl_bayar`, `log_pembayaran_ht`.`total_bayar`, `log_pembayaran_ht`.`keterangan`, `log_pembayaran_dt`.`mitra_id`, `mitra`.`nama_mitra`
FROM `log_pembayaran`
JOIN `log_pembayaran_dt` ON `log_pembayaran_ht`.`no_bayar` = `log_pembayaran_dt`.`no_bayar`
JOIN `mitra` ON `log_pembayaran_dt`.`mitra_id` = `mitra`.`mitra_id`
ERROR - 2018-01-04 10:32:51 --> Query error: Table 'dms.log_pembayaran' doesn't exist - Invalid query: SELECT `log_pembayaran_ht`.`no_bayar`, `log_pembayaran_ht`.`tgl_bayar`, `log_pembayaran_ht`.`total_bayar`, `log_pembayaran_ht`.`keterangan`, `log_pembayaran_dt`.`mitra_id`, `mitra`.`nama_mitra`
FROM `log_pembayaran`
JOIN `log_pembayaran_dt` ON `log_pembayaran_ht`.`no_bayar` = `log_pembayaran_dt`.`no_bayar`
JOIN `mitra` ON `log_pembayaran_dt`.`mitra_id` = `mitra`.`mitra_id`
ERROR - 2018-01-04 10:33:12 --> Query error: Table 'dms.log_pembayaran' doesn't exist - Invalid query: SELECT `log_pembayaran_ht`.`no_bayar`, `log_pembayaran_ht`.`tgl_bayar`, `log_pembayaran_ht`.`total_bayar`, `log_pembayaran_ht`.`keterangan`, `log_pembayaran_dt`.`mitra_id`, `mitra`.`nama_mitra`
FROM `log_pembayaran`
JOIN `log_pembayaran_dt` ON `log_pembayaran_ht`.`no_bayar` = `log_pembayaran_dt`.`no_bayar`
JOIN `mitra` ON `log_pembayaran_dt`.`mitra_id` = `mitra`.`mitra_id`
ERROR - 2018-01-04 10:33:46 --> Query error: Table 'dms.log_pembayaran' doesn't exist - Invalid query: SELECT MAX(no_bayar) as max_id FROM log_pembayaran
ERROR - 2018-01-04 10:34:04 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 45
ERROR - 2018-01-04 10:41:45 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 45
ERROR - 2018-01-04 10:42:14 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 45
ERROR - 2018-01-04 10:42:23 --> Query error: Unknown column 'undefined' in 'where clause' - Invalid query: SELECT
                log_penggunaan_kbm.v_no,
                log_penggunaan_kbm.log_no             
                FROM
                mitra
                INNER JOIN log_penggunaan_kbm ON log_penggunaan_kbm.mitra_id = mitra.mitra_id
                WHERE
                    status_aktif = '1' AND log_penggunaan_kbm.status='on' and mitra.mitra_id=undefined
ERROR - 2018-01-04 10:43:36 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 45
ERROR - 2018-01-04 10:45:25 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 45
ERROR - 2018-01-04 10:46:15 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 45
ERROR - 2018-01-04 10:46:42 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 10:48:12 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 10:48:12 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 65
ERROR - 2018-01-04 10:48:13 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 10:48:45 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 10:48:45 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 65
ERROR - 2018-01-04 10:48:46 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 10:49:16 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 10:49:16 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 65
ERROR - 2018-01-04 10:49:18 --> Severity: Error --> Call to a member function pilih_mitra() on null C:\xampp\htdocs\project\dms\application\modules\pembayaran\controllers\Pembayaran.php 56
ERROR - 2018-01-04 10:49:28 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 10:49:28 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 65
ERROR - 2018-01-04 10:49:30 --> Severity: Error --> Call to a member function pilih_mitra() on null C:\xampp\htdocs\project\dms\application\modules\pembayaran\controllers\Pembayaran.php 56
ERROR - 2018-01-04 10:49:56 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 10:49:56 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 65
ERROR - 2018-01-04 13:47:53 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 13:47:53 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 65
ERROR - 2018-01-04 13:50:11 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 13:50:11 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 65
ERROR - 2018-01-04 14:01:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:01:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 65
ERROR - 2018-01-04 14:01:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 104
ERROR - 2018-01-04 14:02:40 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:02:40 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 65
ERROR - 2018-01-04 14:02:40 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 104
ERROR - 2018-01-04 14:03:38 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:03:38 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:04:16 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:04:16 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:05:54 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:05:54 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:06:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:06:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:06:39 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:06:39 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:06:51 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:06:51 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:07:02 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\kendaraan\views\kendaraan_form.php 44
ERROR - 2018-01-04 14:07:02 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\kendaraan\views\kendaraan_form.php 55
ERROR - 2018-01-04 14:07:02 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\kendaraan\views\kendaraan_form.php 82
ERROR - 2018-01-04 14:07:02 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\kendaraan\views\kendaraan_form.php 91
ERROR - 2018-01-04 14:07:04 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\kendaraan\views\kendaraan_form.php 55
ERROR - 2018-01-04 14:07:04 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\kendaraan\views\kendaraan_form.php 82
ERROR - 2018-01-04 14:07:04 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\kendaraan\views\kendaraan_form.php 91
ERROR - 2018-01-04 14:07:06 --> Severity: Warning --> Missing argument 1 for Kendaraan::load_kelas() C:\xampp\htdocs\project\dms\application\modules\kendaraan\controllers\Kendaraan.php 264
ERROR - 2018-01-04 14:07:07 --> Severity: Warning --> Missing argument 1 for Kendaraan::load_subkelas() C:\xampp\htdocs\project\dms\application\modules\kendaraan\controllers\Kendaraan.php 277
ERROR - 2018-01-04 14:07:10 --> Severity: Warning --> Missing argument 1 for Kendaraan::load_kelas() C:\xampp\htdocs\project\dms\application\modules\kendaraan\controllers\Kendaraan.php 264
ERROR - 2018-01-04 14:07:10 --> Severity: Warning --> Missing argument 1 for Kendaraan::load_subkelas() C:\xampp\htdocs\project\dms\application\modules\kendaraan\controllers\Kendaraan.php 277
ERROR - 2018-01-04 14:07:11 --> Severity: Warning --> Missing argument 1 for Kendaraan::load_kelas() C:\xampp\htdocs\project\dms\application\modules\kendaraan\controllers\Kendaraan.php 264
ERROR - 2018-01-04 14:07:11 --> Severity: Warning --> Missing argument 1 for Kendaraan::load_subkelas() C:\xampp\htdocs\project\dms\application\modules\kendaraan\controllers\Kendaraan.php 277
ERROR - 2018-01-04 14:07:29 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:07:29 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:07:35 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:07:35 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:08:34 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:08:34 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:18:35 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:18:35 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:18:41 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:19:58 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:19:58 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:20:00 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:20:02 --> Query error: Unknown column 'kurang_bayarX' in 'where clause' - Invalid query: SELECT
                    log_invoice.no_invoice,
                    log_invoice.kurang_bayar
                    FROM
                    log_invoice
                    WHERE kurang_bayarX >0 AND mitra_id='0000000028'
ERROR - 2018-01-04 14:20:46 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:20:46 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:20:48 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:25:09 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:25:09 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:25:11 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:26:08 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:26:08 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:26:09 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:26:32 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:26:32 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:26:34 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:29:49 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:29:49 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 14:29:50 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 76
ERROR - 2018-01-04 08:45:31 --> Severity: Compile Error --> Cannot redeclare Pembayaran::get_kurangbayar() C:\xampp\htdocs\project\dms\application\modules\pembayaran\controllers\Pembayaran.php 92
ERROR - 2018-01-04 14:45:51 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:45:51 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 77
ERROR - 2018-01-04 14:45:53 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 77
ERROR - 2018-01-04 14:46:41 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:46:41 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 77
ERROR - 2018-01-04 14:46:43 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 77
ERROR - 2018-01-04 14:47:39 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:47:39 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 77
ERROR - 2018-01-04 14:47:41 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 77
ERROR - 2018-01-04 14:51:13 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:51:13 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 14:51:15 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 14:59:45 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 14:59:45 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 14:59:47 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 15:10:34 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 15:10:34 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 15:10:36 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 15:10:49 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 15:10:49 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 15:10:52 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 15:11:35 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 15:11:35 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 15:11:37 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 15:13:33 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 15:13:33 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 15:13:35 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 15:13:55 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 15:13:55 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 15:13:57 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 15:14:14 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 15:14:14 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 15:14:15 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 16:09:03 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 45
ERROR - 2018-01-04 16:20:07 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 16:20:07 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 16:20:10 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 16:29:53 --> Query error: Table 'dms.log_5masterbarang' doesn't exist - Invalid query: SELECT *
FROM `log_5masterbarang`
WHERE `kodebarang` IS NULL
ERROR - 2018-01-04 16:30:42 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 16:30:42 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 16:30:44 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 16:30:58 --> Query error: Unknown column 'no_invoice' in 'field list' - Invalid query: INSERT INTO `log_pembayaran_ht` (`no_bayar`, `no_invoice`, `nilai_invoice`, `kurang_bayar`, `catatan`) VALUES ('BUM-UBI2018010001', 'INV-UBI2017120003', '1069000', NULL, '')
ERROR - 2018-01-04 16:31:32 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 16:31:32 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 16:31:33 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 16:31:45 --> Query error: Unknown column 'nilai_invoice' in 'field list' - Invalid query: INSERT INTO `log_pembayaran_dt` (`no_bayar`, `no_invoice`, `nilai_invoice`, `kurang_bayar`, `catatan`) VALUES ('BUM-UBI2018010001', 'INV-UBI2017120002', '725295', NULL, '')
ERROR - 2018-01-04 16:32:32 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 16:32:32 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 16:32:34 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 16:32:43 --> Query error: Column 'kurang_bayar' cannot be null - Invalid query: INSERT INTO `log_pembayaran_dt` (`no_bayar`, `no_invoice`, `kurang_bayar`, `catatan`) VALUES ('BUM-UBI2018010001', 'INV-UBI2017120003', NULL, '')
ERROR - 2018-01-04 16:33:17 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 16:33:17 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 16:33:19 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 16:33:28 --> Query error: Cannot add or update a child row: a foreign key constraint fails (`dms`.`log_pembayaran_dt`, CONSTRAINT `log_pembayaran_dt_ibfk_3` FOREIGN KEY (`no_invoice`) REFERENCES `log_invoice` (`no_invoice`)) - Invalid query: INSERT INTO `log_pembayaran_dt` (`no_bayar`, `no_invoice`, `kurang_bayar`, `catatan`) VALUES ('BUM-UBI2018010001', 'INV-UBI2017120003', '1069000', '')
ERROR - 2018-01-04 16:36:45 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 16:36:45 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 16:36:47 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 71
ERROR - 2018-01-04 16:57:58 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 16:57:58 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 82
ERROR - 2018-01-04 16:58:00 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 82
ERROR - 2018-01-04 17:01:39 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 17:01:39 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 82
ERROR - 2018-01-04 17:01:41 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 82
ERROR - 2018-01-04 17:02:00 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 82
ERROR - 2018-01-04 17:03:38 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 37
ERROR - 2018-01-04 17:03:38 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 82
ERROR - 2018-01-04 17:03:41 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 82
ERROR - 2018-01-04 17:05:17 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 82
ERROR - 2018-01-04 17:08:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 55
ERROR - 2018-01-04 17:08:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:08:24 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:09:28 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 55
ERROR - 2018-01-04 17:09:28 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:09:29 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:10:41 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 55
ERROR - 2018-01-04 17:10:41 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:10:43 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:12:07 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 55
ERROR - 2018-01-04 17:12:07 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:12:09 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:13:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 55
ERROR - 2018-01-04 17:13:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:13:24 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:17:16 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 55
ERROR - 2018-01-04 17:17:16 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:17:17 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:26:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:26:26 --> Severity: Error --> Call to a member function insert() on null C:\xampp\htdocs\project\dms\application\modules\pembayaran\controllers\Pembayaran.php 150
ERROR - 2018-01-04 17:28:36 --> Severity: Error --> Call to a member function insert() on null C:\xampp\htdocs\project\dms\application\modules\pembayaran\controllers\Pembayaran.php 150
ERROR - 2018-01-04 17:29:07 --> Severity: Error --> Call to a member function insert() on null C:\xampp\htdocs\project\dms\application\modules\pembayaran\controllers\Pembayaran.php 150
ERROR - 2018-01-04 17:29:12 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 55
ERROR - 2018-01-04 17:29:12 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:29:13 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:29:19 --> Severity: Error --> Call to a member function insert() on null C:\xampp\htdocs\project\dms\application\modules\pembayaran\controllers\Pembayaran.php 150
ERROR - 2018-01-04 17:40:03 --> Severity: Error --> Call to a member function insert() on null C:\xampp\htdocs\project\dms\application\modules\pembayaran\controllers\Pembayaran.php 150
ERROR - 2018-01-04 17:40:46 --> Severity: Error --> Call to a member function insert() on null C:\xampp\htdocs\project\dms\application\modules\pembayaran\controllers\Pembayaran.php 150
ERROR - 2018-01-04 17:40:50 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 55
ERROR - 2018-01-04 17:40:50 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:40:52 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:40:55 --> Severity: Error --> Call to a member function insert() on null C:\xampp\htdocs\project\dms\application\modules\pembayaran\controllers\Pembayaran.php 150
ERROR - 2018-01-04 17:41:45 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 55
ERROR - 2018-01-04 17:41:45 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:41:47 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:41:49 --> Severity: Error --> Call to a member function insert() on null C:\xampp\htdocs\project\dms\application\modules\pembayaran\controllers\Pembayaran.php 150
ERROR - 2018-01-04 17:42:56 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 55
ERROR - 2018-01-04 17:42:56 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:42:58 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:43:01 --> Query error: Unknown column 'no_bayarx' in 'field list' - Invalid query: INSERT INTO `log_pembayaran_ht` (`no_bayarx`, `tgl_bayar`, `keterangan`, `created_on`, `created_by`) VALUES ('BUM-UBI2018010001', '1970-01-01', NULL, '2018-01-04 17:43:01', '1')
ERROR - 2018-01-04 17:43:47 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 55
ERROR - 2018-01-04 17:43:47 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:43:49 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 100
ERROR - 2018-01-04 17:43:52 --> Query error: Column 'keterangan' cannot be null - Invalid query: INSERT INTO `log_pembayaran_ht` (`no_bayar`, `tgl_bayar`, `keterangan`, `created_on`, `created_by`) VALUES ('BUM-UBI2018010001', '1970-01-01', NULL, '2018-01-04 17:43:52', '1')
ERROR - 2018-01-04 17:44:35 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 55
ERROR - 2018-01-04 17:44:35 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 89
ERROR - 2018-01-04 17:44:37 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 89
ERROR - 2018-01-04 17:44:56 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 55
ERROR - 2018-01-04 17:44:56 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 93
ERROR - 2018-01-04 17:44:58 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 93
ERROR - 2018-01-04 17:45:05 --> Query error: Column 'keterangan' cannot be null - Invalid query: INSERT INTO `log_pembayaran_ht` (`no_bayar`, `tgl_bayar`, `keterangan`, `created_on`, `created_by`) VALUES ('BUM-UBI2018010001', '1970-01-01', NULL, '2018-01-04 17:45:05', '1')
ERROR - 2018-01-04 17:45:45 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 47
ERROR - 2018-01-04 17:45:45 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 17:45:46 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 17:45:55 --> Query error: Column 'keterangan' cannot be null - Invalid query: INSERT INTO `log_pembayaran_ht` (`no_bayar`, `tgl_bayar`, `keterangan`, `created_on`, `created_by`) VALUES ('BUM-UBI2018010001', '1970-01-01', NULL, '2018-01-04 17:45:55', '1')
ERROR - 2018-01-04 17:46:33 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 47
ERROR - 2018-01-04 17:46:33 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 17:46:35 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 17:47:50 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 47
ERROR - 2018-01-04 17:47:50 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 17:47:52 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 17:47:54 --> Query error: Column 'keterangan' cannot be null - Invalid query: INSERT INTO `log_pembayaran_ht` (`no_bayar`, `tgl_bayar`, `keterangan`, `created_on`, `created_by`) VALUES ('BUM-UBI2018010001', '1970-01-01', NULL, '2018-01-04 17:47:54', '1')
ERROR - 2018-01-04 17:48:20 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 47
ERROR - 2018-01-04 17:48:20 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 17:48:21 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 17:48:56 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 47
ERROR - 2018-01-04 17:48:56 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 17:48:57 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 17:49:03 --> Query error: Cannot add or update a child row: a foreign key constraint fails (`dms`.`log_pembayaran_ht`, CONSTRAINT `log_pembayaran_ht_ibfk_1` FOREIGN KEY (`no_bayar`) REFERENCES `log_pembayaran_dt` (`no_bayar`)) - Invalid query: INSERT INTO `log_pembayaran_ht` (`no_bayar`, `tgl_bayar`, `keterangan`, `created_on`, `created_by`) VALUES ('BUM-UBI2018010001', '1970-01-01', ' adsf', '2018-01-04 17:49:03', '1')
ERROR - 2018-01-04 17:52:28 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 47
ERROR - 2018-01-04 17:52:28 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 17:52:33 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 47
ERROR - 2018-01-04 17:52:33 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 17:52:56 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 17:53:55 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 47
ERROR - 2018-01-04 17:53:55 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 17:54:58 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 47
ERROR - 2018-01-04 17:54:58 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 17:55:00 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 17:55:16 --> Query error: Cannot add or update a child row: a foreign key constraint fails (`dms`.`log_pembayaran_dt`, CONSTRAINT `log_pembayaran_dt_ibfk_3` FOREIGN KEY (`no_invoice`) REFERENCES `log_invoice` (`no_invoice`)) - Invalid query: INSERT INTO `log_pembayaran_dt` (`no_bayar`, `no_invoice`, `kurang_bayar`, `catatan`) VALUES ('BUM-UBI2018010001', 'INV-UBI2017120002', '725295', '')
ERROR - 2018-01-04 18:00:03 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 45
ERROR - 2018-01-04 18:00:07 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 47
ERROR - 2018-01-04 18:00:07 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 18:00:17 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\pembayaran\views\pembayaran_form.php 84
ERROR - 2018-01-04 18:00:45 --> Query error: Cannot add or update a child row: a foreign key constraint fails (`dms`.`log_pembayaran_dt`, CONSTRAINT `log_pembayaran_dt_ibfk_3` FOREIGN KEY (`no_invoice`) REFERENCES `log_invoice` (`no_invoice`)) - Invalid query: INSERT INTO `log_pembayaran_dt` (`no_bayar`, `no_invoice`, `kurang_bayar`, `catatan`) VALUES ('BUM-UBI2018010002', 'INV-UBI2017120002', '725295', 'v')
ERROR - 2018-01-04 18:01:30 --> Query error: Cannot add or update a child row: a foreign key constraint fails (`dms`.`log_pembayaran_dt`, CONSTRAINT `log_pembayaran_dt_ibfk_3` FOREIGN KEY (`no_invoice`) REFERENCES `log_invoice` (`no_invoice`)) - Invalid query: INSERT INTO `log_pembayaran_dt` (`no_bayar`, `no_invoice`, `kurang_bayar`, `catatan`) VALUES ('BUM-UBI2018010002', 'INV-UBI2017120002', '725295', 'v')
ERROR - 2018-01-04 12:05:08 --> 404 Page Not Found: /index
