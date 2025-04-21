<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2017-12-06 02:45:17 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\project\dms\application\modules\users\views\login.php 5
ERROR - 2017-12-06 02:45:19 --> 404 Page Not Found: /index
ERROR - 2017-12-06 02:45:19 --> 404 Page Not Found: /index
ERROR - 2017-12-06 02:45:19 --> 404 Page Not Found: /index
ERROR - 2017-12-06 09:47:41 --> Severity: Error --> Call to a member function SetDisplayMode() on null C:\xampp\htdocs\project\dms\application\modules\mitra\controllers\Mitra.php 585
ERROR - 2017-12-06 09:48:12 --> Severity: Warning --> file_get_contents(&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;title&gt;Print Invoice&lt;/title&gt;
    &lt;style&gt;
        *
        {
            margin:0;
            padding:0;
            font-family:Arial;
            font-size:10pt;
            color:#000;
        }
        body
        {
            width:100%;
            font-family:Arial;
            font-size:10pt;
            margin:0;
            padding:0;
        }
         
        p
        {
            margin:0;
            padding:0;
        }
         
        #wrapper
        {
            width:180mm;
            margin:0 15mm;
        }
         
        .page
        {
            height:297mm;
            width:210mm;
            page-break-after:always;
        }
 
        table
        {
            border-left: 1px solid #ccc;
            border-top: 1px solid #ccc;
             
            border-spacing:0;
            border-collapse: collapse; 
             
        }
         
        table td 
        {
            border-right: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            padding: 2mm;
        }
         
        table.heading
        {
            height:50mm;
        }
         
        h1.heading
        {
            font-size:14pt;
            color:#000;
            font-weight:normal;
        }
         
        h2.heading
        {
            font-size:9pt;
            color:#000;
            font-weight:normal;
        }
         
        hr
        {
            color:#ccc;
            background:#ccc;
        }
         
        #invoice_body
        {
            height: 149mm;
        }
         
        #invoice_body , #invoice_total
        {   
            width:100%;
        }
        #invoice_body table , #invoice_total table
        {
            width:100%;
            border-left: 1px solid #ccc;
            border-top: 1px solid #ccc;
     
            border-spacing:0;
            border-collapse: collapse; 
             
            margin-top:5mm;
        }
         
        #invoice_body table td , #invoice_total table td
        {
            text-align:center;
            font-size:9pt;
            border-right: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            padding:2mm 0;
        }
         
        #invoice_body table td.mono  , #invoice_total table td.mono
        {
            font-family:monospace;
            text-align:right;
            padding-right:3mm;
            font-size:10pt;
        }
         
        #footer
        {   
            width:180mm;
            margin:0 15mm;
            padding-bottom:3mm;
        }
        #footer table
        {
            width:100%;
            border-left: 1px solid #ccc;
            border-top: 1px solid #ccc;
             
            background:#eee;
             
            border-spacing:0;
            border-collapse: collapse; 
        }
        #footer table td
        {
            width:25%;
            text-align:center;
            font-size:9pt;
            border-right: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
        }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
&lt;div id=&quot;wrapper&quot;&gt;
     
    &lt;p style=&quot;text-align:center; font-weight:bold; padding-top:5mm;&quot;&gt;INVOICE&lt;/p&gt;
    &lt;br /&gt;
    &lt;table class=&quot;heading&quot; style=&quot;width:100%;&quot;&gt;
        &lt;tr&gt;
            &lt;td style=&quot;width:80mm;&quot;&gt;
                &lt;h1 class=&quot;heading&quot;&gt;ABC Corp&lt;/h1&gt;
                &lt;h2 class=&quot;heading&quot;&gt;
                    123 Happy Street&lt;br /&gt;
                    CoolCity - Pincode&lt;br /&gt;
                    Region , Country&lt;br /&gt;
                     
                    Website : www.website.com&lt;br /&gt;
                    E-mail : info@website.com&lt;br /&gt;
                    Phone : +1 - 123456789
                &lt;/h2&gt;
            &lt;/td&gt;
            &lt;td rowspan=&quot;2&quot; valign=&quot;top&quot; align=&quot;right&quot; style=&quot;padding:3mm;&quot;&gt;
                &lt;table&gt;
                    &lt;tr&gt;&lt;td&gt;Invoice No : &lt;/td&gt;&lt;td&gt;11-12-17&lt;/td&gt;&lt;/tr&gt;
                    &lt;tr&gt;&lt;td&gt;Dated : &lt;/td&gt;&lt;td&gt;01-Aug-2011&lt;/td&gt;&lt;/tr&gt;
                    &lt;tr&gt;&lt;td&gt;Currency : &lt;/td&gt;&lt;td&gt;USD&lt;/td&gt;&lt;/tr&gt;
                &lt;/table&gt;
            &lt;/td&gt;
        &lt;/tr&gt;
        &lt;tr&gt;
            &lt;td&gt;
                &lt;b&gt;Buyer&lt;/b&gt; :&lt;br /&gt;
                Client Name&lt;br /&gt;
            Client Address
                &lt;br /&gt;
                City - Pincode , Country&lt;br /&gt;
            &lt;/td&gt;
        &lt;/tr&gt;
    &lt;/table&gt;
         
         
    &lt;div id=&quot;content&quot;&gt;
         
        &lt;div id=&quot;invoice_body&quot;&gt;
            &lt;table&gt;
            &lt;tr style=&quot;background:#eee;&quot;&gt;
                &lt;td style=&quot;width:8%;&quot;&gt;&lt;b&gt;Sl. No.&lt;/b&gt;&lt;/td&gt;
                &lt;td&gt;&lt;b&gt;Product&lt;/b&gt;&lt;/td&gt;
                &lt;td style=&quot;width:15%;&quot;&gt;&lt;b&gt;Quantity&lt;/b&gt;&lt;/td&gt;
                &lt;td style=&quot;width:15%;&quot;&gt;&lt;b&gt;Rate&lt;/b&gt;&lt;/td&gt;
                &lt;td style=&quot;width:15%;&quot;&gt;&lt;b&gt;Total&lt;/b&gt;&lt;/td&gt;
            &lt;/tr&gt;
            &lt;/table&gt;
             
            &lt;table&gt;
            &lt;tr&gt;
                &lt;td style=&quot;width:8%;&quot;&gt;1&lt;/td&gt;
                &lt;td style=&quot;text-align:left; padding-left:10px;&quot;&gt;Software Development&lt;br /&gt;Description : Upgradation of telecrm&lt;/td&gt;
                &lt;td class=&quot;mono&quot; style=&quot;width:15%;&quot;&gt;1&lt;/td&gt;&lt;td style=&quot;width:15%;&quot; class=&quot;mono&quot;&gt;157.00&lt;/td&gt;
                &lt;td style=&quot;width:15%;&quot; class=&quot;mono&quot;&gt;157.00&lt;/td&gt;
            &lt;/tr&gt;         
            &lt;tr&gt;
                &lt;td colspan=&quot;3&quot;&gt;&lt;/td&gt;
                &lt;td&gt;&lt;/td&gt;
                &lt;td&gt;&lt;/td&gt;
            &lt;/tr&gt;
             
            &lt;tr&gt;
                &lt;td colspan=&quot;3&quot;&gt;&lt;/td&gt;
                &lt;td&gt;Total :&lt;/td&gt;
                &lt;td class=&quot;mono&quot;&gt;157.00&lt;/td&gt;
            &lt;/tr&gt;
        &lt;/table&gt;
        &lt;/div&gt;
        &lt;div id=&quot;invoice_total&quot;&gt;
            Total Amount :
            &lt;table&gt;
                &lt;tr&gt;
                    &lt;td style=&quot;text-align:left; padding-left:10px;&quot;&gt;One  Hundred And Fifty Seven  only&lt;/td&gt;
                    &lt;td style=&quot;width:15%;&quot;&gt;USD&lt;/td&gt;
                    &lt;td style=&quot;width:15%;&quot; class=&quot;mono&quot;&gt;157.00&lt;/td&gt;
                &lt;/tr&gt;
            &lt;/table&gt;
        &lt;/div&gt;
        &lt;br /&gt;
        &lt;hr /&gt;
        &lt;br /&gt;
         
        &lt;table style=&quot;width:100%; height:35mm;&quot;&gt;
            &lt;tr&gt;
                &lt;td style=&quot;width:65%;&quot; valign=&quot;top&quot;&gt;
                    Payment Information :&lt;br /&gt;
                    Please make cheque payments payable to : &lt;br /&gt;
                    &lt;b&gt;ABC Corp&lt;/b&gt;
                    &lt;br /&gt;&lt;br /&gt;
                    The Invoice is payable within 7 days of issue.&lt;br /&gt;&lt;br /&gt;
                &lt;/td&gt;
                &lt;td&gt;
                &lt;div id=&quot;box&quot;&gt;
                    E &amp;amp; O.E.&lt;br /&gt;
                    For ABC Corp&lt;br /&gt;&lt;br /&gt;&lt;br /&gt;&lt;br /&gt;
                    Authorised Signatory
                &lt;/div&gt;
                &lt;/td&gt;
            &lt;/tr&gt;
        &lt;/table&gt;
    &lt;/div&gt;
     
    &lt;br /&gt;
     
    &lt;/div&gt;
     
    &lt;htmlpagefooter name=&quot;footer&quot;&gt;
        &lt;hr /&gt;
        &lt;div id=&quot;footer&quot;&gt; 
            &lt;table&gt;
                &lt;tr&gt;&lt;td&gt;Software Solutions&lt;/td&gt;&lt;td&gt;Mobile Solutions&lt;/td&gt;&lt;td&gt;Web Solutions&lt;/td&gt;&lt;/tr&gt;
            &lt;/table&gt;
        &lt;/div&gt;
    &lt;/htmlpagefooter&gt;
    &lt;sethtmlpagefooter name=&quot;footer&quot; value=&quot;on&quot; /&gt;
     
&lt;/body&gt;
&lt;/html&gt;): failed to open stream: Invalid argument C:\xampp\htdocs\project\dms\application\modules\mitra\controllers\Mitra.php 599
ERROR - 2017-12-06 09:48:12 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at C:\xampp\htdocs\project\dms\system\core\Exceptions.php:272) C:\xampp\htdocs\project\dms\application\libraries\mpdf.php 7451
ERROR - 2017-12-06 09:48:13 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at C:\xampp\htdocs\project\dms\system\core\Exceptions.php:272) C:\xampp\htdocs\project\dms\application\libraries\mpdf.php 1736
ERROR - 2017-12-06 09:49:55 --> Severity: Error --> Call to a member function RestartDocTemplate() on null C:\xampp\htdocs\project\dms\application\modules\mitra\controllers\Mitra.php 586
ERROR - 2017-12-06 09:50:22 --> Severity: Error --> Call to a member function SetImportUse() on null C:\xampp\htdocs\project\dms\application\modules\mitra\controllers\Mitra.php 586
ERROR - 2017-12-06 09:50:36 --> Severity: Error --> Call to a member function AddPage() on null C:\xampp\htdocs\project\dms\application\modules\mitra\controllers\Mitra.php 589
ERROR - 2017-12-06 09:58:24 --> Severity: Error --> Call to a member function SetImportUse() on null C:\xampp\htdocs\project\dms\application\modules\mitra\controllers\Mitra.php 586
ERROR - 2017-12-06 04:08:55 --> 404 Page Not Found: ./Mitrafoto/male-def.png
ERROR - 2017-12-06 04:08:55 --> 404 Page Not Found: ./Mitrafoto/male-def.png
ERROR - 2017-12-06 04:10:39 --> 404 Page Not Found: ./Mitrafoto/male-def.png
ERROR - 2017-12-06 04:10:39 --> 404 Page Not Found: ./Mitrafoto/male-def.png
ERROR - 2017-12-06 04:11:20 --> 404 Page Not Found: ./Mitrafoto/male-def.png
ERROR - 2017-12-06 04:11:20 --> 404 Page Not Found: ./Mitrafoto/male-def.png
ERROR - 2017-12-06 04:15:06 --> 404 Page Not Found: ./Mitrafoto/male-def.png
ERROR - 2017-12-06 04:15:06 --> 404 Page Not Found: ./Mitrafoto/male-def.png
ERROR - 2017-12-06 04:16:01 --> 404 Page Not Found: ./Mitrafoto/male-def.png
ERROR - 2017-12-06 04:16:01 --> 404 Page Not Found: ./Mitrafoto/male-def.png
ERROR - 2017-12-06 04:16:07 --> 404 Page Not Found: ./Mitrafoto/male-def.png
ERROR - 2017-12-06 04:16:07 --> 404 Page Not Found: ./Mitrafoto/male-def.png
ERROR - 2017-12-06 10:42:55 --> Severity: Parsing Error --> syntax error, unexpected ')', expecting ',' or ';' C:\xampp\htdocs\project\dms\application\modules\mitra\views\print_data.php 173
ERROR - 2017-12-06 04:45:29 --> 404 Page Not Found: /index
ERROR - 2017-12-06 04:45:29 --> 404 Page Not Found: /index
ERROR - 2017-12-06 04:46:15 --> 404 Page Not Found: /index
ERROR - 2017-12-06 04:46:15 --> 404 Page Not Found: /index
ERROR - 2017-12-06 04:46:44 --> 404 Page Not Found: /index
ERROR - 2017-12-06 04:46:44 --> 404 Page Not Found: /index
ERROR - 2017-12-06 04:51:42 --> 404 Page Not Found: /index
ERROR - 2017-12-06 04:51:42 --> 404 Page Not Found: /index
ERROR - 2017-12-06 04:51:55 --> 404 Page Not Found: /index
ERROR - 2017-12-06 04:54:47 --> 404 Page Not Found: /index
ERROR - 2017-12-06 04:54:47 --> 404 Page Not Found: /index
ERROR - 2017-12-06 07:11:11 --> 404 Page Not Found: /index
ERROR - 2017-12-06 07:11:11 --> 404 Page Not Found: /index
ERROR - 2017-12-06 07:11:19 --> 404 Page Not Found: /index
ERROR - 2017-12-06 15:03:58 --> Severity: Warning --> preg_match() expects parameter 2 to be string, array given C:\xampp\htdocs\project\dms\application\libraries\mpdf.php 1148
ERROR - 2017-12-06 15:03:58 --> Severity: Warning --> strlen() expects parameter 1 to be string, array given C:\xampp\htdocs\project\dms\application\libraries\mpdf.php 1154
ERROR - 2017-12-06 15:03:58 --> Severity: Warning --> substr() expects parameter 1 to be string, array given C:\xampp\htdocs\project\dms\application\libraries\mpdf.php 1158
ERROR - 2017-12-06 15:03:58 --> Severity: Warning --> substr() expects parameter 1 to be string, array given C:\xampp\htdocs\project\dms\application\libraries\mpdf.php 1162
ERROR - 2017-12-06 15:03:58 --> Severity: Warning --> substr() expects parameter 1 to be string, array given C:\xampp\htdocs\project\dms\application\libraries\mpdf.php 1166
ERROR - 2017-12-06 15:03:58 --> Severity: Warning --> strlen() expects parameter 1 to be string, array given C:\xampp\htdocs\project\dms\application\libraries\mpdf.php 1172
ERROR - 2017-12-06 15:03:58 --> Severity: Warning --> strlen() expects parameter 1 to be string, array given C:\xampp\htdocs\project\dms\application\libraries\mpdf.php 1172
ERROR - 2017-12-06 15:03:58 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at C:\xampp\htdocs\project\dms\system\core\Exceptions.php:272) C:\xampp\htdocs\project\dms\application\libraries\mpdf.php 7451
ERROR - 2017-12-06 15:03:58 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at C:\xampp\htdocs\project\dms\system\core\Exceptions.php:272) C:\xampp\htdocs\project\dms\application\libraries\mpdf.php 1736
ERROR - 2017-12-06 09:23:09 --> 404 Page Not Found: ../modules/kendaraan/controllers//index
ERROR - 2017-12-06 09:27:59 --> 404 Page Not Found: ../modules/kendaraan/controllers/Kendaraan/index
ERROR - 2017-12-06 09:51:43 --> 404 Page Not Found: ../modules/kendaraan/controllers/Kendaraan/index
ERROR - 2017-12-06 15:54:29 --> Severity: Error --> Call to a member function order_by() on null C:\xampp\htdocs\project\dms\application\modules\kendaraan\controllers\Kendaraan.php 38
ERROR - 2017-12-06 15:55:35 --> Query error: Unknown column 'kbm' in 'order clause' - Invalid query: SELECT *
FROM `kbm`
ORDER BY `kbm` ASC
ERROR - 2017-12-06 16:03:26 --> Query error: Unknown column 'users.nm_lengkap' in 'field list' - Invalid query: SELECT `kbm`.*, `users`.`nm_lengkap`
FROM `kbm`
INNER JOIN `kbm_merk` ON `id`=`merk`
INNER JOIN `kbm_model` ON `id`=`model`
ERROR - 2017-12-06 16:05:16 --> Query error: Column 'id' in on clause is ambiguous - Invalid query: SELECT `kbm`.*, `kbm_merk`.`merk`, `kbm_model`.`model`
FROM `kbm`
INNER JOIN `kbm_merk` ON `id`=`merk`
INNER JOIN `kbm_model` ON `id`=`model`
ERROR - 2017-12-06 16:05:45 --> Query error: Column 'merk' in on clause is ambiguous - Invalid query: SELECT `kbm`.*, `kbm_merk`.`merk`, `kbm_model`.`model`
FROM `kbm`
INNER JOIN `kbm_merk` ON `kbm_merk`.`id`=`merk`
INNER JOIN `kbm_model` ON `kbm_model`.`id`=`model`
ERROR - 2017-12-06 16:05:46 --> Query error: Column 'merk' in on clause is ambiguous - Invalid query: SELECT `kbm`.*, `kbm_merk`.`merk`, `kbm_model`.`model`
FROM `kbm`
INNER JOIN `kbm_merk` ON `kbm_merk`.`id`=`merk`
INNER JOIN `kbm_model` ON `kbm_model`.`id`=`model`
ERROR - 2017-12-06 16:05:47 --> Query error: Column 'merk' in on clause is ambiguous - Invalid query: SELECT `kbm`.*, `kbm_merk`.`merk`, `kbm_model`.`model`
FROM `kbm`
INNER JOIN `kbm_merk` ON `kbm_merk`.`id`=`merk`
INNER JOIN `kbm_model` ON `kbm_model`.`id`=`model`
ERROR - 2017-12-06 16:05:47 --> Query error: Column 'merk' in on clause is ambiguous - Invalid query: SELECT `kbm`.*, `kbm_merk`.`merk`, `kbm_model`.`model`
FROM `kbm`
INNER JOIN `kbm_merk` ON `kbm_merk`.`id`=`merk`
INNER JOIN `kbm_model` ON `kbm_model`.`id`=`model`
ERROR - 2017-12-06 16:05:47 --> Query error: Column 'merk' in on clause is ambiguous - Invalid query: SELECT `kbm`.*, `kbm_merk`.`merk`, `kbm_model`.`model`
FROM `kbm`
INNER JOIN `kbm_merk` ON `kbm_merk`.`id`=`merk`
INNER JOIN `kbm_model` ON `kbm_model`.`id`=`model`
ERROR - 2017-12-06 16:05:48 --> Query error: Column 'merk' in on clause is ambiguous - Invalid query: SELECT `kbm`.*, `kbm_merk`.`merk`, `kbm_model`.`model`
FROM `kbm`
INNER JOIN `kbm_merk` ON `kbm_merk`.`id`=`merk`
INNER JOIN `kbm_model` ON `kbm_model`.`id`=`model`
ERROR - 2017-12-06 16:05:48 --> Query error: Column 'merk' in on clause is ambiguous - Invalid query: SELECT `kbm`.*, `kbm_merk`.`merk`, `kbm_model`.`model`
FROM `kbm`
INNER JOIN `kbm_merk` ON `kbm_merk`.`id`=`merk`
INNER JOIN `kbm_model` ON `kbm_model`.`id`=`model`
ERROR - 2017-12-06 16:05:48 --> Query error: Column 'merk' in on clause is ambiguous - Invalid query: SELECT `kbm`.*, `kbm_merk`.`merk`, `kbm_model`.`model`
FROM `kbm`
INNER JOIN `kbm_merk` ON `kbm_merk`.`id`=`merk`
INNER JOIN `kbm_model` ON `kbm_model`.`id`=`model`
ERROR - 2017-12-06 16:18:47 --> Severity: Warning --> Missing argument 1 for Mitra::print_request() C:\xampp\htdocs\project\dms\application\modules\mitra\controllers\Mitra.php 583
ERROR - 2017-12-06 16:18:47 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '' at line 11 - Invalid query: SELECT
                mitra_cv.nomor,
                mitra_cv.namaperusahaan,
                mitra_cv.bidangusaha,
                mitra_cv.blnthn_masuk,
                mitra_cv.blnthn_keluar,
                mitra_cv.jabatan,
                mitra_cv.masakerja,
                mitra_cv.alasankeluar,
                mitra_cv.alamatperusahaan
                FROM mitra_cv WHERE mitra_id=
ERROR - 2017-12-06 16:20:53 --> Severity: Error --> Call to a member function tampil_pengalaman() on null C:\xampp\htdocs\project\dms\application\modules\kendaraan\controllers\Kendaraan.php 591
ERROR - 2017-12-06 10:31:05 --> Severity: Notice --> Undefined variable: nama_program C:\xampp\htdocs\project\dms\application\modules\users\views\login.php 5
ERROR - 2017-12-06 10:31:05 --> 404 Page Not Found: /index
ERROR - 2017-12-06 10:31:05 --> 404 Page Not Found: /index
ERROR - 2017-12-06 10:31:05 --> 404 Page Not Found: /index
ERROR - 2017-12-06 17:04:43 --> Severity: Error --> Call to undefined method Kendaraan_model::tampil_merk() C:\xampp\htdocs\project\dms\application\modules\kendaraan\controllers\Kendaraan.php 49
