<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2018-03-09 03:49:32 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\project\dms\application\modules\users\views\login.php 5
ERROR - 2018-03-09 03:49:34 --> 404 Page Not Found: /index
ERROR - 2018-03-09 03:49:34 --> 404 Page Not Found: /index
ERROR - 2018-03-09 03:49:34 --> 404 Page Not Found: /index
ERROR - 2018-03-09 09:49:44 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 10:25:36 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 10:28:23 --> Query error: Column 'no_perkiraan' cannot be null - Invalid query: INSERT INTO `jurnal` (`tipe`, `nomor`, `tanggal`, `no_perkiraan`, `keterangan`, `no_reff`, `kredit`, `debet`, `tp`) VALUES ('BUM', '771-AM1800017', '2018-03-09', NULL, 'Titipan Bank Charge noinv#INV-UBI2018030012 An#Herman Kardon', 'INV-UBI2018030012', '5000', 0, 'K')
ERROR - 2018-03-09 10:31:18 --> Query error: Duplicate entry 'INV-UBI2018030012' for key 'PRIMARY' - Invalid query: INSERT INTO `log_invoice` (`no_invoice`, `tgl_invoice`, `tgl_tempo`, `periode_start`, `periode_end`, `log_no`, `mitra_id`, `v_no`, `jam_aktif`, `menit_aktif`, `jml_trip`, `trip_earning`, `uber_fee`, `insentive_misc`, `pendapatan_mitra`, `net_afteruf`, `tunai`, `kartu_kredit`, `management_fee`, `biaya_sewa`, `bank_charge`, `total_bayar_mitra`, `nilai_invoice`, `kurang_bayar`, `notes`, `sts_inv`, `created_on`, `created_by`) VALUES ('INV-UBI2018030012', '2018-03-09', '2018-03-11', '2018-02-27', '2018-03-04', 'LOG-20180100004', '0000000003', 'UBIR502G', '37', '6', '14', '780379', '260125', '0', '1040504', '780379', '774500', '5879', '20810', '1028571', '5000', '1054381', '1048502', 1048502, 'Biaya denda keterlambatan adalah IDR20,000 per hari', 'piutang', '2018-03-09 10:31:18', '1')
ERROR - 2018-03-09 10:32:06 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 10:32:13 --> Query error: Table 'dms.virtual_account' doesn't exist - Invalid query: SELECT
      virtual_account.no_va,
      virtual_account.nama_bank
      FROM
      log_invoice
      INNER JOIN mitra ON log_invoice.mitra_id = mitra.mitra_id
      INNER JOIN virtual_account ON mitra.nim = virtual_account.no_va
      WHERE log_invoice.no_invoice='INV-UBI2018030012'
ERROR - 2018-03-09 10:35:34 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 10:38:37 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 10:40:37 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 10:42:46 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 10:56:40 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 10:56:49 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 10:58:06 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:02:42 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:07:53 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:14:12 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:15:55 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:15:58 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:18:39 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:18:40 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:23:39 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:23:55 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:25:58 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:26:09 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:29:20 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:29:55 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:29:58 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:31:09 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:31:17 --> Query error: Unknown column 'nomorX' in 'where clause' - Invalid query: UPDATE `jurnal` SET `tanggal` = '2018-03-09', `no_perkiraan` = '4301-01-01', `keterangan` = 'Management Fee noinv#INV-UBI2018030016 An#udin', `no_reff` = 'INV-UBI2018030016', `kredit` = '17000', `debet` = 0
WHERE `nomorX` = '771-AM1800021'
AND `no_perkiraan` = '4301-01-01'
AND `tipe` = 'BUM'
AND `tp` = 'K'
ERROR - 2018-03-09 11:31:46 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:32:13 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:32:40 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:33:15 --> Query error: Unknown column 'nomorx' in 'where clause' - Invalid query: UPDATE `jurnal` SET `tanggal` = '2018-03-09', `no_perkiraan` = '4301-01-01', `keterangan` = 'Management Fee noinv#INV-UBI2018030016 An#udin', `no_reff` = 'INV-UBI2018030016', `kredit` = '17000', `debet` = 0
WHERE `nomorx` = '771-AM1800021'
AND `no_perkiraan` = '4301-01-01'
AND `tipe` = 'BUM'
AND `tp` = 'K'
ERROR - 2018-03-09 11:39:18 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 11:39:29 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 07:42:15 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\project\dms\application\modules\users\views\login.php 5
ERROR - 2018-03-09 07:42:15 --> 404 Page Not Found: /index
ERROR - 2018-03-09 07:42:15 --> 404 Page Not Found: /index
ERROR - 2018-03-09 07:42:15 --> 404 Page Not Found: /index
ERROR - 2018-03-09 15:03:04 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:03:12 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:04:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:05:40 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:07:03 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:09:12 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:09:57 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:12:32 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:12:41 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:26:35 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:27:44 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:28:39 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:28:57 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:33:16 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:34:41 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:35:15 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:35:36 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:39:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:39:30 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:46:23 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:47:51 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:48:07 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:49:43 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:53:48 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:54:58 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:55:47 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 15:59:34 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 16:02:43 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\modules\penagihan\views\penagihan_form.php 48
ERROR - 2018-03-09 10:09:27 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\project\dms\application\modules\users\views\login.php 5
ERROR - 2018-03-09 10:09:28 --> 404 Page Not Found: /index
ERROR - 2018-03-09 10:09:28 --> 404 Page Not Found: /index
ERROR - 2018-03-09 10:09:28 --> 404 Page Not Found: /index
