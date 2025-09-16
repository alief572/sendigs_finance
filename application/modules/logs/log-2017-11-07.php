<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2017-11-07 03:54:29 --> Could not find the language line "Deposit_invalid_id"
ERROR - 2017-11-07 04:04:43 --> Query error: Unknown column 'created_on' in 'field list' - Invalid query: INSERT INTO `setoran` (`tipe`, `setoran`, `dp`, `adm`, `created_on`, `created_by`) VALUES ('XXX', '1200000', '0', '5000', '2017-11-07 04:04:43', '1')
ERROR - 2017-11-07 04:04:51 --> Query error: Unknown column 'created_on' in 'field list' - Invalid query: INSERT INTO `setoran` (`tipe`, `setoran`, `dp`, `adm`, `created_on`, `created_by`) VALUES ('XXX', '1200000', '0', '5000', '2017-11-07 04:04:51', '1')
ERROR - 2017-11-07 04:07:56 --> Could not find the language line "Deposit_invalid_id"
ERROR - 2017-11-07 04:10:04 --> Could not find the language line "Deposit_invalid_id"
ERROR - 2017-11-07 04:10:19 --> Could not find the language line "Deposit_invalid_id"
ERROR - 2017-11-07 04:11:22 --> Could not find the language line "Setoran_title_edit"
ERROR - 2017-11-07 04:18:25 --> Could not find the language line "Deposit_title_edit"
ERROR - 2017-11-07 04:18:39 --> Could not find the language line "Deposit_title_edit"
ERROR - 2017-11-07 04:19:05 --> Could not find the language line "Setoran_title_edit"
ERROR - 2017-11-07 04:19:19 --> Could not find the language line "Setoran_title_edit"
ERROR - 2017-11-07 04:24:38 --> Severity: Error --> Call to a member function delete() on null C:\xampp\htdocs\project\dms\application\modules\setoran\controllers\Setoran.php 189
ERROR - 2017-11-07 04:24:47 --> Severity: Error --> Call to a member function delete() on null C:\xampp\htdocs\project\dms\application\modules\setoran\controllers\Setoran.php 189
ERROR - 2017-11-07 04:30:52 --> Could not find the language line "Setoran_title_edit"
ERROR - 2017-11-07 07:45:41 --> Could not find the language line "Setoran_title_edit"
ERROR - 2017-11-07 07:45:45 --> Query error: Unknown column 'idlogx' in 'field list' - Invalid query: INSERT INTO `log_aktifitas` (`idlogx`, `kode_universal`, `fasilitas`, `ket`, `jumlah`, `sql`, `status`, `created_on`, `created_by`) VALUES ('201711774545M1114C', '7', '18', 'SUKSES, Edit data Setoran 7', '1', 'UPDATE `setoran` SET `tipe` = CASE \nWHEN `tipe_id` = \'7\' THEN \'a\'\nELSE `tipe` END, `setoran` = CASE \nWHEN `tipe_id` = \'7\' THEN \'1\'\nELSE `setoran` END, `dp` = CASE \nWHEN `tipe_id` = \'7\' THEN \'1\'\nELSE `dp` END, `adm` = CASE \nWHEN `tipe_id` = \'7\' THEN \'12\'\nELSE `adm` END, `modified_on` = CASE \nWHEN `tipe_id` = \'7\' THEN \'2017-11-07 07:45:45\'\nELSE `modified_on` END, `modified_by` = CASE \nWHEN `tipe_id` = \'7\' THEN \'1\'\nELSE `modified_by` END\nWHERE `tipe_id` IN(\'7\')', 1, '2017-11-07 07:45:45', '1')
ERROR - 2017-11-07 07:48:53 --> Query error: Unknown column 'idlogx' in 'field list' - Invalid query: INSERT INTO `log_aktifitas` (`idlogx`, `kode_universal`, `fasilitas`, `ket`, `jumlah`, `sql`, `status`, `created_on`, `created_by`) VALUES ('201711774853Q945W', '10', '18', 'SUKSES, Delete data Setoran 10', 0, 'DELETE FROM `setoran`\nWHERE `tipe_id` = \'10\'', 1, '2017-11-07 07:48:53', '1')
ERROR - 2017-11-07 08:00:17 --> 404 Page Not Found: ../modules/mitra/controllers//index
ERROR - 2017-11-07 08:27:30 --> Severity: Error --> Call to a member function insert() on null C:\xampp\htdocs\project\dms\application\modules\deposit\controllers\Deposit.php 148
ERROR - 2017-11-07 08:27:45 --> Severity: Error --> Call to a member function insert() on null C:\xampp\htdocs\project\dms\application\modules\deposit\controllers\Deposit.php 148
ERROR - 2017-11-07 08:35:30 --> Query error: Unknown column 'idlogX' in 'field list' - Invalid query: INSERT INTO `log_aktifitas` (`idlogX`, `kode_universal`, `fasilitas`, `ket`, `jumlah`, `sql`, `status`, `created_on`, `created_by`) VALUES ('201711783530I806N', '12', '18', 'SUKSES, Delete data Setoran 12', 0, 'DELETE FROM `setoran`\nWHERE `tipe_id` = \'12\'', 1, '2017-11-07 08:35:30', '1')
ERROR - 2017-11-07 08:36:18 --> Severity: Error --> Call to undefined function simpan_aktifitasxxx() C:\xampp\htdocs\project\dms\application\modules\setoran\controllers\Setoran.php 165
ERROR - 2017-11-07 08:46:16 --> Query error: Unknown column 'idlogx' in 'field list' - Invalid query: INSERT INTO `log_aktifitas` (`idlogx`, `kode_universal`, `fasilitas`, `ket`, `jumlah`, `sql`, `status`, `created_on`, `created_by`) VALUES ('201711784616C491B', '18', '20', 'SUKSES, Delete data Setoran 18', 0, 'DELETE FROM `setoran`\nWHERE `tipe_id` = \'18\'', 1, '2017-11-07 08:46:16', '1')
ERROR - 2017-11-07 08:46:19 --> Query error: Unknown column 'idlogx' in 'field list' - Invalid query: INSERT INTO `log_aktifitas` (`idlogx`, `kode_universal`, `fasilitas`, `ket`, `jumlah`, `sql`, `status`, `created_on`, `created_by`) VALUES ('201711784619J387R', '18', '20', 'SUKSES, Delete data Setoran 18', 0, 'DELETE FROM `setoran`\nWHERE `tipe_id` = \'18\'', 1, '2017-11-07 08:46:19', '1')
ERROR - 2017-11-07 09:25:00 --> Severity: Error --> Call to undefined function gen_primaryX() C:\xampp\htdocs\project\dms\application\modules\aktifitas\models\Aktifitas_model.php 70
ERROR - 2017-11-07 09:25:13 --> Severity: Error --> Call to undefined function gen_primaryX() C:\xampp\htdocs\project\dms\application\modules\aktifitas\models\Aktifitas_model.php 70
ERROR - 2017-11-07 09:26:55 --> Severity: Error --> Call to undefined function gen_primaryX() C:\xampp\htdocs\project\dms\application\modules\aktifitas\models\Aktifitas_model.php 75
ERROR - 2017-11-07 09:27:35 --> Severity: Error --> Call to undefined function gen_primaryX() C:\xampp\htdocs\project\dms\application\modules\aktifitas\models\Aktifitas_model.php 70
ERROR - 2017-11-07 09:28:01 --> Severity: Error --> Call to undefined function gen_primaryX() C:\xampp\htdocs\project\dms\application\modules\aktifitas\models\Aktifitas_model.php 70
ERROR - 2017-11-07 09:28:47 --> Severity: Error --> Call to undefined function gen_primaryX() C:\xampp\htdocs\project\dms\application\modules\aktifitas\models\Aktifitas_model.php 70
ERROR - 2017-11-07 09:29:28 --> Severity: Error --> Call to undefined function gen_primaryY() C:\xampp\htdocs\project\dms\application\modules\aktifitas\models\Aktifitas_model.php 103
ERROR - 2017-11-07 09:31:28 --> Severity: Parsing Error --> syntax error, unexpected '}' C:\xampp\htdocs\project\dms\application\modules\aktifitas\models\Aktifitas_model.php 75
ERROR - 2017-11-07 09:32:08 --> Severity: Error --> Call to undefined function gen_primaryY() C:\xampp\htdocs\project\dms\application\modules\aktifitas\models\Aktifitas_model.php 104
ERROR - 2017-11-07 09:37:20 --> Severity: Error --> Call to undefined function gen_primaryY() C:\xampp\htdocs\project\dms\application\modules\aktifitas\models\Aktifitas_model.php 104
ERROR - 2017-11-07 09:38:22 --> Severity: Error --> Call to undefined function gen_primaryD() C:\xampp\htdocs\project\dms\application\modules\aktifitas\models\Aktifitas_model.php 70
ERROR - 2017-11-07 09:42:19 --> Severity: Error --> Call to undefined function gen_primaryD() C:\xampp\htdocs\project\dms\application\modules\aktifitas\models\Aktifitas_model.php 75
ERROR - 2017-11-07 09:43:15 --> Severity: Error --> Call to undefined function gen_primaryD() C:\xampp\htdocs\project\dms\application\modules\aktifitas\models\Aktifitas_model.php 83
ERROR - 2017-11-07 09:45:05 --> Severity: Error --> Call to undefined function gen_primaryD() C:\xampp\htdocs\project\dms\application\modules\aktifitas\models\Aktifitas_model.php 76
ERROR - 2017-11-07 09:53:12 --> Could not find the language line "Setoran_title_edit"
