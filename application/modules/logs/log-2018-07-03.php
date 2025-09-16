<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2018-07-03 08:55:07 --> Severity: Notice --> Undefined variable: nama_program /home/www/importa/application/modules/users/views/login_animate.php 6
ERROR - 2018-07-03 08:57:53 --> Severity: Notice --> Undefined variable: nama_program /home/www/importa/application/modules/users/views/login_animate.php 6
ERROR - 2018-07-03 08:57:59 --> Severity: Notice --> Undefined variable: nama_program /home/www/importa/application/modules/users/views/login_animate.php 6
ERROR - 2018-07-03 08:58:16 --> Query error: Unknown column 'karyawan.id_divisi' in 'field list' - Invalid query: SELECT `karyawan`.`id_divisi`, `karyawan`.`id_karyawan`, `karyawan`.`nik`, `karyawan`.`nama_karyawan`, `karyawan`.`tempatlahir`, `karyawan`.`tanggallahir`, `karyawan`.`jeniskelamin`, `karyawan`.`alamataktif`, `karyawan`.`sts_aktif`, `divisi`.`nm_divisi`
FROM `karyawan`
JOIN `divisi` ON `karyawan`.`id_divisi` = `divisi`.`id_divisi`
WHERE `karyawan`.`deleted` =0
ORDER BY `nama_karyawan` ASC
ERROR - 2018-07-03 08:58:16 --> Query error: Unknown column 'karyawan.id_divisi' in 'field list' - Invalid query: SELECT `karyawan`.`id_divisi`, `karyawan`.`id_karyawan`, `karyawan`.`nik`, `karyawan`.`nama_karyawan`, `karyawan`.`tempatlahir`, `karyawan`.`tanggallahir`, `karyawan`.`jeniskelamin`, `karyawan`.`alamataktif`, `karyawan`.`sts_aktif`, `divisi`.`nm_divisi`
FROM `karyawan`
JOIN `divisi` ON `karyawan`.`id_divisi` = `divisi`.`id_divisi`
WHERE `karyawan`.`deleted` =0
ORDER BY `nama_karyawan` ASC
ERROR - 2018-07-03 14:35:42 --> Severity: Notice --> Undefined variable: nama_program /home/www/importa/application/modules/users/views/login_animate.php 6
ERROR - 2018-07-03 14:42:08 --> Query error: Duplicate entry '101-SO180001' for key 'PRIMARY' - Invalid query: INSERT INTO `trans_so_header` (`no_so`, `id_customer`, `nm_customer`, `tanggal`, `id_salesman`, `nm_salesman`, `pic`, `waktu`, `dpp`, `ppn`, `flag_ppn`, `total`) VALUES ('101-SO180001', '', '', '2018-07-03', '', '', '', '2018-07-03 14:42:08', '14000000', '1400000', NULL, '15400000')
ERROR - 2018-07-03 14:48:39 --> 404 Page Not Found: ../modules/salesorder/controllers/Salesorder/hapus_item_so
ERROR - 2018-07-03 14:59:18 --> 404 Page Not Found: ../modules/salesorder/controllers/Salesorder/hapus_header_so
ERROR - 2018-07-03 15:00:55 --> Query error: Unknown column 'deleted' in 'field list' - Invalid query: UPDATE `trans_so_header` SET `deleted` = 1, `deleted_by` = '3'
WHERE `no_so` = '101-SO180001'
ERROR - 2018-07-03 15:01:04 --> Query error: Unknown column 'deleted' in 'field list' - Invalid query: UPDATE `trans_so_header` SET `deleted` = 1, `deleted_by` = '3'
WHERE `no_so` = '-SO180001'
ERROR - 2018-07-03 15:12:36 --> Severity: Notice --> Undefined variable: nama_program /home/www/importa/application/modules/users/views/login_animate.php 6
ERROR - 2018-07-03 15:12:49 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/komponen/views/komponen_form.php 36
ERROR - 2018-07-03 15:13:16 --> Severity: Notice --> Trying to get property of non-object /home/www/importa/application/modules/salesorder/models/Salesorder_model.php 97
ERROR - 2018-07-03 15:13:24 --> Severity: Notice --> Trying to get property of non-object /home/www/importa/application/modules/salesorder/models/Salesorder_model.php 97
ERROR - 2018-07-03 15:13:24 --> Query error: Duplicate entry '-SO180001-G02B180001' for key 'PRIMARY' - Invalid query: INSERT INTO `trans_so_detail` (`no_so`, `id_barang`, `nm_barang`, `satuan`, `jenis`, `qty_order`, `qty_supply`, `ukuran`, `harga`, `diskon`) VALUES ('-SO180001', 'G02B180001', 'Kursi', '2', '', '1', '4', '', '250000', '0')
ERROR - 2018-07-03 15:13:34 --> Severity: Notice --> Trying to get property of non-object /home/www/importa/application/modules/salesorder/models/Salesorder_model.php 97
ERROR - 2018-07-03 15:14:06 --> Severity: Notice --> Undefined variable: nama_program /home/www/importa/application/modules/users/views/login_animate.php 6
ERROR - 2018-07-03 16:26:22 --> Severity: Notice --> Undefined property: stdClass::$0 /home/www/importa/application/modules/salesorder/controllers/Salesorder.php 168
ERROR - 2018-07-03 16:26:22 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '0) VALUES (NULL)' at line 1 - Invalid query: INSERT INTO `trans_so_detail` (0) VALUES (NULL)
ERROR - 2018-07-03 16:26:36 --> Severity: Notice --> Undefined property: stdClass::$0 /home/www/importa/application/modules/salesorder/controllers/Salesorder.php 168
ERROR - 2018-07-03 16:26:36 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '0) VALUES (NULL)' at line 1 - Invalid query: INSERT INTO `trans_so_detail` (0) VALUES (NULL)
ERROR - 2018-07-03 16:29:00 --> Severity: Notice --> Undefined property: stdClass::$qty_avl /home/www/importa/application/modules/salesorder/controllers/Salesorder.php 175
ERROR - 2018-07-03 16:29:00 --> Query error: Column 'qty_supply' cannot be null - Invalid query: INSERT INTO `trans_so_detail` (`no_so`, `id_barang`, `nm_barang`, `satuan`, `jenis`, `qty_order`, `qty_supply`, `ukuran`, `harga`, `diskon`) VALUES ('101-SO180001', 'G01B180001', 'Meja Makan', '', '', '2', NULL, '', '4500000', '0')
ERROR - 2018-07-03 16:53:47 --> 404 Page Not Found: ../modules/salesorder/controllers//index
ERROR - 2018-07-03 16:53:49 --> 404 Page Not Found: ../modules/salesorder/controllers//index
ERROR - 2018-07-03 16:53:59 --> 404 Page Not Found: ../modules/salesorder/controllers//index
ERROR - 2018-07-03 16:54:08 --> 404 Page Not Found: ../modules/salesorder/controllers//index
ERROR - 2018-07-03 16:54:46 --> 404 Page Not Found: ../modules/salesorder/controllers//index
ERROR - 2018-07-03 17:19:24 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/supplier/views/supplier_form.php 103
ERROR - 2018-07-03 17:26:08 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/supplier/views/supplier_form.php 103
ERROR - 2018-07-03 17:26:13 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/supplier/views/supplier_form.php 103
ERROR - 2018-07-03 17:27:54 --> Query error: Table 'importa_sys.trans_so_headers' doesn't exist - Invalid query: SELECT *
FROM `trans_so_headers`
WHERE `trans_so_headers`.`no_so` = '101-SO180001'
ERROR - 2018-07-03 17:51:54 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/supplier/views/supplier_form.php 103
ERROR - 2018-07-03 18:25:03 --> 404 Page Not Found: ../modules/salesorder/controllers/Salesorder/edit
ERROR - 2018-07-03 18:30:40 --> Severity: Notice --> Undefined variable: nama_program /home/www/importa/application/modules/users/views/login_animate.php 6
ERROR - 2018-07-03 18:30:59 --> Could not find the language line "barang_title_manage"
ERROR - 2018-07-03 18:30:59 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 23
ERROR - 2018-07-03 18:30:59 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 93
ERROR - 2018-07-03 18:31:32 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/komponen/views/komponen_form.php 36
ERROR - 2018-07-03 18:31:36 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/komponen/views/komponen_form.php 36
ERROR - 2018-07-03 18:31:37 --> Query error: Unknown column 'karyawan.id_divisi' in 'field list' - Invalid query: SELECT `karyawan`.`id_divisi`, `karyawan`.`id_karyawan`, `karyawan`.`nik`, `karyawan`.`nama_karyawan`, `karyawan`.`tempatlahir`, `karyawan`.`tanggallahir`, `karyawan`.`jeniskelamin`, `karyawan`.`alamataktif`, `karyawan`.`sts_aktif`, `divisi`.`nm_divisi`
FROM `karyawan`
JOIN `divisi` ON `karyawan`.`id_divisi` = `divisi`.`id_divisi`
WHERE `karyawan`.`deleted` =0
ORDER BY `nama_karyawan` ASC
ERROR - 2018-07-03 18:32:04 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/koli/views/koli_form.php 36
ERROR - 2018-07-03 18:36:25 --> Query error: Unknown column 'no_sos' in 'where clause' - Invalid query: SELECT *
FROM `trans_so_header`
WHERE `no_sos` = '101-SO180002'
ERROR - 2018-07-03 18:36:52 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 38
ERROR - 2018-07-03 18:36:52 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 93
ERROR - 2018-07-03 18:36:52 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 107
ERROR - 2018-07-03 18:36:52 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 165
ERROR - 2018-07-03 18:36:52 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 178
ERROR - 2018-07-03 18:36:52 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 602
ERROR - 2018-07-03 18:37:36 --> 404 Page Not Found: /index
ERROR - 2018-07-03 18:37:36 --> 404 Page Not Found: /index
ERROR - 2018-07-03 18:37:36 --> 404 Page Not Found: /index
ERROR - 2018-07-03 18:37:36 --> 404 Page Not Found: /index
ERROR - 2018-07-03 18:37:36 --> 404 Page Not Found: /index
ERROR - 2018-07-03 18:37:36 --> 404 Page Not Found: /index
ERROR - 2018-07-03 18:37:41 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 38
ERROR - 2018-07-03 18:37:41 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 93
ERROR - 2018-07-03 18:37:41 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 107
ERROR - 2018-07-03 18:37:41 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 165
ERROR - 2018-07-03 18:37:41 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 178
ERROR - 2018-07-03 18:37:41 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 602
ERROR - 2018-07-03 18:37:48 --> Query error: Unknown column 'karyawan.id_divisi' in 'field list' - Invalid query: SELECT `karyawan`.`id_divisi`, `karyawan`.`id_karyawan`, `karyawan`.`nik`, `karyawan`.`nama_karyawan`, `karyawan`.`tempatlahir`, `karyawan`.`tanggallahir`, `karyawan`.`jeniskelamin`, `karyawan`.`alamataktif`, `karyawan`.`sts_aktif`, `divisi`.`nm_divisi`
FROM `karyawan`
JOIN `divisi` ON `karyawan`.`id_divisi` = `divisi`.`id_divisi`
WHERE `karyawan`.`deleted` =0
ORDER BY `nama_karyawan` ASC
ERROR - 2018-07-03 18:38:22 --> Query error: Unknown column 'no_sos' in 'where clause' - Invalid query: SELECT *
FROM `trans_so_detail`
WHERE `no_sos` = '101-SO180002'
ERROR - 2018-07-03 18:41:15 --> Query error: Unknown column 'no_sos' in 'where clause' - Invalid query: SELECT *
FROM `trans_so_detail`
WHERE `no_sos` = '101-SO180002'
ERROR - 2018-07-03 18:44:09 --> Query error: Unknown column 'karyawan.id_divisi' in 'field list' - Invalid query: SELECT `karyawan`.`id_divisi`, `karyawan`.`id_karyawan`, `karyawan`.`nik`, `karyawan`.`nama_karyawan`, `karyawan`.`tempatlahir`, `karyawan`.`tanggallahir`, `karyawan`.`jeniskelamin`, `karyawan`.`alamataktif`, `karyawan`.`sts_aktif`, `divisi`.`nm_divisi`
FROM `karyawan`
JOIN `divisi` ON `karyawan`.`divisi` = `divisi`.`id_divisi`
WHERE `karyawan`.`deleted` =0
ORDER BY `nama_karyawan` ASC
ERROR - 2018-07-03 18:44:30 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/karyawan/views/karyawan_form.php 87
ERROR - 2018-07-03 18:45:27 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/komponen/views/komponen_form.php 36
ERROR - 2018-07-03 18:45:34 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/karyawan/views/karyawan_form.php 87
ERROR - 2018-07-03 19:02:53 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 38
ERROR - 2018-07-03 19:02:53 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 93
ERROR - 2018-07-03 19:02:53 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 107
ERROR - 2018-07-03 19:02:53 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 165
ERROR - 2018-07-03 19:02:53 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 178
ERROR - 2018-07-03 19:02:53 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 602
ERROR - 2018-07-03 19:03:04 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/koli/views/koli_form.php 36
ERROR - 2018-07-03 19:03:10 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/komponen/views/komponen_form.php 36
ERROR - 2018-07-03 19:03:14 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/koli/views/koli_form.php 36
ERROR - 2018-07-03 19:03:49 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/koli/views/koli_form.php 36
ERROR - 2018-07-03 19:03:55 --> Could not find the language line "barang_title_manage"
ERROR - 2018-07-03 19:03:55 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 23
ERROR - 2018-07-03 19:03:55 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 93
ERROR - 2018-07-03 19:04:05 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 38
ERROR - 2018-07-03 19:04:05 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 93
ERROR - 2018-07-03 19:04:05 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 107
ERROR - 2018-07-03 19:04:05 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 165
ERROR - 2018-07-03 19:04:05 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 178
ERROR - 2018-07-03 19:04:05 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 602
ERROR - 2018-07-03 19:04:37 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/koli/views/koli_form.php 36
ERROR - 2018-07-03 19:05:05 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/koli/views/koli_form.php 36
ERROR - 2018-07-03 19:07:03 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/koli/views/koli_form.php 36
ERROR - 2018-07-03 19:07:11 --> Could not find the language line "barang_title_manage"
ERROR - 2018-07-03 19:07:11 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 23
ERROR - 2018-07-03 19:07:11 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 93
ERROR - 2018-07-03 19:07:29 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/koli/views/koli_form.php 36
ERROR - 2018-07-03 19:07:42 --> Could not find the language line "barang_title_manage"
ERROR - 2018-07-03 19:07:42 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 23
ERROR - 2018-07-03 19:07:42 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 93
ERROR - 2018-07-03 19:08:14 --> Could not find the language line "barang_title_edit"
ERROR - 2018-07-03 19:09:00 --> Could not find the language line "barang_title_manage"
ERROR - 2018-07-03 19:09:00 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 23
ERROR - 2018-07-03 19:09:00 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 93
ERROR - 2018-07-03 19:09:43 --> Could not find the language line "barang_title_manage"
ERROR - 2018-07-03 19:09:43 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 23
ERROR - 2018-07-03 19:09:43 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 93
ERROR - 2018-07-03 19:09:54 --> Could not find the language line "barang_title_manage"
ERROR - 2018-07-03 19:09:54 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 23
ERROR - 2018-07-03 19:09:54 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 93
ERROR - 2018-07-03 19:10:01 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/koli/views/koli_form.php 36
ERROR - 2018-07-03 19:10:27 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/koli/views/koli_form.php 36
ERROR - 2018-07-03 19:11:00 --> Could not find the language line "barang_title_manage"
ERROR - 2018-07-03 19:11:00 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 23
ERROR - 2018-07-03 19:11:00 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 93
ERROR - 2018-07-03 19:19:25 --> Severity: Notice --> Undefined variable: nama_program /home/www/importa/application/modules/users/views/login_animate.php 6
ERROR - 2018-07-03 19:49:43 --> Could not find the language line "barang_title_manage"
ERROR - 2018-07-03 19:49:43 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 23
ERROR - 2018-07-03 19:49:43 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 93
ERROR - 2018-07-03 20:21:53 --> Could not find the language line "barang_title_manage"
ERROR - 2018-07-03 20:21:53 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 23
ERROR - 2018-07-03 20:21:53 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/barang/views/barang_form.php 93
ERROR - 2018-07-03 20:26:04 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 38
ERROR - 2018-07-03 20:26:04 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 93
ERROR - 2018-07-03 20:26:04 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 107
ERROR - 2018-07-03 20:26:04 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 165
ERROR - 2018-07-03 20:26:04 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 178
ERROR - 2018-07-03 20:26:04 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 602
ERROR - 2018-07-03 20:26:08 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 107
ERROR - 2018-07-03 20:26:35 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 38
ERROR - 2018-07-03 20:26:35 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 93
ERROR - 2018-07-03 20:26:35 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 107
ERROR - 2018-07-03 20:26:35 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 165
ERROR - 2018-07-03 20:26:35 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 178
ERROR - 2018-07-03 20:26:35 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 602
ERROR - 2018-07-03 20:26:48 --> Severity: Warning --> Invalid argument supplied for foreach() /home/www/importa/application/modules/customer/views/customer_form.php 107
