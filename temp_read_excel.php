<?php
$file = 'C:/Users/Febry/Downloads/Petty Cash Konsep Baru FIX.xlsx';
$zip = new ZipArchive();
if ($zip->open($file) !== true) {
    die('Cannot open file');
}

// Get sheet names from workbook.xml
$workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
$sheets = [];
foreach ($workbook->sheets->sheet as $sheet) {
    $attrs = $sheet->attributes();
    $rId = (string)$attrs['r:id'] ?? (string)$sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
    $sheets[] = ['name' => (string)$attrs['name'], 'rId' => $rId];
    echo "Sheet: " . (string)$attrs['name'] . " (rId: $rId)" . PHP_EOL;
}

// Find target sheet
$targetName = null;
$targetRId = null;
foreach ($sheets as $s) {
    if (stripos($s['name'], 'Expense Petty Cash') !== false) {
        $targetName = $s['name'];
        $targetRId = $s['rId'];
        break;
    }
}
if (!$targetName) {
    die('Target sheet not found');
}
echo PHP_EOL . "Target: $targetName ($targetRId)" . PHP_EOL;

// Get sheet file from relationships
$rels = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
$sheetFile = null;
foreach ($rels->Relationship as $rel) {
    if ((string)$rel['Id'] === $targetRId) {
        $sheetFile = 'xl/' . (string)$rel['Target'];
        break;
    }
}
echo "Sheet file: $sheetFile" . PHP_EOL . PHP_EOL;

// Load shared strings
$sharedStrings = [];
$ssXml = $zip->getFromName('xl/sharedStrings.xml');
if ($ssXml) {
    $ss = simplexml_load_string($ssXml);
    foreach ($ss->si as $si) {
        $text = '';
        if ($si->t) {
            $text = (string)$si->t;
        } elseif ($si->r) {
            foreach ($si->r as $r) {
                $text .= (string)$r->t;
            }
        }
        $sharedStrings[] = $text;
    }
}

// Load sheet data
$sheetXml = simplexml_load_string($zip->getFromName($sheetFile));
$rows = $sheetXml->sheetData->row;

foreach ($rows as $row) {
    $rowNum = (string)$row['r'];
    $rowData = [];
    foreach ($row->c as $cell) {
        $ref = (string)$cell['r'];
        $type = (string)$cell['t'];
        $value = (string)$cell->v;
        
        if ($type === 's') {
            $value = $sharedStrings[(int)$value] ?? $value;
        }
        
        if ($value !== '') {
            $rowData[] = "$ref=$value";
        }
    }
    if (!empty($rowData)) {
        echo "Row $rowNum: " . implode(' | ', $rowData) . PHP_EOL;
    }
    if ((int)$rowNum > 60) {
        echo "... (truncated)" . PHP_EOL;
        break;
    }
}

$zip->close();
