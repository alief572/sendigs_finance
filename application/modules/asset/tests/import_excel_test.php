<?php
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

error_reporting(E_ALL);
require __DIR__.'/import_rules_test.php';
require dirname(__DIR__,3).'/libraries/Asset_import_excel.php';
$excel = new Asset_import_excel(); $path = tempnam(sys_get_temp_dir(),'asset_test_');
function writeArchive(string $path, array $entries): void {
    $zip = new ZipArchive();
    check($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 'create zip fixture');
    foreach ($entries as $name => $contents) { $zip->addFromString($name, $contents); }
    check($zip->close(), 'save zip fixture');
}
try {
    $fixtureReporting=error_reporting(); error_reporting(E_ALL); ob_start();
    $excel->template($masters,$path);
    check($excel->read($path)===[], 'example must not import');
    check(ob_get_clean()==='', 'development warnings must not corrupt XLSX/JSON');
    check(error_reporting()===E_ALL,'error reporting restored'); error_reporting($fixtureReporting);
    $book=(new XlsxReader())->load($path);
    check($book->getActiveSheet()->getTitle()==='Data Asset','active');
    check($book->getSheetByName('Referensi')->getSheetState()==='hidden','hidden');
    check($book->getActiveSheet()->getCell('B2')->getDataValidation()->getFormula1()==='Import_category','dropdown');
    check($book->getActiveSheet()->getCell('H2')->getDataValidation()->getFormula1()==='Import_years','years dropdown');
    $book->getActiveSheet()->fromArray(['Laptop','ALAT','PT A','','2026-10-12',5,3000000,4],null,'A2');
    (new XlsxWriter($book))->save($path);
    $result=$rules->validate($excel->read($path),$masters);
    check(!$result['errors'] && $result['summary']['unit']===5,'roundtrip');
    $book->getActiveSheet()->setCellValue('E2',46307);
    (new XlsxWriter($book))->save($path);
    check($rules->validate($excel->read($path),$masters)['rows'][0]['date']==='2026-10-12','native Excel date');
    $book->setExcelCalendar(Date::CALENDAR_MAC_1904);
    $book->getActiveSheet()->setCellValue('E2',46307-1462);
    (new XlsxWriter($book))->save($path);
    check($rules->validate($excel->read($path),$masters)['rows'][0]['date']==='2026-10-12','1904 Excel date');
    $book->setExcelCalendar(Date::CALENDAR_WINDOWS_1900);
    $book->getActiveSheet()->setCellValue('E2','2026-10-12');
    $book->getActiveSheet()->setCellValue('G2','=1+2');
    (new XlsxWriter($book))->save($path);
    check(count($rules->validate($excel->read($path),$masters)['errors'])>0,'formula');
    $book->getActiveSheet()->setCellValue('I1','Cost Center');
    (new XlsxWriter($book))->save($path);
    try { $excel->read($path); throw new LogicException('legacy nine-column format accepted'); } catch (RuntimeException $e) { check(!($e instanceof LogicException),'nine-column format rejected'); }
    $book->getActiveSheet()->removeColumn('I');
    $book->getActiveSheet()->setCellValue('A1','wrong header');
    (new XlsxWriter($book))->save($path);
    try { $excel->read($path); throw new LogicException('header accepted'); } catch (RuntimeException $e) { check(!($e instanceof LogicException),'header rejection'); }
    $book->disconnectWorksheets();
    file_put_contents($path,'not excel');
    try { $excel->read($path); throw new LogicException('bad format accepted'); } catch (RuntimeException $e) { check(!($e instanceof LogicException),'bad format'); }
    file_put_contents($path,'PK'.str_repeat('x',5*1024*1024));
    try { $excel->read($path); throw new LogicException('oversize accepted'); } catch (RuntimeException $e) { check(!($e instanceof LogicException),'5 MB cap'); }
    writeArchive($path, ['xl/worksheets/sheet1.xml' => str_repeat('x', 31 * 1024 * 1024)]);
    try { $excel->read($path); throw new LogicException('expansion accepted'); } catch (RuntimeException $e) { check(!($e instanceof LogicException),'expanded zip cap'); }
    $forged=file_get_contents($path); $central=strpos($forged,"PK\x01\x02");
    $forged=substr_replace($forged,pack('V',1),22,4);
    $forged=substr_replace($forged,pack('V',1),$central+24,4); file_put_contents($path,$forged);
    try { $excel->read($path); throw new LogicException('forged size accepted'); } catch (RuntimeException $e) { check(!($e instanceof LogicException),'bounded inflate despite forged metadata'); }
    writeArchive($path, ['xl/workbook.xml' => '<!DOCTYPE workbook [<!ENTITY x SYSTEM "file:///etc/passwd">]><workbook/>']);
    try { $excel->read($path); throw new LogicException('entity accepted'); } catch (RuntimeException $e) { check(!($e instanceof LogicException),'XML entity rejected'); }
    writeArchive($path, ['xl/workbook.xml' => '<broken', '[Content_Types].xml' => '<Types/>']);
    ob_start();
    try { $excel->read($path); throw new LogicException('malformed XML accepted'); } catch (RuntimeException $e) { check(!($e instanceof LogicException),'malformed XML rejected'); }
    check(ob_get_clean()==='','malformed XML warnings contained');
    echo "Excel PhpSpreadsheet roundtrip OK\n";
} finally { if (is_file($path)) { unlink($path); } }
