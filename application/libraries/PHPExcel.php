<?php

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

class PHPExcel extends Spreadsheet
{
    private ?PHPExcel_Style $legacyStyle = null;

    public function getCellXfSupervisor(): Style
    {
        if ($this->legacyStyle === null) {
            $this->legacyStyle = new PHPExcel_Style(true);
            $this->legacyStyle->bindParent($this);
        }
        return $this->legacyStyle;
    }
}

class PHPExcel_Style extends Style
{
    public function applyFromArray(array $styleArray, bool $advancedBorders = true): static
    {
        $groups = [
            'fill' => ['type' => 'fillType', 'color' => 'startColor', 'startcolor' => 'startColor', 'endcolor' => 'endColor'],
            'alignment' => ['wrap' => 'wrapText', 'rotation' => 'textRotation', 'shrinktofit' => 'shrinkToFit'],
            'font' => ['strike' => 'strikethrough', 'superScript' => 'superscript', 'subScript' => 'subscript'],
            'numberFormat' => ['code' => 'formatCode'],
            'borders' => ['allborders' => 'allBorders', 'diagonaldirection' => 'diagonalDirection'],
        ];
        $styleArray = self::renameKeys($styleArray, ['numberformat' => 'numberFormat']);
        foreach ($groups as $group => $keys) {
            if (isset($styleArray[$group])) {
                $styleArray[$group] = self::renameKeys($styleArray[$group], $keys);
            }
        }
        foreach ($styleArray['borders'] ?? [] as $edge => $settings) {
            if (is_array($settings)) {
                $styleArray['borders'][$edge] = self::renameKeys($settings, ['style' => 'borderStyle']);
            }
        }
        return parent::applyFromArray($styleArray, $advancedBorders);
    }

    private static function renameKeys(array $settings, array $names): array
    {
        foreach ($names as $old => $new) {
            if (array_key_exists($old, $settings)) {
                $settings[$new] ??= $settings[$old];
                unset($settings[$old]);
            }
        }
        return $settings;
    }
}

class PHPExcel_Cell
{
    public static function stringFromColumnIndex($index): string
    {
        return Coordinate::stringFromColumnIndex($index + 1);
    }
}

class PHPExcel_IOFactory
{
    private const FORMATS = ['Excel2007' => 'Xlsx', 'Excel5' => 'Xls', 'CSV' => 'Csv', 'HTML' => 'Html'];

    public static function createWriter(Spreadsheet $workbook, string $format): IWriter
    {
        return IOFactory::createWriter($workbook, self::FORMATS[$format] ?? $format);
    }

    public static function createReader(string $format): IReader
    {
        return IOFactory::createReader(self::FORMATS[$format] ?? $format);
    }

    public static function load(string $path): Spreadsheet
    {
        return IOFactory::load($path);
    }
}

class_alias(PhpOffice\PhpSpreadsheet\Cell\DataType::class, 'PHPExcel_Cell_DataType');
class_alias(PhpOffice\PhpSpreadsheet\Shared\Date::class, 'PHPExcel_Shared_Date');
class_alias(PhpOffice\PhpSpreadsheet\Shared\File::class, 'PHPExcel_Shared_File');
class_alias(PhpOffice\PhpSpreadsheet\Style\Alignment::class, 'PHPExcel_Style_Alignment');
class_alias(PhpOffice\PhpSpreadsheet\Style\Border::class, 'PHPExcel_Style_Border');
class_alias(PhpOffice\PhpSpreadsheet\Style\Color::class, 'PHPExcel_Style_Color');
class_alias(PhpOffice\PhpSpreadsheet\Style\Fill::class, 'PHPExcel_Style_Fill');
class_alias(PhpOffice\PhpSpreadsheet\Style\NumberFormat::class, 'PHPExcel_Style_NumberFormat');
class_alias(PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::class, 'PHPExcel_Worksheet');
class_alias(PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column\Rule::class, 'PHPExcel_Worksheet_AutoFilter_Column_Rule');
