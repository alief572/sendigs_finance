<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2017-02-01 04:33:14 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\labgit\inventory\application\modules\users\views\login.php 5
ERROR - 2017-02-01 04:33:15 --> 404 Page Not Found: /index
ERROR - 2017-02-01 04:33:15 --> 404 Page Not Found: /index
ERROR - 2017-02-01 04:33:15 --> 404 Page Not Found: /index
ERROR - 2017-02-01 04:33:21 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:33:21 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:33:39 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:33:39 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:37:51 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:37:51 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:39:27 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:39:27 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:39:47 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:39:47 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:43:54 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:43:54 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:48:50 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:48:50 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:49:03 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:49:03 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:49:21 --> Query error: Table 'my_inventory.log_trasaksidt' doesn't exist - Invalid query: INSERT INTO log_trasaksidt
        SELECT
        log_transaksiht.notransaksi,
        log_podt.kodebarang,
        log_podt.satuan,
        log_podt.jumlahpesan,
        log_podt.jumlahpesan,
        log_podt.catatan,
        CONCAT('1') as status
        FROM
        log_podt
        INNER JOIN log_transaksiht ON log_transaksiht.nopo = log_podt.nopo WHERE log_podt.nopo='244/05/2014/PO/H0RO/HIP'
ERROR - 2017-02-01 04:53:14 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:53:14 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:53:31 --> Query error: Table 'my_inventory.log_transaksidtx' doesn't exist - Invalid query: INSERT INTO log_transaksidtX
        SELECT
        log_transaksiht.notransaksi,
        log_podt.kodebarang,
        log_podt.satuan,
        log_podt.jumlahpesan,
        log_podt.jumlahpesan,
        log_podt.catatan,
        CONCAT('1') as status
        FROM
        log_podt
        INNER JOIN log_transaksiht ON log_transaksiht.nopo = log_podt.nopo WHERE log_podt.nopo='244/05/2014/PO/H0RO/HIP'
ERROR - 2017-02-01 04:55:58 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:55:58 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:56:10 --> Query error: Cannot add or update a child row: a foreign key constraint fails (`my_inventory`.`log_transaksidt`, CONSTRAINT `log_transaksidt_ibfk_2` FOREIGN KEY (`kodebarang`) REFERENCES `log_5masterbarang` (`kodebarang`) ON UPDATE CASCADE) - Invalid query: INSERT INTO log_transaksidt
        SELECT
        log_transaksiht.notransaksi,
        log_podt.kodebarang,
        log_podt.satuan,
        log_podt.jumlahpesan,
        log_podt.jumlahpesan,
        log_podt.catatan,
        CONCAT('1') as status
        FROM
        log_podt
        INNER JOIN log_transaksiht ON log_transaksiht.nopo = log_podt.nopo WHERE log_podt.nopo='244/05/2014/PO/H0RO/HIP'
ERROR - 2017-02-01 04:56:20 --> Query error: Duplicate entry 'GR20170200002' for key 'PRIMARY' - Invalid query: INSERT INTO `log_transaksiht` (`tipetransaksi`, `notransaksi`, `tanggal`, `nopo`, `nosj`, `namapenerima`, `idsupplier`, `nofaktur`, `post`, `created_on`, `created_by`) VALUES ('2', 'GR20170200002', '2017-02-01', '244/05/2014/PO/H0RO/HIP', 'c', 'v', 'S001130783', 'c', 1, '2017-02-01 04:56:20', '1')
ERROR - 2017-02-01 04:56:48 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:56:48 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 04:57:08 --> Query error: Cannot add or update a child row: a foreign key constraint fails (`my_inventory`.`log_transaksidt`, CONSTRAINT `log_transaksidt_ibfk_2` FOREIGN KEY (`kodebarang`) REFERENCES `log_5masterbarang` (`kodebarang`) ON UPDATE CASCADE) - Invalid query: INSERT INTO log_transaksidt
        SELECT
        log_transaksiht.notransaksi,
        log_podt.kodebarang,
        log_podt.satuan,
        log_podt.jumlahpesan,
        log_podt.jumlahpesan,
        log_podt.catatan,
        CONCAT('1') as status
        FROM
        log_podt
        INNER JOIN log_transaksiht ON log_transaksiht.nopo = log_podt.nopo WHERE log_podt.nopo='244/05/2014/PO/H0RO/HIP'
ERROR - 2017-02-01 05:13:24 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:13:24 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:13:46 --> Query error: Unknown column 'deleted' in 'field list' - Invalid query: UPDATE `log_transaksiht` SET `deleted` = 1, `deleted_by` = '1'
WHERE `notransaksi` = 'GR20170200001'
ERROR - 2017-02-01 05:13:55 --> Query error: Duplicate entry 'GR20170200001' for key 'PRIMARY' - Invalid query: INSERT INTO `log_transaksiht` (`tipetransaksi`, `notransaksi`, `tanggal`, `nopo`, `nosj`, `namapenerima`, `idsupplier`, `nofaktur`, `post`, `created_on`, `created_by`) VALUES ('2', 'GR20170200001', '2017-02-01', '244/05/2014/PO/H0RO/HIP', 'z', 'z', 'S001130783', 'z', 1, '2017-02-01 05:13:55', '1')
ERROR - 2017-02-01 05:15:59 --> Query error: Unknown column 'deleted' in 'field list' - Invalid query: UPDATE `log_transaksiht` SET `deleted` = 1, `deleted_by` = '1'
WHERE `notransaksi` = 'GR20170200001'
ERROR - 2017-02-01 05:16:59 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:16:59 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:17:21 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:17:21 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:17:38 --> Query error: Unknown column 'deleted' in 'field list' - Invalid query: UPDATE `log_transaksiht` SET `deleted` = 1, `deleted_by` = '1'
WHERE `notransaksi` = 'GR20170200001'
ERROR - 2017-02-01 05:18:01 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:18:01 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:18:31 --> Query error: Unknown column 'deleted' in 'field list' - Invalid query: UPDATE `log_transaksiht` SET `deleted` = 1, `deleted_by` = '1'
WHERE `notransaksi` = 'GR20170200001'
ERROR - 2017-02-01 05:21:26 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:21:26 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:23:02 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:23:02 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:24:13 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:24:13 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:25:41 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:25:41 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:27:20 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:27:20 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:27:38 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:27:38 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:28:47 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:28:47 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:30:20 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:30:20 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:34:25 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:34:25 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:35:36 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:35:36 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:35:38 --> Severity: Notice --> Undefined property: CI::$kelompok_model C:\xampp\htdocs\labgit\inventory\application\third_party\MX\Controller.php 59
ERROR - 2017-02-01 05:35:39 --> Severity: Error --> Call to a member function where() on null C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 52
ERROR - 2017-02-01 05:35:40 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:35:40 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:36:39 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:36:39 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:39:21 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:39:21 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:39:32 --> Query error: Unknown column 'deleted' in 'field list' - Invalid query: UPDATE `log_transaksiht` SET `deleted` = 1, `deleted_by` = '1'
WHERE `notransaksi` = 'GR20170200001'
ERROR - 2017-02-01 05:39:56 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:39:56 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:40:02 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:40:02 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:40:20 --> Query error: Unknown column 'deleted' in 'field list' - Invalid query: UPDATE `log_transaksiht` SET `deleted` = 1, `deleted_by` = '1'
WHERE `notransaksi` = 'GR20170200001'
ERROR - 2017-02-01 05:44:08 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:44:08 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:45:23 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:45:23 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:45:40 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '* FROM log_transaksiht WHERE notransaksi='GR20170200001'' at line 1 - Invalid query: DELETE * FROM log_transaksiht WHERE notransaksi='GR20170200001'
ERROR - 2017-02-01 05:46:23 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:46:23 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:47:25 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:47:25 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:47:37 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 05:47:37 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:06:30 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\labgit\inventory\application\modules\users\views\login.php 5
ERROR - 2017-02-01 08:06:32 --> 404 Page Not Found: /index
ERROR - 2017-02-01 08:06:32 --> 404 Page Not Found: /index
ERROR - 2017-02-01 08:06:33 --> 404 Page Not Found: /index
ERROR - 2017-02-01 08:06:47 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:06:47 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:07:13 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:07:13 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:10:05 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:10:05 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:17:15 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:17:15 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:17:26 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:17:26 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:17:56 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:17:56 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:18:11 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:18:11 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:18:27 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:18:27 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:18:48 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:18:48 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:19:05 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:19:05 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:19:25 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:19:25 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:19:33 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:19:33 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:19:43 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:19:43 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:20:14 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:20:14 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:20:16 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:20:16 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:20:41 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:20:41 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:21:25 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:21:25 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:21:40 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:21:40 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:21:55 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:21:55 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:22:08 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:22:08 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:22:44 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:22:44 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:26:20 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:26:20 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:27:47 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:27:47 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:29:04 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:29:04 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:30:50 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:30:50 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:31:04 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:31:04 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:31:58 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:31:58 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:32:07 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:32:07 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:32:11 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:32:11 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:32:24 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:32:24 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:32:26 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:32:26 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:33:02 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:33:02 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:33:04 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:33:04 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:34:01 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:34:01 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:34:39 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:34:39 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:34:48 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:34:48 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:36:30 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:36:30 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:36:38 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:36:38 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:37:07 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:37:07 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:37:30 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:37:30 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:37:31 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:37:31 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:38:07 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:38:07 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:38:26 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:38:26 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:41:35 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:41:35 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:42:24 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:42:24 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:43:09 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:43:09 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:43:20 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:43:20 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:44:06 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:44:06 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:44:33 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:44:33 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:44:52 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:44:52 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:45:09 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:45:09 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:45:25 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:45:25 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:45:31 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:45:31 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:46:13 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:46:13 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:46:48 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:46:48 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:47:24 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:47:24 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:48:03 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:48:03 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:48:31 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:48:31 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:48:51 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:48:51 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:49:02 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:49:02 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:49:52 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:49:52 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:51:02 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:51:02 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:51:35 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:51:35 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:52:05 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:52:05 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:52:33 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:52:33 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:53:32 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:53:32 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:54:05 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:54:05 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:54:06 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:54:06 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:54:29 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:54:29 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:55:21 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:55:21 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:55:22 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:55:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:55:31 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 08:55:31 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 09:08:00 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 09:08:00 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 09:08:02 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 09:08:02 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 09:16:51 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 09:16:51 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 09:17:33 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 59
ERROR - 2017-02-01 09:17:33 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 59
ERROR - 2017-02-01 09:17:34 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 59
ERROR - 2017-02-01 09:17:34 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 59
ERROR - 2017-02-01 09:17:35 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 59
ERROR - 2017-02-01 09:17:35 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 59
ERROR - 2017-02-01 09:17:36 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 59
ERROR - 2017-02-01 09:17:36 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 59
ERROR - 2017-02-01 09:17:37 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 59
ERROR - 2017-02-01 09:17:37 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 59
ERROR - 2017-02-01 09:17:37 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 59
ERROR - 2017-02-01 09:17:37 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 59
ERROR - 2017-02-01 09:18:25 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 59
ERROR - 2017-02-01 09:18:25 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 59
ERROR - 2017-02-01 09:18:47 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 61
ERROR - 2017-02-01 09:18:47 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 61
ERROR - 2017-02-01 09:19:16 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 61
ERROR - 2017-02-01 09:19:16 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 61
ERROR - 2017-02-01 09:20:27 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 60
ERROR - 2017-02-01 09:20:27 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 60
ERROR - 2017-02-01 09:20:47 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 60
ERROR - 2017-02-01 09:20:47 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 60
ERROR - 2017-02-01 09:20:48 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 60
ERROR - 2017-02-01 09:20:48 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 60
ERROR - 2017-02-01 09:20:49 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 60
ERROR - 2017-02-01 09:20:49 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 60
ERROR - 2017-02-01 09:20:50 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 60
ERROR - 2017-02-01 09:20:50 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\views\Laporan_Penerimaan_form.php 60
ERROR - 2017-02-01 09:25:21 --> 404 Page Not Found: ../modules/lap_pengajuan/controllers//index
ERROR - 2017-02-01 09:31:54 --> Query error: Table 'my_inventory.laporan_penerimaan_barangx' doesn't exist - Invalid query: SELECT * FROM laporan_penerimaan_barangX
         WHERE tanggal BETWEEN '01-02-2017' and '01-02-2017'
ERROR - 2017-02-01 09:33:14 --> Severity: Notice --> Undefined property: stdClass::$jumlahpesan C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\controllers\Lap_penerimaan.php 132
ERROR - 2017-02-01 09:33:14 --> Severity: Notice --> Undefined property: stdClass::$catatan C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\controllers\Lap_penerimaan.php 133
ERROR - 2017-02-01 09:33:14 --> Severity: Notice --> Undefined property: stdClass::$jumlahpesan C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\controllers\Lap_penerimaan.php 132
ERROR - 2017-02-01 09:33:14 --> Severity: Notice --> Undefined property: stdClass::$catatan C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\controllers\Lap_penerimaan.php 133
ERROR - 2017-02-01 09:33:14 --> Severity: Notice --> Undefined property: stdClass::$jumlahpesan C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\controllers\Lap_penerimaan.php 132
ERROR - 2017-02-01 09:33:14 --> Severity: Notice --> Undefined property: stdClass::$catatan C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\controllers\Lap_penerimaan.php 133
ERROR - 2017-02-01 11:07:02 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 11:07:02 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 11:07:32 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 11:07:32 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 11:11:47 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 11:11:47 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 11:11:49 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 11:11:49 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 11:11:53 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 11:11:53 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 11:12:20 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 11:12:20 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 11:12:22 --> Could not find the language line "tanggal_dibutuhkan"
ERROR - 2017-02-01 11:12:22 --> Could not find the language line "tanggal_dibutuhkan"
ERROR - 2017-02-01 11:12:22 --> Severity: Notice --> Undefined variable: kode_barang C:\xampp\htdocs\labgit\inventory\application\modules\pengajuan\views\Pengajuan_form.php 75
ERROR - 2017-02-01 11:12:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\pengajuan\views\Pengajuan_form.php 75
ERROR - 2017-02-01 11:12:25 --> Severity: Notice --> Undefined variable: kode_barang C:\xampp\htdocs\labgit\inventory\application\modules\pengeluaran\views\Pengeluaran_form.php 75
ERROR - 2017-02-01 11:12:25 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\pengeluaran\views\Pengeluaran_form.php 75
ERROR - 2017-02-01 11:12:25 --> Could not find the language line "pengajuan_btn_new_barang"
ERROR - 2017-02-01 11:12:27 --> Could not find the language line "tanggal_dibutuhkan"
ERROR - 2017-02-01 11:12:27 --> Could not find the language line "tanggal_dibutuhkan"
ERROR - 2017-02-01 11:14:00 --> 404 Page Not Found: ../modules/lap_pengajuan/controllers//index
ERROR - 2017-02-01 11:14:06 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 11:14:06 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 11:15:40 --> 404 Page Not Found: ../modules/lap_pengajuan/controllers//index
ERROR - 2017-02-01 11:21:10 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\labgit\inventory\application\modules\users\views\login.php 5
ERROR - 2017-02-01 11:21:11 --> 404 Page Not Found: /index
ERROR - 2017-02-01 11:21:11 --> 404 Page Not Found: /index
ERROR - 2017-02-01 11:21:11 --> 404 Page Not Found: /index
ERROR - 2017-02-01 11:22:15 --> 404 Page Not Found: ../modules/lap_pengajuan/controllers//index
ERROR - 2017-02-01 11:24:05 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\labgit\inventory\application\modules\users\views\login.php 5
ERROR - 2017-02-01 11:24:05 --> 404 Page Not Found: /index
ERROR - 2017-02-01 11:24:05 --> 404 Page Not Found: /index
ERROR - 2017-02-01 11:24:05 --> 404 Page Not Found: /index
ERROR - 2017-02-01 11:24:40 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:24:40 --> Severity: Notice --> Undefined property: CI::$Lap_penerimaan_model C:\xampp\htdocs\labgit\inventory\application\third_party\MX\Controller.php 59
ERROR - 2017-02-01 11:24:40 --> Severity: Error --> Call to a member function where() on null C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\controllers\Lap_penerimaan.php 28
ERROR - 2017-02-01 11:25:06 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:25:11 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:25:50 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:26:02 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:26:58 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:27:14 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:27:33 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:27:48 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:27:59 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:28:00 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:28:05 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\labgit\inventory\application\modules\users\views\login.php 5
ERROR - 2017-02-01 11:28:05 --> 404 Page Not Found: /index
ERROR - 2017-02-01 11:28:05 --> 404 Page Not Found: /index
ERROR - 2017-02-01 11:28:06 --> 404 Page Not Found: /index
ERROR - 2017-02-01 11:28:11 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:28:17 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:29:31 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:29:32 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:29:37 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:30:15 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:30:57 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:31:01 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:31:25 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:34:43 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:35:21 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:37:35 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:37:45 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:40:14 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:40:35 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:43:30 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:44:07 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:44:11 --> 404 Page Not Found: ../modules/lap_pengajuan/controllers//index
ERROR - 2017-02-01 11:46:20 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:46:21 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:46:30 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:46:56 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:46:57 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:47:16 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:47:17 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:47:17 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:47:17 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:47:23 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:48:43 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:48:44 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:48:45 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:49:01 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:49:04 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:49:05 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:49:24 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:49:25 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:49:27 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:49:37 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:49:44 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:49:52 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:50:19 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:50:42 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:50:43 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:50:49 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:50:58 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:51:28 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:51:58 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:52:04 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:52:20 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:52:44 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:52:51 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:53:26 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:53:27 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:54:09 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:54:47 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:54:48 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:54:54 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:55:19 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:57:24 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:57:25 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:57:27 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:58:35 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:58:36 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:58:42 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:58:42 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:58:43 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 11:58:44 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:06:52 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:06:53 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:07:15 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:07:43 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:08:25 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:08:48 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:08:55 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:09:09 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:09:27 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:09:32 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:09:46 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:09:54 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:10:22 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:10:23 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:10:33 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:10:34 --> 404 Page Not Found: ../modules/lap_pengajuan/controllers//index
ERROR - 2017-02-01 12:11:27 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:11:48 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:11:49 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:11:52 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:14:39 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:14:42 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:14:43 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:15:07 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:15:08 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:15:14 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 12:15:14 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 12:15:17 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:15:35 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:15:37 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:15:56 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:16:40 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:16:42 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:16:50 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:17:16 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:17:17 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:17:37 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:17:55 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:41:10 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:41:21 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:41:21 --> Severity: Notice --> Undefined index: tanggal2 C:\xampp\htdocs\labgit\inventory\application\modules\lap_penerimaan\controllers\Lap_penerimaan.php 39
ERROR - 2017-02-01 12:42:18 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:42:36 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:42:43 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:43:47 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:44:12 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:44:22 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:44:37 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:44:43 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:45:10 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:45:51 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:46:16 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:48:07 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:48:15 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:49:39 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:50:16 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:50:17 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:50:22 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:50:43 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:50:45 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:50:49 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:52:42 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:53:27 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:53:35 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 12:53:35 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 12:53:41 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:53:54 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:54:38 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 12:54:40 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:04:00 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:04:21 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:04:32 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:06:13 --> Severity: Notice --> Undefined variable: kode_barang C:\xampp\htdocs\labgit\inventory\application\modules\pengeluaran\views\Pengeluaran_form.php 75
ERROR - 2017-02-01 13:06:13 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\pengeluaran\views\Pengeluaran_form.php 75
ERROR - 2017-02-01 13:06:13 --> Could not find the language line "pengajuan_btn_new_barang"
ERROR - 2017-02-01 13:12:55 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:13:04 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:13:56 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:14:32 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:18:03 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:18:08 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:18:14 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:20:31 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:21:48 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:21:49 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:21:56 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:22:29 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:22:30 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:22:31 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:22:37 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:23:17 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:23:22 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:23:35 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:23:41 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 13:59:49 --> Severity: Notice --> Undefined property: stdClass::$jumlah C:\xampp\htdocs\labgit\inventory\application\modules\lap_pengeluaran\controllers\Lap_pengeluaran.php 64
ERROR - 2017-02-01 13:59:49 --> Severity: Notice --> Undefined property: stdClass::$penerima C:\xampp\htdocs\labgit\inventory\application\modules\lap_pengeluaran\controllers\Lap_pengeluaran.php 65
ERROR - 2017-02-01 13:59:49 --> Severity: Notice --> Undefined property: stdClass::$jumlah C:\xampp\htdocs\labgit\inventory\application\modules\lap_pengeluaran\controllers\Lap_pengeluaran.php 64
ERROR - 2017-02-01 13:59:49 --> Severity: Notice --> Undefined property: stdClass::$penerima C:\xampp\htdocs\labgit\inventory\application\modules\lap_pengeluaran\controllers\Lap_pengeluaran.php 65
ERROR - 2017-02-01 13:59:49 --> Severity: Notice --> Undefined property: stdClass::$jumlah C:\xampp\htdocs\labgit\inventory\application\modules\lap_pengeluaran\controllers\Lap_pengeluaran.php 64
ERROR - 2017-02-01 13:59:49 --> Severity: Notice --> Undefined property: stdClass::$penerima C:\xampp\htdocs\labgit\inventory\application\modules\lap_pengeluaran\controllers\Lap_pengeluaran.php 65
ERROR - 2017-02-01 14:03:09 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 14:03:17 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 14:04:45 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 14:04:51 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 14:06:52 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 14:16:17 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:03:35 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\labgit\inventory\application\modules\users\views\login.php 5
ERROR - 2017-02-01 15:03:35 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:03:35 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:03:35 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:03:38 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 15:03:47 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 15:34:17 --> Could not find the language line "lap_penerimaan_title_manage"
ERROR - 2017-02-01 15:34:28 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:34:28 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:34:35 --> Severity: Notice --> Undefined property: CI::$kelompok_model C:\xampp\htdocs\labgit\inventory\application\third_party\MX\Controller.php 59
ERROR - 2017-02-01 15:34:35 --> Severity: Error --> Call to a member function where() on null C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 52
ERROR - 2017-02-01 15:34:44 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:34:44 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:38:42 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:38:42 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:38:44 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:38:44 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:38:45 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:38:45 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:38:45 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:38:45 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:38:46 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:38:46 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:38:46 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:38:46 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:38:47 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:38:47 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:38:48 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:38:48 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:39:43 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:39:43 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:39:46 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:39:46 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:39:58 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:39:58 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:40:11 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:40:11 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:40:14 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:40:14 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:40:14 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:40:14 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:40:15 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:40:15 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:40:49 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:40:49 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:42:52 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:42:52 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:42:54 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:42:54 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:42:55 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:42:55 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:42:55 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:42:55 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:42:55 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:42:55 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:42:56 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:42:56 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:42:57 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:42:57 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:20 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:44:20 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:44:21 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:21 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:22 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:22 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:22 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:22 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:22 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:22 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:23 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:23 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:23 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:23 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:23 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:23 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:23 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:23 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:23 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:23 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:24 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:24 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:24 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:24 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:24 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:24 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:25 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:25 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:25 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:25 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:26 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:26 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:27 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:27 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:28 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:28 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:28 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:44:28 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:46:09 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:46:09 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:46:11 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:46:12 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:46:13 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:46:13 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:46:13 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:46:13 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:46:13 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:46:14 --> 404 Page Not Found: /index
ERROR - 2017-02-01 15:48:49 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:48:49 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:52:10 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:52:10 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 15:52:11 --> 404 Page Not Found: /index
ERROR - 2017-02-01 16:23:03 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\labgit\inventory\application\modules\users\views\login.php 5
ERROR - 2017-02-01 16:23:03 --> 404 Page Not Found: /index
ERROR - 2017-02-01 16:23:03 --> 404 Page Not Found: /index
ERROR - 2017-02-01 16:23:03 --> 404 Page Not Found: /index
ERROR - 2017-02-01 16:23:05 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:23:05 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:23:07 --> 404 Page Not Found: /index
ERROR - 2017-02-01 16:24:13 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:24:13 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:24:32 --> 404 Page Not Found: /index
ERROR - 2017-02-01 16:25:50 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:25:50 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:28:09 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:28:09 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:28:55 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:28:55 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:30:55 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:30:55 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:31:04 --> 404 Page Not Found: /index
ERROR - 2017-02-01 16:31:33 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:31:33 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:31:36 --> 404 Page Not Found: /index
ERROR - 2017-02-01 16:32:26 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:32:26 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:32:27 --> 404 Page Not Found: /index
ERROR - 2017-02-01 16:32:58 --> 404 Page Not Found: /index
ERROR - 2017-02-01 16:33:04 --> 404 Page Not Found: /index
ERROR - 2017-02-01 16:37:55 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:37:55 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:37:57 --> Severity: Notice --> Undefined index: nopo C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 119
ERROR - 2017-02-01 16:38:56 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:38:56 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:38:58 --> Severity: Notice --> Undefined index: nopo C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 119
ERROR - 2017-02-01 16:39:57 --> Severity: Notice --> Undefined index: nopo C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 119
ERROR - 2017-02-01 16:40:07 --> Severity: Notice --> Undefined index: nopo C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 119
ERROR - 2017-02-01 16:41:34 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:41:34 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:41:35 --> Severity: Notice --> Undefined index: nopo C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 119
ERROR - 2017-02-01 16:44:47 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:44:47 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:44:49 --> Severity: Error --> Call to a member function WriteHTML() on null C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 190
ERROR - 2017-02-01 16:45:17 --> Severity: Error --> Call to a member function WriteHTML() on null C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 190
ERROR - 2017-02-01 16:45:22 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:45:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:45:25 --> Severity: Error --> Call to a member function WriteHTML() on null C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 190
ERROR - 2017-02-01 16:46:44 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:46:44 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:46:47 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:46:47 --> Severity: Error --> Call to a member function WriteHTML() on null C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 190
ERROR - 2017-02-01 16:47:23 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:47:23 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:47:25 --> Severity: Error --> Call to a member function Output() on null C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 191
ERROR - 2017-02-01 16:47:37 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:47:37 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:50:50 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\labgit\inventory\application\modules\users\views\login.php 5
ERROR - 2017-02-01 16:50:50 --> 404 Page Not Found: /index
ERROR - 2017-02-01 16:50:50 --> 404 Page Not Found: /index
ERROR - 2017-02-01 16:50:50 --> 404 Page Not Found: /index
ERROR - 2017-02-01 16:50:52 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:50:52 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:50:56 --> Severity: Warning --> Missing argument 1 for Penerimaan::test() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 157
ERROR - 2017-02-01 16:50:56 --> Severity: Notice --> Undefined variable: nopo C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 158
ERROR - 2017-02-01 16:50:56 --> Severity: Notice --> Undefined variable: nopo C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 170
ERROR - 2017-02-01 16:51:29 --> Severity: Warning --> Missing argument 1 for Penerimaan::test() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 157
ERROR - 2017-02-01 16:51:29 --> Severity: Notice --> Undefined variable: nopo C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 158
ERROR - 2017-02-01 16:51:29 --> Severity: Notice --> Undefined variable: nopo C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 170
ERROR - 2017-02-01 16:51:56 --> Severity: Warning --> Missing argument 1 for Penerimaan::test() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 157
ERROR - 2017-02-01 16:51:56 --> Severity: Notice --> Undefined variable: nopo C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 158
ERROR - 2017-02-01 16:51:56 --> Severity: Notice --> Undefined variable: nopo C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 170
ERROR - 2017-02-01 16:52:01 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:52:01 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:52:03 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:52:03 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:52:10 --> Severity: Notice --> Undefined index: nopo C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 119
ERROR - 2017-02-01 16:52:26 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:52:26 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:53:42 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:53:42 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:53:45 --> Severity: Error --> Call to a member function WriteHTML() on null C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 227
ERROR - 2017-02-01 16:56:22 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:56:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:56:24 --> Severity: Error --> Call to a member function WriteHTML() on null C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 227
ERROR - 2017-02-01 16:57:07 --> Severity: Notice --> Undefined variable: nomor_po C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:57:07 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 16:57:09 --> Severity: Error --> Call to a member function WriteHTML() on null C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 227
ERROR - 2017-02-01 16:59:46 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 17:00:22 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 17:00:53 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 17:00:55 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 17:02:06 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 17:15:45 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 55
ERROR - 2017-02-01 17:16:10 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 17:16:53 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 55
ERROR - 2017-02-01 17:17:14 --> Severity: Error --> Call to undefined method MY_Loader::render() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 225
ERROR - 2017-02-01 17:18:53 --> Severity: Parsing Error --> syntax error, unexpected 'echo' (T_ECHO) C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 225
ERROR - 2017-02-01 17:21:08 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 55
ERROR - 2017-02-01 17:21:59 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 55
ERROR - 2017-02-01 17:24:39 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 16
ERROR - 2017-02-01 17:25:58 --> Query error: Unknown column 'log_podt.nopoX' in 'field list' - Invalid query: SELECT
        log_podt.nopoX,
        log_podt.kodebarang,
        log_podt.jumlahpesan,
        log_podt.satuan,
        log_podt.catatan
        FROM
        log_poht
        INNER JOIN log_podt ON log_podt.nopo = log_poht.nopo WHERE log_podt.nopo='GR20170200001'
ERROR - 2017-02-01 17:29:00 --> Query error: Unknown column 'notransakai' in 'where clause' - Invalid query: SELECT * FROM
        laporan_penerimaan_barang
        WHERE notransakai='GR20170200001'
ERROR - 2017-02-01 17:29:28 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 15
ERROR - 2017-02-01 17:29:49 --> Query error: Table 'my_inventory.laporan_penerimaan_barangx' doesn't exist - Invalid query: SELECT * FROM
        laporan_penerimaan_barangX
        WHERE notransaksi='GR20170200001'
ERROR - 2017-02-01 17:30:41 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 15
ERROR - 2017-02-01 17:31:06 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 15
ERROR - 2017-02-01 17:31:43 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 15
ERROR - 2017-02-01 17:32:32 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 16
ERROR - 2017-02-01 17:33:12 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 16
ERROR - 2017-02-01 17:44:14 --> Severity: Warning --> include(../mpdf.php): failed to open stream: No such file or directory C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 478
ERROR - 2017-02-01 17:44:14 --> Severity: Warning --> include(): Failed opening '../mpdf.php' for inclusion (include_path='.;C:\xampp\php\PEAR') C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 478
ERROR - 2017-02-01 17:44:14 --> Severity: Warning --> file_get_contents(mpdfstyletables.css): failed to open stream: No such file or directory C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 487
ERROR - 2017-02-01 17:44:14 --> 404 Page Not Found: /index
ERROR - 2017-02-01 17:44:14 --> 404 Page Not Found: /index
ERROR - 2017-02-01 17:44:14 --> 404 Page Not Found: /index
ERROR - 2017-02-01 17:44:14 --> 404 Page Not Found: /index
ERROR - 2017-02-01 17:44:14 --> 404 Page Not Found: /index
ERROR - 2017-02-01 17:44:14 --> 404 Page Not Found: /index
ERROR - 2017-02-01 17:44:14 --> 404 Page Not Found: /index
ERROR - 2017-02-01 17:44:14 --> 404 Page Not Found: /index
ERROR - 2017-02-01 17:45:50 --> Severity: Error --> Call to a member function result() on null C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 30
ERROR - 2017-02-01 17:45:50 --> 404 Page Not Found: /index
ERROR - 2017-02-01 17:46:13 --> 404 Page Not Found: /index
ERROR - 2017-02-01 17:47:48 --> Severity: Parsing Error --> syntax error, unexpected end of file C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\controllers\Penerimaan.php 204
ERROR - 2017-02-01 17:52:44 --> 404 Page Not Found: /index
ERROR - 2017-02-01 17:54:00 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 16
ERROR - 2017-02-01 17:54:59 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 16
ERROR - 2017-02-01 17:55:41 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 16
ERROR - 2017-02-01 18:04:56 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 18:14:49 --> 404 Page Not Found: /index
ERROR - 2017-02-01 18:14:49 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:45:41 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\labgit\inventory\application\modules\users\views\login.php 5
ERROR - 2017-02-01 23:45:41 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:45:41 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:45:41 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:45:51 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\labgit\inventory\application\modules\users\views\login.php 5
ERROR - 2017-02-01 23:45:52 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:45:52 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:45:53 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\Penerimaan_form.php 59
ERROR - 2017-02-01 23:45:56 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:45:56 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:49:09 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:49:09 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:51:26 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:51:26 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:51:53 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:51:53 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:54:24 --> Severity: Warning --> Invalid argument supplied for foreach() C:\xampp\htdocs\labgit\inventory\application\modules\penerimaan\views\print.php 24
ERROR - 2017-02-01 23:54:24 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:54:24 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:54:40 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:54:40 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:54:56 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:54:56 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:55:05 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:55:05 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:55:43 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:55:43 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:56:01 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:56:01 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:57:04 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:57:04 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:57:25 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:57:25 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:59:09 --> 404 Page Not Found: /index
ERROR - 2017-02-01 23:59:09 --> 404 Page Not Found: /index
