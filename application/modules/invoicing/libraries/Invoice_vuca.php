<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__.'/Invoice_consulting.php';

class Invoice_vuca
{
    public static function fixed_codes()
    {
        return array('1102-01-01','2104-01-07','1106-01-05');
    }

    public static function sales_accounts($db, $accounting, $source, $lock=false)
    {
        $result=$db->query('SELECT owner_company_id,accounts FROM ms_invoice_coa_mapping WHERE source_company_id=?'.($lock?' FOR UPDATE':''),array($source));
        if (!$result) throw new RuntimeException('Pengaturan COA Vuca tidak dapat dibaca.');
        $mapping=$result->row_array();
        if (!$mapping || (string)$mapping['owner_company_id']!=='4') throw new RuntimeException('Lengkapi Pengaturan COA Invoicing company sumber dengan pemilik jurnal Vuca.');
        $codes=json_decode($mapping['accounts'],true);
        if (!is_array($codes)) throw new RuntimeException('Mapping COA penjualan Vuca tidak valid.');
        $result=$accounting->query('SELECT no_perkiraan,nama FROM coa_master ORDER BY no_perkiraan');
        if (!$result) throw new RuntimeException('Master COA Vuca tidak dapat dibaca.');
        $master=array();
        foreach ($result->result_array() as $row) $master[$row['no_perkiraan']][]=$row['nama'];
        $accounts=array();
        foreach (Invoice_consulting::revenues() as $role=>$label) {
            $code=$codes[$role]??null;
            if (!is_string($code) || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D',$code)
                || substr($code,0,4)==='0000' || in_array($code,self::fixed_codes(),true)
                || !isset($master[$code]) || count($master[$code])!==1 || trim($master[$code][0])==='') {
                throw new RuntimeException('Mapping '.$label.' tidak valid pada master COA Vuca.');
            }
            $accounts[$role]=array('code'=>$code,'name'=>$master[$code][0],'label'=>$label);
        }
        return $accounts;
    }

    public static function saved_revenue($journals)
    {
        $revenues=array_values(array_filter($journals,function($line){
            return !in_array($line['coa'],self::fixed_codes(),true) && (float)$line['kredit']>0;
        }));
        if (count($revenues)!==1) throw new RuntimeException('COA pendapatan historis Vuca tidak lengkap atau ambigu.');
        return $revenues[0];
    }

    public static function preview($accounting, $amount, $company, $date)
    {
        $result=$accounting->query('SELECT no_perkiraan,nama FROM coa_master ORDER BY no_perkiraan');
        if (!$result) throw new RuntimeException('Master COA Vuca tidak dapat dibaca.');
        $master=array();
        foreach ($result->result_array() as $row) $master[$row['no_perkiraan']][]=$row['nama'];
        $lines=array();
        foreach (array_merge(self::fixed_codes(),array('')) as $code) {
            if ($code!=='' && (!isset($master[$code]) || count($master[$code])!==1 || trim($master[$code][0])==='')) throw new RuntimeException('Master COA jurnal Vuca tidak lengkap.');
            $pph=$amount*0.5/100;
            $lines[]=array('coa'=>$code,'nm_coa'=>$code===''?'Pilih COA penjualan':$master[$code][0],
                'id_company'=>$company['id'],'nm_company'=>$company['nm_company'],'tgl_jurnal'=>$date,
                'debit'=>$code==='1102-01-01'?$amount-$pph:($code==='1106-01-05'?$pph:0),
                'kredit'=>$code===''?$amount:0);
        }
        return $lines;
    }
}
