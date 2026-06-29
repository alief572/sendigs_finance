<?php
$file = 'D:/web_dev/sendigs_finance/Petty Cash Konsep Baru FIX.xlsx';
$z = new ZipArchive();
if($z->open($file)!==true) die('error opening file');

$wbStr = $z->getFromName('xl/workbook.xml');
$wb = simplexml_load_string($wbStr);

$ssXml = $z->getFromName('xl/sharedStrings.xml');
$ss = [];
if ($ssXml) {
    $sx = simplexml_load_string($ssXml);
    foreach($sx->si as $si) {
        $t='';
        if(isset($si->t)) $t=(string)$si->t;
        elseif(isset($si->r)) {
            foreach($si->r as $r) $t.=(string)$r->t;
        }
        $ss[]=$t;
    }
}

$rels = simplexml_load_string($z->getFromName('xl/_rels/workbook.xml.rels'));

foreach($wb->sheets->sheet as $s) {
    $name = (string)$s['name'];
    $rid = (string)$s['r:id'];
    if(!$rid) {
        $ns = $s->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $rid = (string)$ns['id'];
    }
    echo "\n=== Sheet: $name ===\n";
    foreach($rels->Relationship as $r) {
        if((string)$r['Id'] === $rid) {
            $sf = 'xl/'.(string)$r['Target'];
            $sheetStr = $z->getFromName($sf);
            if(!$sheetStr) continue;
            $sx = simplexml_load_string($sheetStr);
            $rc=0;
            foreach($sx->sheetData->row as $row) {
                $rd=[];
                foreach($row->c as $c) {
                    $v=(string)$c->v;
                    if(isset($c['t']) && (string)$c['t']==='s') $v=$ss[(int)$v]??$v;
                    if($v!=='') $rd[]=$c['r']."=".$v;
                }
                if(!empty($rd)) {
                    echo implode(' | ', $rd)."\n";
                    $rc++;
                    if($rc>15) {
                        echo "...\n";
                        break;
                    }
                }
            }
        }
    }
}
