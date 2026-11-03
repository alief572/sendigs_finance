<?php
class Jurnal_debt_posting
{
    public static function forwardLegacy($anchor)
    {
        $original=$_POST;
        try{
            $_POST=['id'=>(int)$anchor->id,'no_transaksi'=>$anchor->no_transaksi,'jenis_transaksi'=>'Payment Hutang Petty Cash'];
            echo Modules::run('jurnal_payment_petty_cash/save_posting_jurnal');
        }finally{$_POST=$original;}
    }

    private $ci;
    public function __construct($params=[]){$this->ci=$params['ci'] ?? get_instance();}
    private function now(){return (new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s');}

    private function saveAttempt($key,$status,$results)
    {
        if($this->ci->db->where('group_key',$key)->update('tr_jurnal_debt_attempts',[
            'status'=>$status,'target_results'=>json_encode($results,JSON_THROW_ON_ERROR),'updated_at'=>$this->now()])===false)throw new RuntimeException('Gagal menyimpan status percobaan.');
    }

    private function transactional($db,$tables,$allowMyisamHeader=false)
    {
        $result=$db->query('SELECT TABLE_NAME,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('.implode(',',array_fill(0,count($tables),'?')).')',$tables);
        if(!$result)throw new InvalidArgumentException('Engine tabel posting belum dapat diverifikasi.');
        $engines=$result->result_array();
        if(count($engines)!==count($tables))throw new InvalidArgumentException('Tabel posting tujuan belum lengkap.');
        $map=[];
        foreach($engines as $engine){
            $map[$engine['TABLE_NAME']]=strtoupper($engine['ENGINE']);
            if($map[$engine['TABLE_NAME']]==='INNODB')continue;
            if($allowMyisamHeader&&$engine['TABLE_NAME']==='japh'&&$map['japh']==='MYISAM')continue;
            throw new InvalidArgumentException('Detail/counter dan registry posting hutang memerlukan InnoDB; periksa konfigurasi tujuan.');
        }
        return $map;
    }

    public function execute($id,$expectedSource='PCS')
    {
        if(!has_permission('Jurnal_All.Manage')&&!has_permission('Jurnal_Payment_Petty_Cash.Add'))throw new RuntimeException('Anda tidak memiliki akses posting jurnal.');
        if(!is_scalar($id)||!preg_match('/^[1-9]\d*$/D',(string)$id))throw new InvalidArgumentException('ID jurnal tidak valid.');
        if($expectedSource!=='PCS')throw new InvalidArgumentException('Sumber jurnal tidak valid.');
        $ci=$this->ci;$model=$ci->Jurnal_all_model;$db=$ci->db;
        if(!$model->debt_posting_ready())throw new InvalidArgumentException('Jalankan migrasi registry hutang dan metadata posting sebelum melanjutkan.');
        $actor=['id'=>(string)$ci->auth->user_id(),'name'=>(string)$ci->auth->user_name()];
        if($actor['id']===''||trim($actor['name'])==='')throw new InvalidArgumentException('User posting tidak tersedia.');
        $detail=$model->debt_groups(true)->find($id);$key=$detail['header']['group_id'];
        $lockNames=['jurnal_debt:'.substr($key,0,48)];
        foreach($detail['members'] as $member)$lockNames[]='jurnal_all:'.substr(hash('sha256',$model->key($member)),0,48);
        $lockNames=array_values(array_unique($lockNames));sort($lockNames,SORT_STRING);
        $locks=[];$targets=[];$targetDebug=[];$headerEngines=[];$newHeaders=[];$begun=[];$results=[];$claimed=false;$commitAttempted=false;$sourceBegun=false;
        $sourceDebug=$db->db_debug;$db->db_debug=false;
        try{
            foreach($lockNames as $name){
                $lock=$db->query('SELECT GET_LOCK(?, 10) AS acquired',[$name]);
                if(!$lock || (int)$lock->row()->acquired!==1)throw new InvalidArgumentException('Kelompok hutang sedang diproses. Muat ulang daftar.');
                $locks[]=$name;
            }
            $fresh=$model->debt_groups(true)->find($id);
            if($fresh['header']['group_id']!==$key || $fresh['fingerprint']!==$detail['fingerprint'])throw new InvalidArgumentException('Anggota jurnal berubah. Muat ulang daftar.');
            if(!$fresh['can_post'] || !$fresh['header']['group_valid'])throw new InvalidArgumentException('Posting hutang diblokir: '.implode(' ',$fresh['blockers'] ?: [$fresh['header']['group_status']]));
            $ci->load->model('jurnal_payment_petty_cash/Jurnal_payment_petty_cash_model');
            $ci->load->model('jurnal_payment_petty_cash/Jurnal_payment_petty_cash_nomor_model');
            $writer=$ci->Jurnal_payment_petty_cash_model;$numbers=$ci->Jurnal_payment_petty_cash_nomor_model;
            if($writer->find_unclear_coa($fresh['rows']))throw new InvalidArgumentException('COA hutang belum dikonfirmasi Finance.');
            $this->transactional($db,['tr_jurnal','tr_jurnal_debt_attempts']);
            $references=[$fresh['header']['payment_voucher']];
            foreach($fresh['members'] as $member)$references[]=$member['no_transaksi'];
            $references=array_merge($references,explode(', ',$fresh['header']['no_referensi']));
            if($db->table_exists('jurnaltras')){
                $staging=$db->group_start()->where_in('no_reff',$references)->or_where_in('no_request',$references)->group_end()->get('jurnaltras');
                if(!$staging||$staging->num_rows()>0)throw new InvalidArgumentException('Jurnaltras pembayaran terkait sudah ada atau belum dapat dipastikan; rekonsiliasi diperlukan.');
            }
            foreach($fresh['company_sections'] as $section){
                $target=$ci->load->database($section['target_db'],true);
                if(!$target)throw new InvalidArgumentException('Koneksi tujuan tidak tersedia.');
                $targets[$section['target_db']]=$target;
                $targetDebug[$section['target_db']]=$target->db_debug;$target->db_debug=false;
                $engines=$this->transactional($target,['jurnal','japh','pastibisa_tb_cabang'],true);$headerEngines[$section['target_db']]=$engines['japh'];
                $coas=[];foreach($section['rows'] as $row)if($model->cents($row['debit'])>0||$model->cents($row['kredit'])>0)$coas[]=$row['coa'];
                $coaResult=$target->select('no_perkiraan')->where_in('no_perkiraan',array_unique($coas))->get('coa_master');
                if(!$coaResult || array_diff($coas,array_column($coaResult->result_array(),'no_perkiraan')))throw new InvalidArgumentException('COA tidak tersedia pada tujuan '.$section['nm_company'].'.');
                foreach(['jurnal','japh'] as $table){
                    $existing=$target->where_in('no_reff',$references)->get($table);
                    if(!$existing || $existing->num_rows()>0)throw new InvalidArgumentException('Accounting terkait sudah ada atau belum dapat dipastikan; lakukan rekonsiliasi sebelum posting.');
                }
            }
            $writer->bind_debt_connections($targets);$numbers->bind_debt_connections($targets);
            $previous=$db->get_where('tr_jurnal_debt_attempts',['group_key'=>$key]);
            if(!$previous)throw new RuntimeException('Registry percobaan tidak tersedia.');
            $previous=$previous->row_array();
            if($previous && ($previous['status']!=='failed_no_commit' || $previous['fingerprint']!==$fresh['fingerprint']))throw new InvalidArgumentException('Percobaan sebelumnya memerlukan rekonsiliasi; jangan posting ulang.');
            if($db->trans_begin()===false)throw new RuntimeException('Gagal memulai registry.');
            $sourceBegun=true;
            $record=['fingerprint'=>$fresh['fingerprint'],'status'=>'processing','actor_id'=>$actor['id'],'actor_name'=>mb_substr($actor['name'],0,191,'UTF-8'),
                'target_results'=>'{}','created_at'=>$this->now(),'updated_at'=>$this->now()];
            $saved=$previous?$db->where('group_key',$key)->update('tr_jurnal_debt_attempts',$record):$db->insert('tr_jurnal_debt_attempts',['group_key'=>$key]+$record);
            if($saved===false || $db->trans_status()===false || $db->trans_commit()===false){$db->trans_rollback();throw new RuntimeException('Registry gagal disimpan.');}
            $sourceBegun=false;
            $claimed=true;
            foreach($fresh['company_sections'] as $section){
                $group=$section['target_db'];$target=$targets[$group];$company=$section['nm_company'];
                if($target->trans_begin()===false)throw new RuntimeException('Gagal memulai tujuan.');
                $begun[$group]=true;
                $counter=$target->query('SELECT nocab FROM pastibisa_tb_cabang WHERE nocab=? FOR UPDATE',['101']);
                if(!$counter||$counter->num_rows()!==1)throw new RuntimeException('Counter tujuan tidak tersedia.');
                $number=$numbers->get_nomor_buk('101',$group);
                if(!$number)throw new RuntimeException('Nomor tujuan tidak tersedia.');
                $collision=$target->get_where('japh',['nomor'=>$number]);
                if(!$collision||$collision->num_rows()>0)throw new RuntimeException('Nomor accounting sudah digunakan.');
                $results[$company]=['number'=>$number,'status'=>'writing'];$this->saveAttempt($key,'processing',$results);
                if($headerEngines[$group]==='MYISAM')$newHeaders[$group]=['nomor'=>$number,'no_reff'=>$fresh['header']['payment_voucher']];
                $rows=array_map(function($row)use($fresh){$row['no_transaksi']=$fresh['header']['payment_voucher'];return (object)$row;},$section['rows']);
                $header=(object)['tgl'=>$section['rows'][0]['tgl_jurnal'],'jml'=>$section['debit'],'no_reff'=>$fresh['header']['payment_voucher'],'user_id'=>$actor['name']];
                if(!$writer->post_jurnal_refill($header,$rows,$number,$group) || !$numbers->increment_nobuk('101',$group) || $target->trans_status()===false)throw new RuntimeException('Penulisan accounting gagal.');
                $results[$company]=['number'=>$number,'status'=>'prepared'];
            }
            $this->saveAttempt($key,'processing',$results);
            foreach($fresh['company_sections'] as $section){
                $group=$section['target_db'];$company=$section['nm_company'];$commitAttempted=true;
                if($targets[$group]->trans_commit()===false)throw new RuntimeException('Commit tujuan tidak dapat dikonfirmasi.');
                unset($begun[$group]);$results[$company]['status']='committed';$this->saveAttempt($key,'processing',$results);
            }
            $after=$model->debt_groups(true)->find($id);
            if($after['fingerprint']!==$fresh['fingerprint'])throw new RuntimeException('Sumber berubah setelah accounting.');
            if($db->trans_begin()===false)throw new RuntimeException('Transaksi status sumber gagal.');
            $sourceBegun=true;
            $ids=array_column($fresh['rows'],'id');$update=['sts'=>'1'];
            if($model->hasPostingColumns())$update+=['tgl_posting'=>$this->now(),'posted_by'=>$actor['id']];
            if($db->where_in('id',$ids)->where_in('sts',['','0'])->update('tr_jurnal',$update)===false || $db->affected_rows()!==count($ids) || $db->trans_status()===false || $db->trans_commit()===false){$db->trans_rollback();throw new RuntimeException('Commit status sumber gagal.');}
            $sourceBegun=false;
            $this->saveAttempt($key,'posted',$results);
            $accounting=[];foreach($results as $company=>$result)$accounting[]=['company'=>$company,'number'=>$result['number']];
            $metadata=true;
            foreach($fresh['members'] as $member){
                try{$model->save_posting_metadata(['source'=>'PCS']+$member,$actor,$accounting);}
                catch(Throwable $e){$metadata=false;}
            }
            $reply=['status'=>1,'msg'=>'Seluruh sisi jurnal hutang berhasil diposting.','outcome'=>$metadata?'posted':'posted_metadata_missing','metadata_saved'=>$metadata,'target_results'=>$results,'accounting_numbers'=>$accounting];
            if(!$metadata)$reply['warning']='Accounting berhasil; metadata belum lengkap. Jangan posting ulang.';
            return $reply;
        }catch(Throwable $error){
            if($sourceBegun)try{$db->trans_rollback();}catch(Throwable $e){$commitAttempted=true;}
            foreach($begun as $group=>$unused)try{if($targets[$group]->trans_rollback()===false)$commitAttempted=true;}catch(Throwable $e){$commitAttempted=true;}
            // Only headers whose identity was verified absent before this attempt may be compensated.
            if(!$commitAttempted)foreach($newHeaders as $group=>$identity){
                try{
                    if($targets[$group]->where($identity)->delete('japh')===false){$commitAttempted=true;continue;}
                    $remaining=$targets[$group]->get_where('japh',$identity);
                    if(!$remaining||$remaining->num_rows()!==0)$commitAttempted=true;
                }catch(Throwable $e){$commitAttempted=true;}
            }
            if(!$claimed)throw $error;
            $state=$commitAttempted?'uncertain':'failed_no_commit';
            try{$this->saveAttempt($key,$state,$results);}catch(Throwable $e){$state='uncertain';}
            return ['status'=>0,'msg'=>$state==='uncertain'?'Hasil posting memerlukan rekonsiliasi. Jangan posting ulang.':'Posting dibatalkan sebelum commit accounting; seluruh tujuan di-rollback.',
                'outcome'=>$state,'target_results'=>$results];
        }finally{
            foreach(array_reverse($locks) as $name)try{$db->query('SELECT RELEASE_LOCK(?)',[$name]);}catch(Throwable $e){log_message('error','Debt posting: lock release unavailable.');}
            $db->db_debug=$sourceDebug;foreach($targets as $group=>$target)if(array_key_exists($group,$targetDebug))$target->db_debug=$targetDebug[$group];
        }
    }
}
