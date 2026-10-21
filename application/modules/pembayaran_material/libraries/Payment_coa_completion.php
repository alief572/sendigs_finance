<?php
class Payment_coa_completion
{
    const ACCOUNTS=[
        '6206-00-00'=>['Biaya Operasional Lainnya','3'],
        '6206-01-00'=>['Biaya Operasional Lainnya - Konsultan','4'],
        '6206-01-07'=>['Employee Engagement','5'],
        '6207-00-00'=>['Perizinan Perusahaan','3']
    ];

    public static function plan($source,$target)
    {
        $byCode=[];foreach($source as $row)$byCode[$row['no_perkiraan']][]=$row;
        $existing=[];foreach($target as $row)if(isset(self::ACCOUNTS[$row['no_perkiraan']]))$existing[$row['no_perkiraan']][]=$row;
        $result=[];
        foreach(self::ACCOUNTS as $code=>$definition){
            $rows=$byCode[$code] ?? [];if(count($rows)!==1)throw new DomainException('Master sumber tidak unik/lengkap: '.$code);
            $row=$rows[0];
            if($row['nama']!==$definition[0]||(string)$row['level']!==$definition[1]||$row['tipe']!=='A'||(string)$row['faktor']!=='1')throw new DomainException('Definisi master sumber berubah: '.$code);
            if(isset($existing[$code])){
                if(count($existing[$code])!==1)throw new DomainException('Master STM duplikat: '.$code);
                foreach(['nama','level','tipe','faktor'] as $field)if((string)$existing[$code][0][$field]!== (string)$row[$field])throw new DomainException('Master STM bertentangan: '.$code);
                continue;
            }
            $record=[];
            foreach(['no_perkiraan','nama','kdcab','bln','thn','tmp','tipe','level','grup','faktor','cf_grup','cf_urut','cf_child'] as $field)$record[$field]=$row[$field] ?? null;
            $record+=['saldoawal'=>'0','debet'=>'0','kredit'=>'0','prev_month'=>null,'kode_bank'=>null];$result[]=$record;
        }
        return $result;
    }

    public static function execute($db,$source,$target,$backupPath,$apply=false)
    {
        foreach([$source,$target] as $schema)if(!preg_match('/^[A-Za-z0-9_]+$/D',$schema))throw new DomainException('Schema master tidak valid.');
        if($source===$target)throw new DomainException('Source dan target master harus berbeda.');
        $sourceTable='`'.$source.'`.coa_master';$targetTable='`'.$target.'`.coa_master';$locked=false;$inserted=[];
        try{
            if($apply){$db->query('LOCK TABLES '.$sourceTable.' READ, '.$targetTable.' WRITE');$locked=true;}
            $codes="'".implode("','",array_keys(self::ACCOUNTS))."'";
            $sourceRows=$db->query('SELECT * FROM '.$sourceTable.' WHERE no_perkiraan IN ('.$codes.') ORDER BY no_perkiraan')->fetch_all(MYSQLI_ASSOC);
            $before=$db->query('SELECT * FROM '.$targetTable.' ORDER BY id')->fetch_all(MYSQLI_ASSOC);
            $plan=self::plan($sourceRows,$before);
            if(!$apply)return ['status'=>'dry_run','missing_count'=>count($plan),'records'=>$plan];
            if(!$plan)return ['status'=>'already_complete','inserted_count'=>0];
            $backup=['source_schema'=>$source,'target_schema'=>$target,'source_rows'=>$sourceRows,'target_before'=>$before,'planned'=>$plan];
            if(is_file($backupPath))throw new DomainException('Backup completion sudah ada; tinjau hasil sebelumnya sebelum melanjutkan.');
            if(!is_dir(dirname($backupPath)))throw new DomainException('Direktori backup tidak tersedia.');
            $handle=fopen($backupPath,'x');if(!$handle)throw new RuntimeException('Backup master gagal dibuat.');
            try{$json=json_encode($backup,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR);if(fwrite($handle,$json)!==strlen($json)||!fflush($handle))throw new RuntimeException('Backup master tidak lengkap.');}finally{fclose($handle);}
            foreach($plan as $row){
                $statement=$db->prepare('INSERT INTO '.$targetTable.' (`'.implode('`,`',array_keys($row)).'`) VALUES ('.implode(',',array_fill(0,count($row),'?')).')');
                $values=array_values($row);$statement->bind_param(str_repeat('s',count($values)),...$values);$statement->execute();
                if($statement->affected_rows!==1)throw new RuntimeException('Insert master tidak terkonfirmasi.');$inserted[]=$db->insert_id;
            }
            $after=$db->query('SELECT * FROM '.$targetTable.' ORDER BY id')->fetch_all(MYSQLI_ASSOC);
            $unchanged=array_values(array_filter($after,function($r)use($inserted){return !in_array((int)$r['id'],array_map('intval',$inserted),true);}));
            if($unchanged!==$before||count($after)!==count($before)+count($plan)||self::plan($sourceRows,$after)!==[])throw new RuntimeException('Verifikasi master setelah insert gagal.');
            return ['status'=>'completed','inserted_count'=>count($inserted),'inserted_ids'=>$inserted,'codes'=>array_column($plan,'no_perkiraan'),'existing_rows_unchanged'=>true,'balances_copied'=>false];
        }catch(Throwable $error){
            if($inserted)throw new RuntimeException('Master MyISAM mungkin terisi sebagian; periksa backup dan ID baru sebelum melanjutkan: '.implode(',',$inserted),0,$error);
            throw $error;
        }finally{if($locked)$db->query('UNLOCK TABLES');}
    }
}
