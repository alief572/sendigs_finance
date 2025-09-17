<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2017-11-30 04:12:49 --> Severity: Warning --> array_key_exists() expects parameter 2 to be array, string given C:\xampp\htdocs\project\dms\application\core\BF_Model.php 626
ERROR - 2017-11-30 04:12:49 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\application\core\BF_Model.php 628
ERROR - 2017-11-30 04:12:49 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\project\dms\system\database\DB_query_builder.php 1992
ERROR - 2017-11-30 04:14:59 --> Query error: Unknown column 'status_aktifX' in 'field list' - Invalid query: UPDATE `mitra` SET `nim` = CASE 
WHEN `mitra_id` = '0000000003' THEN ''
ELSE `nim` END, `nama_mitra` = CASE 
WHEN `mitra_id` = '0000000003' THEN 'Yunas Fandy Handra'
ELSE `nama_mitra` END, `tempatlahir` = CASE 
WHEN `mitra_id` = '0000000003' THEN 'Bogor'
ELSE `tempatlahir` END, `tanggallahir` = CASE 
WHEN `mitra_id` = '0000000003' THEN '2017-11-29'
ELSE `tanggallahir` END, `jeniskelamin` = CASE 
WHEN `mitra_id` = '0000000003' THEN 'L'
ELSE `jeniskelamin` END, `agama` = CASE 
WHEN `mitra_id` = '0000000003' THEN 'Islam'
ELSE `agama` END, `levelpendidikan` = CASE 
WHEN `mitra_id` = '0000000003' THEN 'SARJANA'
ELSE `levelpendidikan` END, `alamataktif` = CASE 
WHEN `mitra_id` = '0000000003' THEN 'CIbinong Bogor'
ELSE `alamataktif` END, `nohp` = CASE 
WHEN `mitra_id` = '0000000003' THEN '612351235123'
ELSE `nohp` END, `norekeningbank` = CASE 
WHEN `mitra_id` = '0000000003' THEN '23455236234'
ELSE `norekeningbank` END, `namabank` = CASE 
WHEN `mitra_id` = '0000000003' THEN 'BCA'
ELSE `namabank` END, `noktp` = CASE 
WHEN `mitra_id` = '0000000003' THEN '09121341234'
ELSE `noktp` END, `tanggalmasuk` = CASE 
WHEN `mitra_id` = '0000000003' THEN '1970-01-01'
ELSE `tanggalmasuk` END, `npwp` = CASE 
WHEN `mitra_id` = '0000000003' THEN '123412341234'
ELSE `npwp` END, `email` = CASE 
WHEN `mitra_id` = '0000000003' THEN ''
ELSE `email` END, `status_aktifX` = CASE 
WHEN `mitra_id` = '0000000003' THEN '1'
ELSE `status_aktifX` END, `modified_on` = CASE 
WHEN `mitra_id` = '0000000003' THEN '2017-11-30 04:14:59'
ELSE `modified_on` END, `modified_by` = CASE 
WHEN `mitra_id` = '0000000003' THEN '1'
ELSE `modified_by` END
WHERE `mitra_id` IN('0000000003')
ERROR - 2017-11-30 08:36:38 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/add_datapengalaman
ERROR - 2017-11-30 08:37:27 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/add_datapengalaman
ERROR - 2017-11-30 08:39:44 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/add_datapengalaman
ERROR - 2017-11-30 08:40:18 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/add_datapengalaman
ERROR - 2017-11-30 08:41:28 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/add_datapengalaman
ERROR - 2017-11-30 08:48:55 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/add_datapengalaman
ERROR - 2017-11-30 09:18:12 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/add_datapengalaman
ERROR - 2017-11-30 09:18:14 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/add_datapengalaman
ERROR - 2017-11-30 09:19:11 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/add_datapengalaman
ERROR - 2017-11-30 09:19:20 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/add_datapengalaman
ERROR - 2017-11-30 09:58:38 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/add_datapengalaman
ERROR - 2017-11-30 10:01:07 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/add_datapengalaman
ERROR - 2017-11-30 10:04:12 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/add_datapengalaman
ERROR - 2017-11-30 10:05:36 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/add_datapengalaman
ERROR - 2017-11-30 10:09:58 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/add_datapengalaman
ERROR - 2017-11-30 10:31:34 --> Query error: Table 'dms.mitra_id' doesn't exist - Invalid query: SELECT *
FROM `mitra_id`
WHERE `mitra_id` = '0000000002'
ERROR - 2017-11-30 10:33:53 --> Severity: Error --> Call to undefined function now() C:\xampp\htdocs\project\dms\application\modules\mitra\models\Mitra_model.php 93
ERROR - 2017-11-30 10:34:57 --> Severity: Parsing Error --> syntax error, unexpected '$data' (T_VARIABLE) C:\xampp\htdocs\project\dms\application\modules\mitra\models\Mitra_model.php 81
ERROR - 2017-11-30 10:35:37 --> Query error: Column 'bulanmasuk' cannot be null - Invalid query: INSERT INTO `mitra_cv` (`mitra_id`, `namaperusahaan`, `bidangusaha`, `bulanmasuk`, `bulankeluar`, `jabatan`, `gajiterakhir`, `masakerja`, `alasankeluar`, `alamatperusahaan`, `created_by`, `created_on`) VALUES ('0000000002', 'Agungrent', 'Automotive', NULL, NULL, 'IT SPV', '7000000', '5.083333333333333', 'Actualisasi Diri', 'Gondangdia', NULL, '2017-11-30 10:35:37')
ERROR - 2017-11-30 10:39:14 --> Severity: Parsing Error --> syntax error, unexpected ';', expecting ')' C:\xampp\htdocs\project\dms\application\modules\mitra\models\Mitra_model.php 92
ERROR - 2017-11-30 10:39:45 --> Severity: Error --> Call to a member function user_id() on null C:\xampp\htdocs\project\dms\application\modules\mitra\models\Mitra_model.php 92
ERROR - 2017-11-30 16:29:39 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\project\dms\application\modules\users\views\login.php 5
ERROR - 2017-11-30 16:29:39 --> 404 Page Not Found: /index
ERROR - 2017-11-30 16:29:39 --> 404 Page Not Found: /index
ERROR - 2017-11-30 16:29:39 --> 404 Page Not Found: /index
ERROR - 2017-11-30 16:29:44 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\project\dms\application\modules\users\views\login.php 5
ERROR - 2017-11-30 16:29:44 --> 404 Page Not Found: /index
ERROR - 2017-11-30 16:29:44 --> 404 Page Not Found: /index
ERROR - 2017-11-30 16:29:45 --> 404 Page Not Found: /index
ERROR - 2017-11-30 16:29:50 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\project\dms\application\modules\users\views\login.php 5
ERROR - 2017-11-30 16:29:51 --> 404 Page Not Found: /index
ERROR - 2017-11-30 16:29:51 --> 404 Page Not Found: /index
ERROR - 2017-11-30 16:34:46 --> Query error: Table 'dms.mitra_cvx' doesn't exist - Invalid query: SELECT
                mitra_cv.namaperusahaan,
                mitra_cv.bidangusaha,
                mitra_cv.blnthn_masuk,
                mitra_cv.blnthn_keluar,
                mitra_cv.jabatan,
                mitra_cv.masakerja
                FROM mitra_cvX WHERE mitra_id=0000000002
ERROR - 2017-11-30 16:35:07 --> Query error: Table 'dms.mitra_cvx' doesn't exist - Invalid query: SELECT
                mitra_cv.namaperusahaan,
                mitra_cv.bidangusaha,
                mitra_cv.blnthn_masuk,
                mitra_cv.blnthn_keluar,
                mitra_cv.jabatan,
                mitra_cv.masakerja
                FROM mitra_cvX WHERE mitra_id=0000000002
ERROR - 2017-11-30 16:47:07 --> Query error: Duplicate entry '' for key 'Index_3' - Invalid query: INSERT INTO `mitra_cv` (`mitra_id`, `namaperusahaan`, `bidangusaha`, `blnthn_masuk`, `blnthn_keluar`, `jabatan`, `gajiterakhir`, `masakerja`, `alasankeluar`, `alamatperusahaan`, `created_by`, `created_on`) VALUES ('0000000002', '', '', '', '', '', '', 'NaN', '', '', '1', '2017-11-30 16:47:07')
ERROR - 2017-11-30 16:51:47 --> Query error: Duplicate entry '' for key 'Index_3' - Invalid query: INSERT INTO `mitra_cv` (`mitra_id`, `namaperusahaan`, `bidangusaha`, `blnthn_masuk`, `blnthn_keluar`, `jabatan`, `gajiterakhir`, `masakerja`, `alasankeluar`, `alamatperusahaan`, `created_by`, `created_on`) VALUES ('0000000002', '', '', '', '', '', '', 'NaN', '', '', '1', '2017-11-30 16:51:47')
ERROR - 2017-11-30 16:51:55 --> Query error: Duplicate entry '' for key 'Index_3' - Invalid query: INSERT INTO `mitra_cv` (`mitra_id`, `namaperusahaan`, `bidangusaha`, `blnthn_masuk`, `blnthn_keluar`, `jabatan`, `gajiterakhir`, `masakerja`, `alasankeluar`, `alamatperusahaan`, `created_by`, `created_on`) VALUES ('0000000002', '', '', '', '', '', '', 'NaN', '', '', '1', '2017-11-30 16:51:55')
ERROR - 2017-11-30 16:53:57 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '[object HTMLCollection]' at line 8 - Invalid query: SELECT
                mitra_cv.namaperusahaan,
                mitra_cv.bidangusaha,
                mitra_cv.blnthn_masuk,
                mitra_cv.blnthn_keluar,
                mitra_cv.jabatan,
                mitra_cv.masakerja
                FROM mitra_cv WHERE mitra_id=[object HTMLCollection]
ERROR - 2017-11-30 16:54:07 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '[object HTMLCollection]' at line 8 - Invalid query: SELECT
                mitra_cv.namaperusahaan,
                mitra_cv.bidangusaha,
                mitra_cv.blnthn_masuk,
                mitra_cv.blnthn_keluar,
                mitra_cv.jabatan,
                mitra_cv.masakerja
                FROM mitra_cv WHERE mitra_id=[object HTMLCollection]
ERROR - 2017-11-30 16:56:53 --> Query error: Table 'dms.mitra_cvx' doesn't exist - Invalid query: SELECT
                mitra_cv.namaperusahaan,
                mitra_cv.bidangusaha,
                mitra_cv.blnthn_masuk,
                mitra_cv.blnthn_keluar,
                mitra_cv.jabatan,
                mitra_cv.masakerja
                FROM mitra_cvx WHERE mitra_id=0000000002
ERROR - 2017-11-30 16:58:30 --> Query error: Table 'dms.mitra_cvx' doesn't exist - Invalid query: SELECT
                mitra_cv.namaperusahaan,
                mitra_cv.bidangusaha,
                mitra_cv.blnthn_masuk,
                mitra_cv.blnthn_keluar,
                mitra_cv.jabatan,
                mitra_cv.masakerja
                FROM mitra_cvx WHERE mitra_id=0000000002
ERROR - 2017-11-30 17:00:00 --> Query error: Table 'dms.mitra_cvx' doesn't exist - Invalid query: SELECT
                mitra_cv.namaperusahaan,
                mitra_cv.bidangusaha,
                mitra_cv.blnthn_masuk,
                mitra_cv.blnthn_keluar,
                mitra_cv.jabatan,
                mitra_cv.masakerja
                FROM mitra_cvx WHERE mitra_id=0000000002
ERROR - 2017-11-30 17:00:04 --> Query error: Table 'dms.mitra_cvx' doesn't exist - Invalid query: SELECT
                mitra_cv.namaperusahaan,
                mitra_cv.bidangusaha,
                mitra_cv.blnthn_masuk,
                mitra_cv.blnthn_keluar,
                mitra_cv.jabatan,
                mitra_cv.masakerja
                FROM mitra_cvx WHERE mitra_id=0000000002
ERROR - 2017-11-30 17:00:13 --> Query error: Table 'dms.mitra_cvx' doesn't exist - Invalid query: SELECT
                mitra_cv.namaperusahaan,
                mitra_cv.bidangusaha,
                mitra_cv.blnthn_masuk,
                mitra_cv.blnthn_keluar,
                mitra_cv.jabatan,
                mitra_cv.masakerja
                FROM mitra_cvx WHERE mitra_id=0000000002
ERROR - 2017-11-30 17:00:51 --> Query error: Table 'dms.mitra_cvx' doesn't exist - Invalid query: SELECT
                mitra_cv.namaperusahaan,
                mitra_cv.bidangusaha,
                mitra_cv.blnthn_masuk,
                mitra_cv.blnthn_keluar,
                mitra_cv.jabatan,
                mitra_cv.masakerja
                FROM mitra_cvx WHERE mitra_id=0000000002
ERROR - 2017-11-30 17:07:51 --> Query error: Table 'dms.mitra_cvx' doesn't exist - Invalid query: DELETE FROM `mitra_cvx`
WHERE `nomor` = '8'
ERROR - 2017-11-30 17:24:37 --> Query error: Unknown column 'x' in 'where clause' - Invalid query: SELECT
                mitra_cv.nomor,
                mitra_cv.namaperusahaan,
                mitra_cv.bidangusaha,
                mitra_cv.blnthn_masuk,
                mitra_cv.blnthn_keluar,
                mitra_cv.jabatan,
                mitra_cv.masakerja
                FROM mitra_cv WHERE mitra_id=x
ERROR - 2017-11-30 17:27:16 --> Query error: Unknown column 'x' in 'where clause' - Invalid query: SELECT
                mitra_cv.nomor,
                mitra_cv.namaperusahaan,
                mitra_cv.bidangusaha,
                mitra_cv.blnthn_masuk,
                mitra_cv.blnthn_keluar,
                mitra_cv.jabatan,
                mitra_cv.masakerja
                FROM mitra_cv WHERE mitra_id=x
ERROR - 2017-11-30 17:28:44 --> Query error: Table 'dms.mitra_cvx' doesn't exist - Invalid query: INSERT INTO `mitra_cvx` (`mitra_id`, `namaperusahaan`, `bidangusaha`, `blnthn_masuk`, `blnthn_keluar`, `jabatan`, `gajiterakhir`, `masakerja`, `alasankeluar`, `alamatperusahaan`, `created_by`, `created_on`) VALUES ('0000000002', 'd', '', '', '', '', '', 'NaN', '', '', '1', '2017-11-30 17:28:44')
ERROR - 2017-11-30 17:32:03 --> Query error: Duplicate entry 's-' for key 'Index_3' - Invalid query: INSERT INTO `mitra_cv` (`mitra_id`, `namaperusahaan`, `bidangusaha`, `blnthn_masuk`, `blnthn_keluar`, `jabatan`, `gajiterakhir`, `masakerja`, `alasankeluar`, `alamatperusahaan`, `created_by`, `created_on`) VALUES ('', 's', '', '', '', '', '', 'NaN', '', '', '1', '2017-11-30 17:32:03')
ERROR - 2017-11-30 17:34:14 --> Query error: Cannot delete or update a parent row: a foreign key constraint fails (`dms`.`mitra_cv`, CONSTRAINT `FK_sdm_karyawancv_1` FOREIGN KEY (`mitra_id`) REFERENCES `mitra` (`mitra_id`) ON UPDATE CASCADE) - Invalid query: DELETE FROM `mitra`
WHERE `mitra_id` = '0000000002'
ERROR - 2017-11-30 17:55:02 --> Severity: Warning --> Creating default object from empty value C:\xampp\htdocs\project\dms\application\modules\mitra\views\mitra_form.php 58
ERROR - 2017-11-30 17:55:04 --> Severity: Warning --> Creating default object from empty value C:\xampp\htdocs\project\dms\application\modules\mitra\views\mitra_form.php 58
ERROR - 2017-11-30 18:56:42 --> Query error: Table 'dms.mitra_pendidikanx' doesn't exist - Invalid query: INSERT INTO `mitra_pendidikanX` (`mitra_id`, `levelpendidikan`, `spesialisasi`, `tahunlulus`, `namasekolah`, `created_by`, `created_on`) VALUES ('0000000003', 'SD', '-', '2000', 'SDN Cimande IV', '1', '2017-11-30 18:56:42')
ERROR - 2017-11-30 19:00:47 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'FROM mitra_pendidikan WHERE mitra_id=0000000003' at line 7 - Invalid query: SELECT
                mitra_pendidikan.nomor,
                mitra_pendidikan.levelpendidikan,
                mitra_pendidikan.spesialisasi,
                mitra_pendidikan.tahunlulus,
                mitra_pendidikan.namasekolah,
                FROM mitra_pendidikan WHERE mitra_id=0000000003
ERROR - 2017-11-30 19:01:48 --> Query error: Duplicate entry 'SDN CImande IV-2000---0000000003' for key 'Index_4' - Invalid query: INSERT INTO `mitra_pendidikan` (`mitra_id`, `levelpendidikan`, `spesialisasi`, `tahunlulus`, `namasekolah`, `created_by`, `created_on`) VALUES ('0000000003', 'SD', '-', '2000', 'SDN CImande IV', '1', '2017-11-30 19:01:48')
ERROR - 2017-11-30 19:50:49 --> Severity: Parsing Error --> syntax error, unexpected ';' C:\xampp\htdocs\project\dms\application\modules\mitra\controllers\Mitra.php 332
ERROR - 2017-11-30 19:54:00 --> Query error: Table 'dms.mitra_keluargax' doesn't exist - Invalid query: SELECT
                mitra_keluarga.nomor,
                mitra_keluarga.nama,
                mitra_keluarga.jeniskelamin,
                mitra_keluarga.tempatlahir,
                mitra_keluarga.tanggallahir,
                mitra_keluarga.levelpendidikan,
                mitra_keluarga.hubungankeluarga,
                mitra_keluarga.pekerjaan
                FROM mitra_keluargax WHERE mitra_id=0000000003
ERROR - 2017-11-30 19:58:33 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/add_datakeluarga
ERROR - 2017-11-30 20:07:10 --> Severity: Parsing Error --> syntax error, unexpected ''created_by'' (T_CONSTANT_ENCAPSED_STRING), expecting ')' C:\xampp\htdocs\project\dms\application\modules\mitra\models\Mitra_model.php 145
ERROR - 2017-11-30 20:07:10 --> Severity: Parsing Error --> syntax error, unexpected ''created_by'' (T_CONSTANT_ENCAPSED_STRING), expecting ')' C:\xampp\htdocs\project\dms\application\modules\mitra\models\Mitra_model.php 145
ERROR - 2017-11-30 20:08:33 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/hapus_temp_kel
ERROR - 2017-11-30 20:09:47 --> Query error: Table 'dms.hapus_temp_kel' doesn't exist - Invalid query: DELETE FROM `hapus_temp_kel`
WHERE `nomor` = '1'
ERROR - 2017-11-30 20:18:58 --> Query error: Duplicate entry '0000000003-Mulyasari-P-1987-05-12-Istri' for key 'index_1' - Invalid query: INSERT INTO `mitra_keluarga` (`mitra_id`, `nama`, `jeniskelamin`, `tempatlahir`, `tanggallahir`, `levelpendidikan`, `hubungankeluarga`, `pekerjaan`, `created_by`, `created_on`) VALUES ('0000000003', 'Mulyasari', 'P', 'Bogor', '1987-05-12', 'DIPLOMA', 'Istri', 'Ibu RT', '1', '2017-11-30 20:18:58')
ERROR - 2017-11-30 20:27:42 --> Query error: Table 'dms.mitra_keluargax' doesn't exist - Invalid query: DELETE FROM `mitra_keluargax`
WHERE `nomor` = '3'
ERROR - 2017-11-30 20:32:42 --> Query error: Duplicate entry '0000000003-s-L-2017-11-29-s' for key 'index_1' - Invalid query: INSERT INTO `mitra_keluarga` (`mitra_id`, `nama`, `jeniskelamin`, `tempatlahir`, `tanggallahir`, `levelpendidikan`, `hubungankeluarga`, `pekerjaan`, `created_by`, `created_on`) VALUES ('0000000003', 's', 'L', 'Bogor', '2017-11-29', 'SARJANA', 's', 's', '1', '2017-11-30 20:32:42')
ERROR - 2017-11-30 21:15:29 --> Severity: Parsing Error --> syntax error, unexpected 'if' (T_IF) C:\xampp\htdocs\project\dms\application\modules\mitra\controllers\Mitra.php 384
ERROR - 2017-11-30 21:16:04 --> Severity: Parsing Error --> syntax error, unexpected 'if' (T_IF), expecting ',' or ';' C:\xampp\htdocs\project\dms\application\modules\mitra\controllers\Mitra.php 384
ERROR - 2017-11-30 21:19:25 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'FROM mitra_sim WHERE mitra_id=0000000003' at line 9 - Invalid query: SELECT
                mitra_sim.nomor,
                mitra_sim.jenis_sim,
                mitra_sim.no_sim,
                mitra_sim.tgl_aktif,
                mitra_sim.tgl_nonaktif,
                mitra_sim.penerbit_sim,
                mitra_sim.status,
                FROM mitra_sim WHERE mitra_id=0000000003
ERROR - 2017-11-30 21:21:30 --> Query error: Duplicate entry '0' for key 'PRIMARY' - Invalid query: INSERT INTO `mitra_sim` (`mitra_id`, `jenis_sim`, `no_sim`, `tgl_aktif`, `tgl_nonaktif`, `penerbit_sim`, `status`, `created_by`, `created_on`) VALUES ('0000000003', 'C', 'v', '2017-12-02', '2017-12-05', 'v', '1', '1', '2017-11-30 21:21:30')
ERROR - 2017-11-30 21:47:11 --> Query error: Unknown column 'created_by' in 'field list' - Invalid query: INSERT INTO `mitra_kontak_urgent` (`mitra_id`, `nama`, `nokontak`, `alamat`, `hubungan`, `created_by`, `created_on`) VALUES (NULL, 'Ajis', '08765748343', 'CIbinong Bogor', 'Adik', '1', '2017-11-30 21:47:11')
ERROR - 2017-11-30 21:47:50 --> Query error: Column 'mitra_id' cannot be null - Invalid query: INSERT INTO `mitra_kontak_urgent` (`mitra_id`, `nama`, `nokontak`, `alamat`, `hubungan`, `created_by`, `created_on`) VALUES (NULL, 'Ajis', '08765748343', 'CIbinong Bogor', 'Adik', '1', '2017-11-30 21:47:50')
ERROR - 2017-11-30 21:49:23 --> Query error: Column 'mitra_id' cannot be null - Invalid query: INSERT INTO `mitra_kontak_urgent` (`mitra_id`, `nama`, `nokontak`, `alamat`, `hubungan`, `created_by`, `created_on`) VALUES (NULL, 'Ajis', '12352354', 'CIbinong Bogor', 'asdfa', '1', '2017-11-30 21:49:23')
ERROR - 2017-11-30 21:51:16 --> 404 Page Not Found: ../modules/mitra/controllers/Mitra/hapus_temp_urgen
ERROR - 2017-11-30 21:53:45 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\project\dms\application\modules\users\views\login.php 5
ERROR - 2017-11-30 21:53:45 --> 404 Page Not Found: /index
ERROR - 2017-11-30 21:53:45 --> 404 Page Not Found: /index
ERROR - 2017-11-30 21:54:06 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\project\dms\application\modules\users\views\login.php 5
ERROR - 2017-11-30 21:54:06 --> 404 Page Not Found: /index
ERROR - 2017-11-30 21:54:06 --> 404 Page Not Found: /index
ERROR - 2017-11-30 21:54:13 --> 404 Page Not Found: ../modules/kendaraan/controllers//index
ERROR - 2017-11-30 21:54:17 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\project\dms\application\modules\users\views\login.php 5
ERROR - 2017-11-30 21:54:17 --> 404 Page Not Found: /index
ERROR - 2017-11-30 21:54:17 --> 404 Page Not Found: /index
ERROR - 2017-11-30 21:54:25 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\project\dms\application\modules\users\views\login.php 5
ERROR - 2017-11-30 21:54:25 --> 404 Page Not Found: /index
ERROR - 2017-11-30 21:54:25 --> 404 Page Not Found: /index
