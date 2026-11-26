<?php
// Fixture writes call legacy PHPExcel directly; actual template/reader scope their compatibility handling.
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require __DIR__.'/import_rules_test.php';
require dirname(__DIR__,3).'/libraries/Asset_import_excel.php';
$excel = new Asset_import_excel(); $path = tempnam(sys_get_temp_dir(),'asset_test_');
try {
    $fixtureReporting=error_reporting(); error_reporting(E_ALL); ob_start();
    $excel->template($masters,$path);
    check($excel->read($path)===[], 'example must not import');
    check(ob_get_clean()==='', 'development warnings must not corrupt XLSX/JSON');
    check(error_reporting()===E_ALL,'error reporting restored'); error_reporting($fixtureReporting);
    $book=PHPExcel_IOFactory::createReader('Excel2007')->load($path);
    check($book->getActiveSheet()->getTitle()==='Data Asset','active');
    check($book->getSheetByName('Referensi')->getSheetState()==='hidden','hidden');
    check($book->getActiveSheet()->getCell('B2')->getDataValidation()->getFormula1()==='Import_category','dropdown');
    check($book->getActiveSheet()->getCell('H2')->getDataValidation()->getFormula1()==='Import_years','years dropdown');
    $book->getActiveSheet()->fromArray(['Laptop','ALAT','PT A','','2026-10-12',5,3000000,4],null,'A2');
    PHPExcel_IOFactory::createWriter($book,'Excel2007')->save($path);
    $result=$rules->validate($excel->read($path),$masters);
    check(!$result['errors'] && $result['summary']['unit']===5,'roundtrip');
    $book->getActiveSheet()->setCellValue('E2',46307);
    PHPExcel_IOFactory::createWriter($book,'Excel2007')->save($path);
    check($rules->validate($excel->read($path),$masters)['rows'][0]['date']==='2026-10-12','native Excel date');
    PHPExcel_Shared_Date::setExcelCalendar(PHPExcel_Shared_Date::CALENDAR_MAC_1904);
    $book->getActiveSheet()->setCellValue('E2',46307-1462);
    PHPExcel_IOFactory::createWriter($book,'Excel2007')->save($path);
    check($rules->validate($excel->read($path),$masters)['rows'][0]['date']==='2026-10-12','1904 Excel date');
    PHPExcel_Shared_Date::setExcelCalendar(PHPExcel_Shared_Date::CALENDAR_WINDOWS_1900);
    $book->getActiveSheet()->setCellValue('E2','2026-10-12');
    $book->getActiveSheet()->setCellValue('G2','=1+2');
    PHPExcel_IOFactory::createWriter($book,'Excel2007')->save($path);
    check(count($rules->validate($excel->read($path),$masters)['errors'])>0,'formula');
    $book->getActiveSheet()->setCellValue('I1','Cost Center');
    PHPExcel_IOFactory::createWriter($book,'Excel2007')->save($path);
    try { $excel->read($path); throw new LogicException('legacy nine-column format accepted'); } catch (RuntimeException $e) { check(!($e instanceof LogicException),'nine-column format rejected'); }
    $book->getActiveSheet()->removeColumn('I');
    $book->getActiveSheet()->setCellValue('A1','wrong header');
    PHPExcel_IOFactory::createWriter($book,'Excel2007')->save($path);
    try { $excel->read($path); throw new LogicException('header accepted'); } catch (RuntimeException $e) { check(!($e instanceof LogicException),'header rejection'); }
    $book->disconnectWorksheets();
    file_put_contents($path,'not excel');
    try { $excel->read($path); throw new LogicException('bad format accepted'); } catch (RuntimeException $e) { check(!($e instanceof LogicException),'bad format'); }
    file_put_contents($path,'PK'.str_repeat('x',5*1024*1024));
    try { $excel->read($path); throw new LogicException('oversize accepted'); } catch (RuntimeException $e) { check(!($e instanceof LogicException),'5 MB cap'); }
    unlink($path); $zip=new PclZip($path);
    $zip->create([[PCLZIP_ATT_FILE_NAME=>'xl/worksheets/sheet1.xml',PCLZIP_ATT_FILE_CONTENT=>str_repeat('x',31*1024*1024)]]);
    try { $excel->read($path); throw new LogicException('expansion accepted'); } catch (RuntimeException $e) { check(!($e instanceof LogicException),'expanded zip cap'); }
    $forged=file_get_contents($path); $central=strpos($forged,"PK\x01\x02");
    $forged=substr_replace($forged,pack('V',1),22,4);
    $forged=substr_replace($forged,pack('V',1),$central+24,4); file_put_contents($path,$forged);
    try { $excel->read($path); throw new LogicException('forged size accepted'); } catch (RuntimeException $e) { check(!($e instanceof LogicException),'bounded inflate despite forged metadata'); }
    unlink($path); $zip=new PclZip($path);
    $zip->create([[PCLZIP_ATT_FILE_NAME=>'xl/workbook.xml',PCLZIP_ATT_FILE_CONTENT=>'<!DOCTYPE workbook [<!ENTITY x SYSTEM "file:///etc/passwd">]><workbook/>']]);
    try { $excel->read($path); throw new LogicException('entity accepted'); } catch (RuntimeException $e) { check(!($e instanceof LogicException),'XML entity rejected'); }
    unlink($path); $zip=new PclZip($path);
    $zip->create([[PCLZIP_ATT_FILE_NAME=>'xl/workbook.xml',PCLZIP_ATT_FILE_CONTENT=>'<broken'],[PCLZIP_ATT_FILE_NAME=>'[Content_Types].xml',PCLZIP_ATT_FILE_CONTENT=>'<Types/>']]);
    ob_start();
    try { $excel->read($path); throw new LogicException('malformed XML accepted'); } catch (RuntimeException $e) { check(!($e instanceof LogicException),'malformed XML rejected'); }
    check(ob_get_clean()==='','malformed XML warnings contained');
    echo "Excel PCLZip roundtrip OK\n";
} finally { if (is_file($path)) { unlink($path); } }
