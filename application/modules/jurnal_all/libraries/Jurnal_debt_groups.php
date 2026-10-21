<?php
class Jurnal_debt_groups
{
    const TYPE='Payment Hutang Petty Cash';
    const TARGETS=['STM'=>['id'=>'7','db'=>'accounting_stm'],'SUSTAIN'=>['id'=>'3','db'=>'accounting_sustain'],'VUCA'=>['id'=>'4','db'=>'accounting_vuca']];
    private $model;
    private $details=null;
    private $byId=[];

    public function __construct($model){$this->model=$model;}

    private function read($query){if($query===false)throw new RuntimeException('Gagal membaca kelompok hutang.');return $query->result_array();}

    public function all()
    {
        if($this->details!==null)return $this->details;
        $db=$this->model->db;$this->details=[];
        $rows=$this->read($db->where('jenis_transaksi',self::TYPE)->order_by('id')->get('tr_jurnal'));
        if(!$rows)return [];
        $keys=[];
        foreach($rows as $row)foreach(explode(',',$row['no_transaksi']) as $key)$keys[]=trim($key);
        $index=[];$approvals=[];
        foreach(array_chunk(array_values(array_unique($keys)),200) as $chunk){
            $payments=$this->read($db->where('tipe','petty_cash_hutang')->group_start()->where_in('id',$chunk)->or_where_in('id_payment',$chunk)->or_where_in('no_doc',$chunk)->group_end()->get('payment_approve'));
            foreach($payments as $p){$approvals[$p['id']]=$p;foreach(['id','id_payment','no_doc'] as $field)if(trim((string)$p[$field])!=='')$index[$field][(string)$p[$field]][$p['id']]=$p;}
        }
        $documents=[];
        if($approvals && $db->table_exists('tr_petty_cash_vuca_sustain'))foreach(array_chunk(array_unique(array_column($approvals,'no_doc')),200) as $chunk){
            foreach($this->read($db->select('no_payment_hutang,company')->where_in('no_payment_hutang',$chunk)->get('tr_petty_cash_vuca_sustain')) as $doc)$documents[$doc['no_payment_hutang']][]=strtoupper(trim((string)$doc['company']));
        }
        $native=[];foreach($rows as $row)$native[$row['no_transaksi']][]=$row;
        $buckets=[];
        foreach($native as $key=>$lines){
            $matches=[];$complete=true;
            foreach(array_unique(array_map('trim',explode(',',$key))) as $token){
                $candidates=[];$chosen=[];
                foreach(['id','id_payment','no_doc'] as $field){
                    $tier=$index[$field][$token] ?? [];$candidates+=$tier;if(!$chosen&&$tier)$chosen=$tier;
                }
                $candidateVouchers=array_unique(array_filter(array_map('trim',array_column($candidates,'id_payment'))));
                if(!$chosen||count($candidateVouchers)>1)$complete=false;
                $matches+=$chosen;
            }
            $vouchers=array_values(array_unique(array_filter(array_map('trim',array_column($matches,'id_payment')))));
            $voucher=$complete&&count($vouchers)===1?$vouchers[0]:null;
            $bucket=$voucher ?? 'native:'.$key;
            if(!isset($buckets[$bucket]))$buckets[$bucket]=['rows'=>[],'approvals'=>[],'voucher'=>$voucher];
            $buckets[$bucket]['rows']=array_merge($buckets[$bucket]['rows'],$lines);
            $buckets[$bucket]['approvals']+=$matches;
        }
        foreach($buckets as $bucket){
            $detail=$this->build($bucket,$documents);
            if(!$detail['relation_valid']){
                $parts=[];foreach($bucket['rows'] as $row)$parts[$row['no_transaksi']][]=$row;
                foreach($parts as $lines){$part=$bucket;$part['rows']=$lines;$this->details[]=$this->build($part,$documents);}
            }else $this->details[]=$detail;
        }
        if($db->table_exists('tr_jurnal_postings')){
            $metadata=[];foreach(array_chunk(array_keys($native),200) as $chunk){
                foreach($this->read($db->where_in('no_transaksi',$chunk)->where('jenis_transaksi',self::TYPE)->get('tr_jurnal_postings')) as $record)$metadata[$record['no_transaksi']]=$record;
            }
            foreach($this->details as &$detail){
                $numbers=[];$actors=[];$times=[];
                foreach($detail['members'] as $member){
                    $record=$metadata[$member['no_transaksi']] ?? null;if(!$record)continue;
                    $entries=json_decode($record['accounting_numbers'],true);
                    if(is_array($entries))foreach($entries as $entry){
                        $company=strtoupper((string)($entry['company'] ?? ''));$company=['7'=>'STM','3'=>'SUSTAIN','4'=>'VUCA'][$company] ?? $company;
                        if(isset(self::TARGETS[$company])&&!empty($entry['number']))$numbers[$company][]=$entry['number'];
                    }
                    if(!empty($record['posted_by_name']))$actors[]=$record['posted_by_name'];if(!empty($record['posted_at']))$times[]=$record['posted_at'];
                }
                $display=[];foreach($detail['company_sections'] as &$section){$values=array_unique($numbers[$section['nm_company']] ?? []);if($values){$section['accounting_number']=implode(', ',$values);$display[]=$section['nm_company'].': '.$section['accounting_number'];}}unset($section);
                $detail['header']['source_numbers']=$display?implode('; ',$display):null;$detail['header']['posted_by']=$actors?implode(', ',array_unique($actors)):null;
                if($times){sort($times,SORT_STRING);$detail['header']['posted_at']=end($times);}
            }unset($detail);
        }
        if($this->model->debt_registry_ready()){
            $attempts=[];foreach(array_chunk(array_column(array_column($this->details,'header'),'group_id'),200) as $chunk){
                foreach($this->read($db->where_in('group_key',$chunk)->get('tr_jurnal_debt_attempts')) as $attempt)$attempts[$attempt['group_key']]=$attempt;
            }
            foreach($this->details as &$detail){
                $attempt=$attempts[$detail['header']['group_id']] ?? null;
                if(!$attempt)continue;
                $detail['attempt']=$attempt;
                if(in_array($attempt['status'],['processing','uncertain'],true)){
                    $detail['blockers'][]='Percobaan posting memerlukan rekonsiliasi.';$detail['can_post']=false;$detail['header']['group_status']='Perlu rekonsiliasi';
                }
                if($attempt['status']==='posted'){$detail['can_post']=false;if($detail['header']['group_status']!=='Sudah posting')$detail['header']['group_status']='Perlu rekonsiliasi';}
                $numbers=json_decode($attempt['target_results'],true);$display=[];
                if(is_array($numbers))foreach($numbers as $company=>$result)if(($result['status'] ?? '')==='committed'&&!empty($result['number']))$display[]=$company.': '.$result['number'];
                $detail['header']['source_numbers']=$display?implode('; ',$display):null;
                foreach($detail['company_sections'] as &$section){
                    $result=$numbers[$section['nm_company']] ?? [];$section['accounting_status']=['committed'=>'Terkonfirmasi','prepared'=>'Disiapkan, belum terkonfirmasi','writing'=>'Sedang diproses'][$result['status'] ?? ''] ?? 'Belum terkonfirmasi';
                    $section['accounting_number']=($result['status'] ?? '')==='committed'?($result['number'] ?? null):null;
                }unset($section);
                if($attempt['status']==='posted'){$detail['header']['posted_at']=$attempt['updated_at'];$detail['header']['posted_by']=$attempt['actor_name'];$detail['header']['posted_by_id']=$attempt['actor_id'];}
            }unset($detail);
        }
        foreach($this->details as $index=>$detail)foreach($detail['rows'] as $row)$this->byId[(string)$row['id']]=$index;
        return $this->details;
    }

    private function build($bucket,$documents)
    {
        $m=$this->model;$rows=$bucket['rows'];usort($rows,function($a,$b){return (int)$a['id']<=>(int)$b['id'];});
        $header=$rows[0];$errors=[];$expected=['STM'=>0];$business=0;$docs=[];$ownerDocs=[];
        foreach($bucket['approvals'] as $p){
            if((string)($p['status'] ?? '')!=='2')$errors[]='Approval pembayaran belum berstatus Paid.';
            $docs[]=$p['no_doc'];$owners=array_values(array_unique($documents[$p['no_doc']] ?? []));
            if(count($owners)!==1 || !in_array($owners[0],['SUSTAIN','VUCA'],true)){$errors[]='Company dokumen sumber belum terverifikasi.';continue;}
            try{$amount=$m->cents($p['jumlah'] ?? '');if($amount<=0)throw new InvalidArgumentException('amount');}
            catch(Throwable $e){$errors[]='Nominal approval tidak tersedia/valid.';continue;}
            if($amount>PHP_INT_MAX-$business)throw new RuntimeException('Total hutang melampaui batas aman.');
            $business+=$amount;$expected[$owners[0]]=($expected[$owners[0]] ?? 0)+$amount;
            $ownerDocs[$owners[0]][]=$p['no_doc'];
        }
        $expected['STM']=$business;
        if(!$bucket['voucher'] || !$business)$errors[]='Relasi voucher/nominal pembayaran belum lengkap.';
        $relationValid=!$errors;
        $sections=[];$members=[];$pending=0;$posted=0;$other=0;$allD=0;$allK=0;
        foreach($rows as $row){
            $name=strtoupper(trim((string)$row['nm_company']));$target=self::TARGETS[$name] ?? null;
            if(!$target){$errors[]='Tujuan accounting tidak dikenali.';$name='TIDAK DIKENALI';}
            elseif(!in_array(trim((string)$row['id_company']),['','1',$target['id'],$name==='STM'?'5':($name==='SUSTAIN'?'6':$target['id'])],true))$errors[]='ID/nama company bertentangan.';
            if(!isset($sections[$name]))$sections[$name]=['id_company'=>$target['id'] ?? '', 'nm_company'=>$name,'target_db'=>$target['db'] ?? null,'rows'=>[],'debit_cents'=>0,'credit_cents'=>0,'valid'=>true,'statuses'=>[]];
            $d=$m->cents($row['debit']);$k=$m->cents($row['kredit']);
            if($d<0||$k<0||(($d>0||$k>0)&&trim((string)$row['coa'])==='')){$sections[$name]['valid']=false;$errors[]='Nominal/COA jurnal tidak valid.';}
            foreach([[$d,$allD],[$k,$allK]] as $sum)if($sum[0]>0 && $sum[0]>PHP_INT_MAX-$sum[1])throw new RuntimeException('Total accounting melampaui batas aman.');
            $allD+=$d;$allK+=$k;$sections[$name]['debit_cents']+=$d;$sections[$name]['credit_cents']+=$k;
            $sections[$name]['rows'][]=$row;$sections[$name]['statuses'][]=(string)$row['sts'];
            if((string)$row['sts']==='1')$posted++;elseif($m->pending($row))$pending++;else $other++;
            $members[$row['no_transaksi']]=$members[$row['no_transaksi']] ?? ['id'=>$row['id'],'no_transaksi'=>$row['no_transaksi'],'jenis_transaksi'=>self::TYPE,'id_company'=>$row['id_company']];
        }
        foreach($expected as $company=>$amount)if(!isset($sections[$company])||$amount<=0)$errors[]='Sisi accounting yang diperlukan belum lengkap.';
        $companies=[];$sectionList=[];$balanced=true;
        foreach(self::TARGETS+['TIDAK DIKENALI'=>[]] as $company=>$target){
            if(!isset($sections[$company]))continue;$section=$sections[$company];
            $section['is_balance']=$section['valid'] && $section['debit_cents']>0 && $section['debit_cents']===$section['credit_cents'];
            $balanced=$balanced&&$section['is_balance'];
            if(!$section['is_balance'] || !isset($expected[$company]) || $section['debit_cents']!==$expected[$company])$errors[]='Saldo sisi '.$company.' tidak sesuai nominal sumber.';
            $section['debit']=$m->fromCents($section['debit_cents']);$section['kredit']=$m->fromCents($section['credit_cents']);
            $section['no_referensi']=implode(', ',array_unique($company==='STM'?$docs:($ownerDocs[$company] ?? $docs)));
            $unique=array_unique($section['statuses']);$section['status']=count($unique)===1&&$unique[0]==='1'?'Sudah posting':(count($unique)===1&&in_array($unique[0],['','0'],true)?'Belum posting':'Posting sebagian');
            $companies[]=['id'=>$section['id_company'],'name'=>$company];$sectionList[]=$section;
        }
        $errors=array_values(array_unique($errors));
        $dates=array_column($rows,'tgl_jurnal');sort($dates,SORT_STRING);
        $header=array_merge($header,['source'=>'PCS','tab'=>'PAY','debt_group'=>true,'group_id'=>hash('sha256','debt|'.($relationValid?$bucket['voucher']:'native:'.$header['no_transaksi'])),
            'group_valid'=>!$errors,'display_no_transaksi'=>$bucket['voucher'] ?? $header['no_transaksi'],'payment_voucher'=>$bucket['voucher'],
            'no_referensi'=>implode(', ',array_unique($docs)),'categories'=>[self::TYPE],'category'=>self::TYPE,'nm_company'=>implode(' / ',array_column($companies,'name')),
            'companies'=>$companies,'tgl_jurnal'=>$dates[0],'debit'=>$m->fromCents($relationValid?$business:$allD),'kredit'=>$m->fromCents($relationValid?$business:$allK),
            'relation_warning'=>$relationValid?null:'Relasi belum lengkap',
            'accounting_debit'=>$m->fromCents($allD),'accounting_kredit'=>$m->fromCents($allK),'source_numbers'=>null,'posted_at'=>null,'posted_by'=>null,'posted_by_id'=>null,
            'keterangan'=>'Pembayaran Hutang Petty Cash - '.implode(', ',array_unique($docs)),
            'extra_search'=>implode(' ',array_merge(array_keys($members),array_keys($bucket['approvals']))),'petty_payment_eligible'=>true,
            'group_status'=>$posted===count($rows)?'Sudah posting':($posted>0?'Posting sebagian':($other?'Status tidak valid':'Belum posting'))]);
        $canonicalRows=[];foreach($rows as $row)$canonicalRows[]=array_intersect_key($row,array_flip(['id','no_transaksi','jenis_transaksi','nm_company','id_company','tgl_jurnal','coa','keterangan','debit','kredit','sts']));
        return ['header'=>$header,'rows'=>$rows,'company_sections'=>$sectionList,'members'=>array_values($members),'blockers'=>$errors,'relation_valid'=>$relationValid,
            'fingerprint'=>hash('sha256',serialize([$canonicalRows,$bucket['approvals'],$expected])), 'is_balance'=>$balanced,
            'can_post'=>!$errors && $pending===count($rows)];
    }

    public function find($id)
    {
        $this->all();if(isset($this->byId[(string)$id]))return $this->details[$this->byId[(string)$id]];
        throw new InvalidArgumentException('Kelompok hutang tidak tersedia.');
    }
}
