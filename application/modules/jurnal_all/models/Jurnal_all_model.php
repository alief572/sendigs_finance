<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once dirname(__DIR__, 2).'/expense/libraries/Expense_company.php';
require_once dirname(__DIR__).'/libraries/Jurnal_debt_groups.php';
require_once dirname(__DIR__).'/libraries/Payment_reference.php';

class Jurnal_all_model extends BF_Model
{
    const SOURCES = [
        'INV'=>['Invoicing'], 'KWD'=>['Kwitansi Deposit'], 'RCV'=>['Penerimaan Piutang'],
        'PAY'=>['Payment','Transport','Transportasi','Kasbon','Expense'],
        'EXP'=>['Expense Report','Expense Report Consultant','Pengembalian Kasbon'],
        'PCS'=>['Petty Cash','Refill Pettycash','Payment Hutang Petty Cash']
    ];
    const TITLES = ['INV'=>'Jurnal Invoicing','RCV'=>'Jurnal Penerimaan','PAY'=>'Jurnal Pembayaran','EXP'=>'Jurnal Expense Report','PCS'=>'Jurnal Pencatatan Petty Cash'];
    private $metadataReady=null;
    private $payments=[];
    private $paymentKeys=[];
    private $paymentCompanions=[];
    private $paymentCompanionKeys=[];
    private $glDbs=[];
    private $userMap=null;
    private $postingColumnsReady=null;
    private $debtResolver=null;
    private $paymentReference=null;
    private $reportPaymentHistory=null;
    private $invoiceHrisDb=null;

    public function debt_groups($refresh=false)
    {
        if($refresh || $this->debtResolver===null)$this->debtResolver=new Jurnal_debt_groups($this);
        return $this->debtResolver;
    }

    public function debt_posting_ready()
    {
        return $this->debt_registry_ready() && $this->posting_metadata_ready();
    }

    public function debt_registry_ready()
    {
        if(!$this->db->table_exists('tr_jurnal_debt_attempts'))return false;
        foreach(['group_key','fingerprint','status','actor_id','actor_name','target_results','created_at','updated_at'] as $field)if(!$this->db->field_exists($field,'tr_jurnal_debt_attempts'))return false;
        return true;
    }

    public function hasPostingColumns()
    {
        if ($this->postingColumnsReady !== null) return $this->postingColumnsReady;
        if ($this->db && method_exists($this->db, 'field_exists')) {
            return $this->postingColumnsReady = ($this->db->field_exists('tgl_posting', 'tr_jurnal') && $this->db->field_exists('posted_by', 'tr_jurnal'));
        }
        return $this->postingColumnsReady = false;
    }

    public function source($type)
    {
        foreach (self::SOURCES as $key=>$types) if (in_array($type, $types, true)) return $key;
        return null;
    }

    public function tab($type)
    {
        if ($type === 'Kwitansi Deposit') return 'INV';
        return in_array($type, ['Refill Pettycash','Payment Hutang Petty Cash'], true) ? 'PAY' : $this->source($type);
    }

    public function filters($values)
    {
        $tab = $this->scalar($values['tab'] ?? 'INV');
        if (!isset(self::TITLES[$tab])) throw new InvalidArgumentException('Tab jurnal tidak valid.');
        $year = $values['year'] ?? date('Y');
        if (!is_scalar($year)) throw new InvalidArgumentException('Periode jurnal tidak valid.');
        $year = $this->scalar($year);
        $month = $this->scalar($values['month'] ?? '');
        if (($year !== '' && (!preg_match('/^\d{4}$/D', $year) || (int)$year < 1900 || (int)$year > 9998))
            || ($month !== '' && (!ctype_digit($month) || (int)$month < 1 || (int)$month > 12))) {
            throw new InvalidArgumentException('Periode jurnal tidak valid.');
        }
        $search = is_array($values['search'] ?? null) ? ($values['search']['value'] ?? '') : ($values['q'] ?? '');
        return ['tab'=>$tab,'year'=>$year === '' ? '' : (int)$year,'month'=>$month,'company'=>$this->scalar($values['company'] ?? ''),
            'category'=>$tab === 'PAY' ? $this->scalar($values['category'] ?? '') : '', 'q'=>$this->scalar($search)];
    }

    public function groups($values, $posted = false, $withSearch = true)
    {
        $f = $this->filters($values);
        $types = $f['tab'] === 'PAY' ? array_merge(self::SOURCES['PAY'], ['Refill Pettycash','Payment Hutang Petty Cash'])
            : ($f['tab'] === 'PCS' ? ['Petty Cash'] : self::SOURCES[$f['tab']]);
        $types=array_values(array_diff($types,[Jurnal_debt_groups::TYPE]));
        if ($f['tab'] === 'INV') $types=array_merge($types,self::SOURCES['KWD']);
        $select = "MIN(a.id) AS id, a.no_transaksi, a.jenis_transaksi, MIN(a.tgl_jurnal) AS tgl_jurnal,
            MAX(a.nm_company) AS nm_company, MAX(a.id_company) AS id_company, MAX(a.nm_divisi) AS nm_divisi,
            MAX(a.keterangan) AS keterangan, SUM(a.debit) AS debit, SUM(a.kredit) AS kredit,
            MIN(NULLIF(a.no_jurnal,'')) AS source_numbers,
            SUM(CASE WHEN a.debit > 0 OR a.kredit > 0 THEN 1 ELSE 0 END) AS active_lines,
            SUM(CASE WHEN a.coa NOT IN ('1102-01-01','1106-01-02','7201-01-04','1106-01-05') AND (a.debit > 0 OR a.kredit > 0) THEN 1 ELSE 0 END) AS receipt_lines";
        if ($this->hasPostingColumns()) {
            $select .= ", MAX(a.tgl_posting) AS db_tgl_posting, MAX(a.posted_by) AS db_posted_by";
        }
        $this->db->select($select, false);
        $this->db->from('tr_jurnal a')->where_in('a.jenis_transaksi', $types);
        if (!$posted && $f['tab'] === 'PCS') {
            $this->db->where('EXISTS (SELECT 1 FROM tr_expense_petty_cash pc WHERE pc.no_pencatatan = a.no_transaksi)', null, false);
        }
        if ($posted) $this->db->where('a.sts', '1');
        else $this->db->group_start()
            ->group_start()->where_in('a.jenis_transaksi', array_merge(self::SOURCES['INV'],self::SOURCES['KWD'],self::SOURCES['EXP']))
                ->group_start()->where_in('a.sts',['','0'])->or_where('a.sts IS NULL',null,false)->group_end()->group_end()
            ->or_group_start()->where_in('a.jenis_transaksi',array_merge(self::SOURCES['RCV'],self::SOURCES['PAY']))->where('a.sts <>','1')->group_end()
            ->or_group_start()->where_in('a.jenis_transaksi',self::SOURCES['PCS'])->where_in('a.sts',['','0'])->group_end()
            ->group_end();
        // Group before filtering the period so a transaction spanning dates is never split.
        $this->db->group_by(['a.no_transaksi','a.jenis_transaksi']);
        if ($f['tab'] === 'EXP') $this->db->group_by('a.id_company');
        if ($f['year'] !== '') {
            $start = sprintf('%04d-%02d-01',$f['year'],$f['month'] === '' ? 1 : (int)$f['month']);
            $end = $f['month'] === '' ? sprintf('%04d-01-01',$f['year']+1) : date('Y-m-d',strtotime($start.' +1 month'));
            if ($f['tab'] === 'INV') {
                $match = 'a.tgl_jurnal >= '.$this->db->escape($start).' AND a.tgl_jurnal < '.$this->db->escape($end);
                $this->db->having('SUM(CASE WHEN '.$match.' THEN 1 ELSE 0 END) >',0,false);
            } else {
                $this->db->having('MIN(a.tgl_jurnal) >=',$start)->having('MIN(a.tgl_jurnal) <',$end);
            }
        } elseif ($f['month'] !== '') {
            $pattern = $this->db->escape(sprintf('____-%02d-%%',(int)$f['month']));
            if ($f['tab'] === 'INV') {
                $this->db->having('SUM(CASE WHEN a.tgl_jurnal LIKE '.$pattern.' THEN 1 ELSE 0 END) >',0,false);
            } else {
                $this->db->having('MIN(a.tgl_jurnal) LIKE '.$pattern,null,false);
            }
        }
        $rows = $this->read($this->db->get())->result_array();
        $this->enrich($rows, $posted);
        if($f['tab']==='PAY')foreach($this->debt_groups()->all() as $detail){
            $header=$detail['header'];
            if($posted?($header['group_status']!=='Sudah posting'):($header['group_status']==='Sudah posting'))continue;
            if($f['year']!=='' && (int)substr($header['tgl_jurnal'],0,4)!==$f['year'])continue;
            if($f['month']!=='' && (int)substr($header['tgl_jurnal'],5,2)!==(int)$f['month'])continue;
            $rows[]=$header;
        }
        $result = [];
        foreach ($rows as $row) {
            if (!$posted && !$this->eligible($row)) continue;
            if ($f['company'] !== '' && !$this->companyMatches($row, $f['company'])) continue;
            if ($f['category'] !== '' && !in_array($f['category'],$row['categories'],true)) continue;
            $row['debit'] = $this->decimal($row['debit']); $row['kredit'] = $this->decimal($row['kredit']);
            $result[] = $row;
        }
        if ($withSearch && trim($f['q']) !== '') $result = $this->search($result,$f['q'],$posted);
        usort($result,function($a,$b){return strcmp($b['tgl_jurnal'],$a['tgl_jurnal']) ?: ($b['id'] <=> $a['id']);});
        return array_values($result);
    }

    public function listing($values, $posted = false)
    {
        $all = $this->groups($values,$posted,false);
        $f = $this->filters($values);
        $rows = trim($f['q']) === '' ? $all : $this->search($all,$f['q'],$posted);
        $totalD=0; $totalK=0;
        foreach ($rows as $row) { $totalD += $this->cents($row['debit']); $totalK += $this->cents($row['kredit']); }
        $start=max(0,(int)$this->scalar($values['start'] ?? 0));
        $length=max(1,min(100,(int)$this->scalar($values['length'] ?? 10)));
        $order=is_array($values['order'] ?? null) ? ($values['order'][0] ?? []) : [];
        $columns=['tgl_jurnal','display_no_transaksi','no_referensi','category','keterangan','nm_company','debit','kredit'];
        $column=$columns[(int)$this->scalar($order['column'] ?? -1)] ?? null;
        if ($column) {
            $direction=strtolower($this->scalar($order['dir'] ?? 'desc'))==='asc' ? 1 : -1;
            usort($rows,function($a,$b)use($column,$direction){
                $cmp=in_array($column,['debit','kredit'],true) ? $this->cents($a[$column]) <=> $this->cents($b[$column]) : strnatcasecmp($a[$column],$b[$column]);
                return $cmp ? $direction*$cmp : ($b['id'] <=> $a['id']);
            });
        }
        return ['draw'=>(int)$this->scalar($values['draw'] ?? 0),'recordsTotal'=>count($all),'recordsFiltered'=>count($rows),
            'data'=>array_slice($rows,$start,$length),'totals'=>['debit'=>$this->fromCents($totalD),'kredit'=>$this->fromCents($totalK)]];
    }

    public function summary($filters)
    {
        $result=[];
        foreach (self::TITLES as $tab=>$title) {
            $values=is_array($filters[$tab] ?? null) ? $filters[$tab] : [];
            $values['tab']=$tab;
            $rows=$this->groups($values,false,false); $amount=0; $oldest=null;
            foreach ($rows as $row) { $amount += $this->cents($row['debit']); if ($oldest===null || $row['tgl_jurnal']<$oldest) $oldest=$row['tgl_jurnal']; }
            $result[$tab]=['count'=>count($rows),'debit'=>$this->fromCents($amount),'oldest'=>$oldest,
                'age'=>$oldest ? max(0,(int)floor((strtotime(date('Y-m-d'))-strtotime($oldest))/86400)) : null];
        }
        return $result;
    }

    public function options($values, $posted = false)
    {
        $f=$this->filters($values); $f['company']=''; $f['category']=''; $f['q']='';
        $rows=$this->groups($f,$posted,false); $companies=[]; $categories=[];
        foreach ($rows as $row) {
            foreach ($row['companies'] as $company) $companies[$company['id']]=$company['name'];
            foreach ($row['categories'] as $category) $categories[$category]=$category;
        }
        asort($companies); asort($categories);
        $years=$this->read($this->db->select('MIN(tgl_jurnal) AS first_date, MAX(tgl_jurnal) AS last_date')->get('tr_jurnal'))->row_array();
        $first=max(1900,(int)substr($years['first_date'] ?? date('Y'),0,4));
        $last=min(9998,max((int)date('Y'),(int)substr($years['last_date'] ?? date('Y'),0,4)));
        return ['companies'=>$companies,'categories'=>array_values($categories),'years'=>range($last,min($first,(int)date('Y')),-1)];
    }

    public function detail($id, $posted)
    {
        if (!is_scalar($id) || !preg_match('/^[1-9]\d*$/D',(string)$id)) throw new InvalidArgumentException('ID jurnal tidak valid.');
        $anchor=$this->read($this->db->get_where('tr_jurnal',['id'=>(int)$id]))->row_array();
        if (!$anchor || !$this->source($anchor['jenis_transaksi'])) throw new InvalidArgumentException('Jurnal tidak tersedia.');
        if($anchor['jenis_transaksi']===Jurnal_debt_groups::TYPE){
            $detail=$this->debt_groups()->find($id);
            if($posted?($detail['header']['group_status']!=='Sudah posting'):($detail['header']['group_status']==='Sudah posting'))throw new InvalidArgumentException('Status kelompok hutang berubah. Muat ulang daftar.');
            return $detail;
        }
        $this->db->where(['no_transaksi'=>$anchor['no_transaksi'],'jenis_transaksi'=>$anchor['jenis_transaksi']]);
        if ($this->source($anchor['jenis_transaksi'])==='EXP') $this->db->where('id_company',$anchor['id_company']);
        $rows=$this->read($this->db->order_by('id','asc')->get('tr_jurnal'))->result_array();
        $debit=0; $credit=0; $validAmounts=true;
        foreach ($rows as $row) {
            if ($posted ? (string)$row['sts']!=='1' : !$this->pending($row)) throw new InvalidArgumentException('Status kelompok jurnal berubah atau sudah diposting.');
            $d=$this->cents($row['debit']); $c=$this->cents($row['kredit']);
            if ($d<0||$c<0) $validAmounts=false;
            $debit+=$d; $credit+=$c;
        }
        if (!$rows) throw new InvalidArgumentException('Detail jurnal tidak tersedia.');
        $anchor['debit']=$this->fromCents($debit); $anchor['kredit']=$this->fromCents($credit);
        $anchor['source_numbers']=implode(', ',array_unique(array_filter(array_column($rows,'no_jurnal'))));
        $anchor['receipt_lines']=count(array_filter($rows,function($r){return !in_array($r['coa'],['1102-01-01','1106-01-02','7201-01-04','1106-01-05'],true)&&($this->cents($r['debit'])>0||$this->cents($r['kredit'])>0);}));
        $anchor['active_lines']=count(array_filter($rows,function($r){return $this->cents($r['debit'])>0||$this->cents($r['kredit'])>0;}));
        $headers=[$anchor]; $this->enrich($headers, $posted); $anchor=$headers[0];
        if (!$posted && !$this->eligible($anchor)) throw new InvalidArgumentException('Dokumen sumber jurnal tidak tersedia.');
        if (!$posted && $anchor['source'] === 'EXP') {
            try {
                $company = $this->validate_expense_company($rows);
                $anchor['accounting_target'] = $company['target'];
                $anchor['company_error'] = '';
            } catch (Throwable $error) {
                $anchor['company_error'] = $error instanceof DomainException ? $error->getMessage() : 'Validasi sumber company belum dapat dilakukan; posting diblokir.';
            }
        }
        $lineDivs = [];
        foreach ($rows as &$r) {
            $r=$this->periodicLineDivision($anchor,$r);
            $r=$this->expenseLineDivision($anchor,$r);
            if (empty($r['nm_divisi']) && !empty($anchor['nm_divisi'])) {
                $r['nm_divisi'] = $anchor['nm_divisi'];
            }
            if (!empty($r['nm_divisi'])) $lineDivs[] = $r['nm_divisi'];
        }
        unset($r);
        if ($lineDivs) {
            $lineDivMap = $this->divisionMap($lineDivs);
            if ($lineDivMap) {
                foreach ($rows as &$r) {
                    $c = trim((string)($r['nm_divisi'] ?? ''));
                    if (isset($lineDivMap[$c])) $r['nm_divisi'] = $lineDivMap[$c];
                }
                unset($r);
            }
        }
        return ['header'=>$anchor,'rows'=>$rows,'is_balance'=>$validAmounts && $debit>0 && $debit===$credit,'can_post'=>empty($anchor['payment_reference_error']) && empty($anchor['company_error'])];
    }

    public function validate_expense_company($rows)
    {
        Expense_company::storedCompany($rows);
        if (!function_exists('get_instance')) throw new RuntimeException('Koneksi sumber company tidak tersedia.');
        $ci = &get_instance();
        return (new Expense_company($this->db, $ci->load->database('consultant', true)))->validateJournal($rows);
    }

    public function posting_metadata_ready()
    {
        if ($this->metadataReady!==null) return $this->metadataReady;
        if (!$this->db->table_exists('tr_jurnal_postings')) return $this->metadataReady=false;
        foreach (['group_key','source','no_transaksi','jenis_transaksi','id_company','posted_at','posted_by','posted_by_name','accounting_numbers'] as $field) {
            if (!$this->db->field_exists($field,'tr_jurnal_postings')) return $this->metadataReady=false;
        }
        return $this->metadataReady=true;
    }

    public function save_posting_metadata($header,$actor,$numbers)
    {
        if (!$this->posting_metadata_ready()) throw new RuntimeException('Schema metadata posting belum tersedia.');
        if (!is_array($numbers) || !$numbers || count($numbers)>20) throw new RuntimeException('Nomor final accounting tidak tersedia.');
        $verified=[];
        foreach ($numbers as $entry) {
            if (!is_array($entry) || !is_scalar($entry['number'] ?? null) || !is_scalar($entry['company'] ?? null)) throw new RuntimeException('Nomor final accounting tidak valid.');
            $number=trim((string)$entry['number']); $company=trim((string)$entry['company']);
            if ($number==='' || strlen($number)>255 || strlen($company)>100) throw new RuntimeException('Nomor final accounting tidak valid.');
            $verified[json_encode([$company,$number])]=['company'=>$company,'number'=>$number];
        }
        if (!is_scalar($actor['id'] ?? null) || !is_scalar($actor['name'] ?? null) || trim((string)$actor['id'])==='' || trim((string)$actor['name'])==='') throw new RuntimeException('User posting tidak tersedia.');
        $record=['group_key'=>hash('sha256',$this->key($header)),'source'=>$header['source'],'no_transaksi'=>$header['no_transaksi'],
            'jenis_transaksi'=>$header['jenis_transaksi'],'id_company'=>$header['source']==='EXP'?(string)$header['id_company']:null,
            'posted_at'=>(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s'),
            'posted_by'=>(string)$actor['id'],'posted_by_name'=>mb_substr((string)$actor['name'],0,191,'UTF-8'),
            'accounting_numbers'=>json_encode(array_values($verified))];
        $debug=$this->db->db_debug; $this->db->db_debug=false; $begun=false;
        try {
            if ($this->db->trans_begin()===false) throw new RuntimeException('Gagal memulai metadata posting.');
            $begun=true;
            if ($this->db->insert('tr_jurnal_postings',$record)===false || $this->db->trans_status()===false) throw new RuntimeException('Gagal menyimpan metadata posting.');
            if ($this->db->trans_commit()===false) throw new RuntimeException('Commit metadata posting gagal.');
        } catch (Throwable $error) { if ($begun) $this->db->trans_rollback(); throw $error; }
        finally { $this->db->db_debug=$debug; }
    }

    private function getGlDb($companyId)
    {
        $id = (string)$companyId;
        $group = in_array($id, ['1', '4'], true) ? 'accounting_vuca' : (in_array($id, ['3', '6'], true) ? 'accounting_sustain' : 'accounting_stm');
        if (isset($this->glDbs[$group])) return $this->glDbs[$group];
        if (!function_exists('get_instance')) return null;
        $ci = &get_instance();
        if (!isset($ci->load)) return null;
        try {
            $db = $ci->load->database($group, true);
            if ($db && !empty($db->conn_id)) {
                return $this->glDbs[$group] = $db;
            }
        } catch (Throwable $e) {
            return null;
        }
        return null;
    }

    private function resolveUserNames($ids)
    {
        if (!$ids) return [];
        $missing = array_diff(array_unique(array_filter(array_map('trim', $ids))), array_keys($this->userMap ?? []));
        if ($missing && $this->db && method_exists($this->db, 'table_exists') && $this->db->table_exists('users')) {
            $numerics = array_values(array_filter($missing, 'is_numeric'));
            $strings = array_values(array_filter($missing, function($v) { return !is_numeric($v); }));
            $this->db->select('id_user, username, nm_lengkap');
            if ($numerics && $strings) {
                $this->db->group_start()->where_in('id_user', $numerics)->or_where_in('username', $strings)->group_end();
            } elseif ($numerics) {
                $this->db->where_in('id_user', $numerics);
            } elseif ($strings) {
                $this->db->where_in('username', $strings);
            }
            $res = $this->db->get('users');
            if ($res) {
                foreach ($res->result_array() as $u) {
                    $name = trim((string)($u['nm_lengkap'] ?: $u['username']));
                    if (!empty($u['id_user'])) $this->userMap[(string)$u['id_user']] = $name;
                    if (!empty($u['username'])) $this->userMap[$u['username']] = $name;
                }
            }
        }
        return $this->userMap ?? [];
    }

    private function resolveGlMetadata(&$rows)
    {
        $pending = [];
        foreach ($rows as $idx => &$row) {
            if (empty($row['source_numbers'])) {
                $pending[$idx] = &$row;
            }
        }
        unset($row);
        if (!$pending) return;

        $buckets = [];
        foreach ($pending as $idx => &$row) {
            $companyId = (string)($row['id_company'] ?? '');
            $src = $row['source'] ?? '';
            if ($src === 'EXP') {
                try { $dbKey = Expense_company::target($companyId); }
                catch (DomainException $error) { continue; }
            } elseif ($src === 'PAY') {
                $dbKey = in_array($companyId, ['1', '4'], true) ? 'accounting_vuca' : (in_array($companyId, ['3', '6'], true) ? 'accounting_sustain' : 'accounting_stm');
            } else {
                $dbKey = in_array($companyId, ['1', '6', '7'], true) ? 'accounting_stm' : ($companyId === '4' ? 'accounting_vuca' : 'accounting_sustain');
            }
            $buckets[$dbKey][$idx] = &$row;
        }
        unset($row);

        $allUserIds = [];

        foreach ($buckets as $dbKey => &$groupRows) {
            $gl = $this->getGlDb($dbKey === 'accounting_stm' ? '7' : ($dbKey === 'accounting_vuca' ? '4' : '3'));
            if (!$gl || !method_exists($gl, 'table_exists')) continue;

            $invRows = []; $rcvRows = []; $payRows = []; $expRows = []; $pcsRows = [];
            foreach ($groupRows as $idx => &$r) {
                $src = $r['source'] ?? '';
                if ($src === 'INV') $invRows[$idx] = &$r;
                elseif ($src === 'RCV') $rcvRows[$idx] = &$r;
                elseif ($src === 'PAY') $payRows[$idx] = &$r;
                elseif ($src === 'EXP') $expRows[$idx] = &$r;
                elseif ($src === 'PCS') $pcsRows[$idx] = &$r;
            }
            unset($r);

            // 1. Invoicing
            if ($invRows && $gl->table_exists('jurnal')) {
                $invMap = [];
                foreach ($invRows as $idx => $r) {
                    $invNo = trim((string)($r['invoice_number'] ?? $r['display_no_transaksi'] ?? ''));
                    if ($invNo !== '') $invMap[$invNo][] = $idx;
                }
                if ($invMap) {
                    $keys = array_keys($invMap);
                    $jurnalNomors = [];
                    $glData = [];
                    foreach (array_chunk($keys, 200) as $chunk) {
                        $q = $gl->select('nomor, tanggal, created_on, created_by, no_reff')->where_in('no_reff', $chunk)->get('jurnal');
                        if ($q) foreach ($q->result_array() as $j) {
                            $glData[$j['no_reff']] = ['nomor' => $j['nomor'], 'tgl' => $j['created_on'] ?: $j['tanggal'], 'user' => $j['created_by']];
                            if (!empty($j['nomor'])) $jurnalNomors[] = $j['nomor'];
                        }
                    }
                    if ($jurnalNomors && $gl->table_exists('javh')) {
                        foreach (array_chunk(array_unique($jurnalNomors), 200) as $chunk) {
                            $q = $gl->select('nomor, tgl, user_id')->where_in('nomor', $chunk)->get('javh');
                            if ($q) foreach ($q->result_array() as $h) {
                                foreach ($glData as &$gd) {
                                    if ($gd['nomor'] === $h['nomor']) {
                                        if (!empty($h['tgl'])) $gd['tgl'] = $h['tgl'];
                                        if (!empty($h['user_id'])) $gd['user'] = $h['user_id'];
                                    }
                                }
                                unset($gd);
                            }
                        }
                    }
                    $unmatched = array_diff($keys, array_keys($glData));
                    if ($unmatched && $gl->table_exists('javh')) {
                        foreach ($unmatched as $uInv) {
                            $q = $gl->select('nomor, tgl, user_id')->like('keterangan', $uInv)->limit(1)->get('javh');
                            if ($q && ($h = $q->row_array())) {
                                $glData[$uInv] = ['nomor' => $h['nomor'], 'tgl' => $h['tgl'], 'user' => $h['user_id']];
                            }
                        }
                    }
                    foreach ($glData as $invNo => $meta) {
                        if (!empty($meta['user'])) $allUserIds[] = (string)$meta['user'];
                        foreach ($invMap[$invNo] ?? [] as $targetIdx) {
                            $invRows[$targetIdx]['source_numbers'] = $meta['nomor'];
                            if (empty($invRows[$targetIdx]['posted_at'])) {
                                $invRows[$targetIdx]['posted_at'] = $meta['tgl'];
                            }
                            if (empty($invRows[$targetIdx]['posted_by_id'])) {
                                $invRows[$targetIdx]['posted_by_id'] = (string)($meta['user'] ?? '');
                            }
                        }
                    }
                }
            }

            // 2. Penerimaan Piutang
            if ($rcvRows && $gl->table_exists('jarh')) {
                $rcvMap = [];
                foreach ($rcvRows as $idx => $r) {
                    $no = trim((string)($r['no_transaksi'] ?? ''));
                    if ($no !== '') $rcvMap[$no][] = $idx;
                }
                if ($rcvMap) {
                    $keys = array_keys($rcvMap);
                    $glData = [];
                    foreach (array_chunk($keys, 200) as $chunk) {
                        $q = $gl->select('nomor, tgl, user_id, no_reff')->where_in('no_reff', $chunk)->get('jarh');
                        if ($q) foreach ($q->result_array() as $h) {
                            $glData[$h['no_reff']] = ['nomor' => $h['nomor'], 'tgl' => $h['tgl'], 'user' => $h['user_id']];
                        }
                    }
                    $unmatched = array_diff($keys, array_keys($glData));
                    if ($unmatched && $gl->table_exists('jurnal')) {
                        foreach (array_chunk($unmatched, 200) as $chunk) {
                            $q = $gl->select('nomor, tanggal, created_on, created_by, no_reff')->where_in('no_reff', $chunk)->get('jurnal');
                            if ($q) foreach ($q->result_array() as $j) {
                                $glData[$j['no_reff']] = ['nomor' => $j['nomor'], 'tgl' => $j['created_on'] ?: $j['tanggal'], 'user' => $j['created_by']];
                            }
                        }
                    }
                    foreach ($glData as $no => $meta) {
                        if (!empty($meta['user'])) $allUserIds[] = (string)$meta['user'];
                        foreach ($rcvMap[$no] ?? [] as $targetIdx) {
                            $rcvRows[$targetIdx]['source_numbers'] = $meta['nomor'];
                            if (empty($rcvRows[$targetIdx]['posted_at'])) {
                                $rcvRows[$targetIdx]['posted_at'] = $meta['tgl'];
                            }
                            if (empty($rcvRows[$targetIdx]['posted_by_id'])) {
                                $rcvRows[$targetIdx]['posted_by_id'] = (string)($meta['user'] ?? '');
                            }
                        }
                    }
                }
            }

            // 3. Payment
            if ($payRows && $gl->table_exists('japh')) {
                $payMap = [];
                foreach ($payRows as $idx => $r) {
                    $raw = trim((string)($r['no_transaksi'] ?? ''));
                    if ($raw !== '') {
                        $split = array_unique(array_filter(array_map('trim', explode(',', $raw))));
                        foreach ($split as $part) $payMap[$part][] = $idx;
                    }
                }
                if ($payMap) {
                    $keys = array_keys($payMap);
                    $glData = [];
                    foreach (array_chunk($keys, 200) as $chunk) {
                        $q = $gl->select('nomor, tgl, user_id, no_reff')->where_in('no_reff', $chunk)->get('japh');
                        if ($q) foreach ($q->result_array() as $h) {
                            $glData[$h['no_reff']] = ['nomor' => $h['nomor'], 'tgl' => $h['tgl'], 'user' => $h['user_id']];
                        }
                    }
                    $unmatched = array_diff($keys, array_keys($glData));
                    if ($unmatched && $gl->table_exists('jurnal')) {
                        foreach (array_chunk($unmatched, 200) as $chunk) {
                            $q = $gl->select('nomor, tanggal, created_on, created_by, no_reff')->where_in('no_reff', $chunk)->get('jurnal');
                            if ($q) foreach ($q->result_array() as $j) {
                                $glData[$j['no_reff']] = ['nomor' => $j['nomor'], 'tgl' => $j['created_on'] ?: $j['tanggal'], 'user' => $j['created_by']];
                            }
                        }
                    }
                    foreach ($glData as $no => $meta) {
                        if (!empty($meta['user'])) $allUserIds[] = (string)$meta['user'];
                        foreach ($payMap[$no] ?? [] as $targetIdx) {
                            $payRows[$targetIdx]['source_numbers'] = $meta['nomor'];
                            if (empty($payRows[$targetIdx]['posted_at'])) {
                                $payRows[$targetIdx]['posted_at'] = $meta['tgl'];
                            }
                            if (empty($payRows[$targetIdx]['posted_by_id'])) {
                                $payRows[$targetIdx]['posted_by_id'] = (string)($meta['user'] ?? '');
                            }
                        }
                    }
                }
            }

            // 4. Expense Report
            if ($expRows && ($gl->table_exists('javh') || $gl->table_exists('jurnal'))) {
                $expMap = [];
                foreach ($expRows as $idx => $r) {
                    $no = trim((string)($r['no_transaksi'] ?? ''));
                    if ($no !== '') $expMap[$no][] = $idx;
                }
                if ($expMap) {
                    $keys = array_keys($expMap);
                    $glData = [];
                    if ($gl->table_exists('jurnal')) {
                        foreach (array_chunk($keys, 200) as $chunk) {
                            $q = $gl->select('nomor, tanggal, created_on, created_by, no_reff')->where_in('no_reff', $chunk)->get('jurnal');
                            if ($q) foreach ($q->result_array() as $j) {
                                $glData[$j['no_reff']] = ['nomor' => $j['nomor'], 'tgl' => $j['created_on'] ?: $j['tanggal'], 'user' => $j['created_by']];
                            }
                        }
                    }
                    $unmatched = array_diff($keys, array_keys($glData));
                    if ($unmatched && $gl->table_exists('javh')) {
                        foreach ($unmatched as $uExp) {
                            $q = $gl->select('nomor, tgl, user_id')->like('keterangan', $uExp)->limit(1)->get('javh');
                            if ($q && ($h = $q->row_array())) {
                                $glData[$uExp] = ['nomor' => $h['nomor'], 'tgl' => $h['tgl'], 'user' => $h['user_id']];
                            }
                        }
                    }
                    foreach ($glData as $no => $meta) {
                        if (!empty($meta['user'])) $allUserIds[] = (string)$meta['user'];
                        foreach ($expMap[$no] ?? [] as $targetIdx) {
                            $expRows[$targetIdx]['source_numbers'] = $meta['nomor'];
                            if (empty($expRows[$targetIdx]['posted_at'])) {
                                $expRows[$targetIdx]['posted_at'] = $meta['tgl'];
                            }
                            if (empty($expRows[$targetIdx]['posted_by_id'])) {
                                $expRows[$targetIdx]['posted_by_id'] = (string)($meta['user'] ?? '');
                            }
                        }
                    }
                }
            }

            // 5. Petty Cash
            if ($pcsRows && ($gl->table_exists('japh') || $gl->table_exists('jurnal'))) {
                $pcsMap = [];
                foreach ($pcsRows as $idx => $r) {
                    $no = trim((string)($r['no_transaksi'] ?? ''));
                    if ($no !== '') $pcsMap[$no][] = $idx;
                }
                if ($pcsMap) {
                    $keys = array_keys($pcsMap);
                    $glData = [];
                    if ($gl->table_exists('japh')) {
                        foreach (array_chunk($keys, 200) as $chunk) {
                            $q = $gl->select('nomor, tgl, user_id, no_reff')->where_in('no_reff', $chunk)->get('japh');
                            if ($q) foreach ($q->result_array() as $h) {
                                $glData[$h['no_reff']] = ['nomor' => $h['nomor'], 'tgl' => $h['tgl'], 'user' => $h['user_id']];
                            }
                        }
                    }
                    $unmatched = array_diff($keys, array_keys($glData));
                    if ($unmatched && $gl->table_exists('jurnal')) {
                        foreach (array_chunk($unmatched, 200) as $chunk) {
                            $q = $gl->select('nomor, tanggal, created_on, created_by, no_reff')->where_in('no_reff', $chunk)->get('jurnal');
                            if ($q) foreach ($q->result_array() as $j) {
                                $glData[$j['no_reff']] = ['nomor' => $j['nomor'], 'tgl' => $j['created_on'] ?: $j['tanggal'], 'user' => $j['created_by']];
                            }
                        }
                    }
                    foreach ($glData as $no => $meta) {
                        if (!empty($meta['user'])) $allUserIds[] = (string)$meta['user'];
                        foreach ($pcsMap[$no] ?? [] as $targetIdx) {
                            $pcsRows[$targetIdx]['source_numbers'] = $meta['nomor'];
                            if (empty($pcsRows[$targetIdx]['posted_at'])) {
                                $pcsRows[$targetIdx]['posted_at'] = $meta['tgl'];
                            }
                            if (empty($pcsRows[$targetIdx]['posted_by_id'])) {
                                $pcsRows[$targetIdx]['posted_by_id'] = (string)($meta['user'] ?? '');
                            }
                        }
                    }
                }
            }
        }
        unset($groupRows);

        $neededUserIds = $allUserIds;
        foreach ($pending as $r) {
            $uid = (string)($r['posted_by_id'] ?? '');
            if ($uid !== '' && empty($r['posted_by'])) {
                $neededUserIds[] = $uid;
            }
        }
        if ($neededUserIds) {
            $userMap = $this->resolveUserNames(array_unique($neededUserIds));
            foreach ($pending as &$r) {
                $uid = (string)($r['posted_by_id'] ?? '');
                if ($uid !== '' && empty($r['posted_by'])) {
                    $r['posted_by'] = $userMap[$uid] ?? $uid;
                }
            }
            unset($r);
        }
    }

    private function postingMetadata(&$rows)
    {
        if (!$rows) return;
        if ($this->posting_metadata_ready()) {
            $keys=[]; foreach ($rows as $row) $keys[]=hash('sha256',$this->key($row));
            $map=[];
            foreach (array_chunk(array_unique($keys),200) as $chunk) {
                foreach ($this->read($this->db->where_in('group_key',$chunk)->get('tr_jurnal_postings'))->result_array() as $record) $map[$record['group_key']]=$record;
            }
            foreach ($rows as &$row) {
                $record=$map[hash('sha256',$this->key($row))] ?? null;
                if (!$record) continue;
                $row['posted_at']=$record['posted_at']; $row['posted_by']=$record['posted_by_name']; $row['posted_by_id']=$record['posted_by'];
                $entries=json_decode($record['accounting_numbers'],true); $numbers=[];
                if (is_array($entries)) foreach ($entries as $entry) {
                    if (!is_array($entry) || !is_string($entry['number'] ?? null) || !is_string($entry['company'] ?? null)) continue;
                    $company=$entry['company'];
                    foreach ($row['companies'] as $side) if ($side['id']===$company) $company=$side['name'];
                    $numbers[]=count($entries)>1 ? ($company!==''?$company.': ':'').$entry['number'] : $entry['number'];
                }
                $row['source_numbers']=$numbers?implode('; ',array_unique($numbers)):null;
            }
            unset($row);
        }
        $this->resolveGlMetadata($rows);
    }

    public function company_sections($rows)
    {
        $sections=[];
        foreach ($rows as $row) {
            $debit=$this->cents($row['debit']); $credit=$this->cents($row['kredit']);
            if ($debit===0 && $credit===0) continue;
            $id=trim((string)($row['id_company'] ?? ''));
            $name=trim((string)($row['nm_company'] ?? ''));
            $key=$id!=='' ? 'id:'.$id : 'name:'.$name;
            if (!isset($sections[$key])) $sections[$key]=['id_company'=>$id,'nm_company'=>$name!==''?$name:($id!==''?'Company '.$id:'Company tidak tersedia'),
                'rows'=>[],'debit'=>0,'kredit'=>0,'valid_amounts'=>true];
            $sections[$key]['rows'][]=$row;
            $sections[$key]['debit']+=$debit; $sections[$key]['kredit']+=$credit;
            if ($debit<0 || $credit<0) $sections[$key]['valid_amounts']=false;
        }
        foreach ($sections as &$section) {
            $section['is_balance']=$section['valid_amounts'] && $section['debit']>0 && $section['debit']===$section['kredit'];
            $section['debit']=$this->fromCents($section['debit']); $section['kredit']=$this->fromCents($section['kredit']);
            unset($section['valid_amounts']);
        }
        unset($section);
        return array_values($sections);
    }

    public function export_rows($values, $posted = false)
    {
        $headers=$this->groups($values,$posted); $result=[];
        // Fetch bounded batches, preserving the original group identity and all COA lines.
        foreach (array_chunk($headers,200) as $chunk) {
            $ordinary=[];
            foreach($chunk as $header){
                if(!empty($header['debt_group']))foreach($this->debt_groups()->find($header['id'])['rows'] as $line)$result[]=['header'=>$header,'line'=>$line];
                else $ordinary[]=$header;
            }
            $chunk=$ordinary;if(!$chunk)continue;
            $numbers=array_unique(array_column($chunk,'no_transaksi'));
            $rows=$this->read($this->db->where_in('no_transaksi',$numbers)->order_by('id','asc')->get('tr_jurnal'))->result_array();
            $map=[]; foreach ($chunk as $header) $map[$this->key($header)]=$header;
            $lineDivs = [];
            foreach ($rows as &$r) {
                $h = $map[$this->key($r)] ?? null;
                if($h) $r=$this->periodicLineDivision($h,$r);
                if($h) $r=$this->expenseLineDivision($h,$r);
                if (empty($r['nm_divisi']) && !empty($h['nm_divisi'])) {
                    $r['nm_divisi'] = $h['nm_divisi'];
                }
                if (!empty($r['nm_divisi'])) $lineDivs[] = $r['nm_divisi'];
            }
            unset($r);
            if ($lineDivs) {
                $lineDivMap = $this->divisionMap($lineDivs);
                if ($lineDivMap) {
                    foreach ($rows as &$r) {
                        $c = trim((string)($r['nm_divisi'] ?? ''));
                        if (isset($lineDivMap[$c])) $r['nm_divisi'] = $lineDivMap[$c];
                    }
                    unset($r);
                }
            }
            foreach ($rows as $row) {
                $header=$map[$this->key($row)] ?? null;
                if (!$header || ($posted ? (string)$row['sts']!=='1' : !$this->pending($row))) continue;
                $result[]=['header'=>$header,'line'=>$row];
            }
        }
        return $result;
    }

    public function pending($row)
    {
        $source=$this->source($row['jenis_transaksi']); $status=$row['sts'];
        if (in_array($source,['INV','KWD','EXP'],true)) return in_array($status,[null,'','0',0],true);
        if ($source==='PCS') return in_array($status,['','0',0],true);
        return $source!==null && $status!==null && (string)$status!=='1';
    }

    public function key($row)
    {
        if(!empty($row['debt_group']))return 'debt:'.$row['group_id'];
        return json_encode([$row['no_transaksi'],$row['jenis_transaksi'],$this->source($row['jenis_transaksi'])==='EXP' ? $row['id_company'] : null]);
    }

    public function cents($value)
    {
        $value=trim((string)$value);
        if (!preg_match('/^(-?)(\d+)(?:\.(\d{1,2}))?$/D',$value,$m)) throw new InvalidArgumentException('Nominal jurnal tidak valid.');
        $digits=ltrim($m[2].str_pad($m[3] ?? '',2,'0'),'0') ?: '0';
        $limit=(string)PHP_INT_MAX;
        if (strlen($digits)>strlen($limit)||(strlen($digits)===strlen($limit)&&strcmp($digits,$limit)>0)) throw new InvalidArgumentException('Nominal jurnal melampaui batas perhitungan aman.');
        return ($m[1]==='-'?-1:1)*(int)$digits;
    }
    public function fromCents($value) {
        if (!is_int($value)||$value===PHP_INT_MIN) throw new InvalidArgumentException('Total jurnal melampaui batas perhitungan aman.');
        return ($value<0?'-':'').intdiv(abs($value),100).'.'.str_pad((string)(abs($value)%100),2,'0',STR_PAD_LEFT);
    }
    private function decimal($value) { return $this->fromCents($this->cents($value)); }
    private function scalar($value) { return is_scalar($value) ? mb_substr((string)$value,0,255,'UTF-8') : ''; }
    private function read($query) { if ($query===false) throw new RuntimeException('Gagal membaca data jurnal.'); return $query; }

    private function enrich(&$rows, $posted = false)
    {
        if (!$posted) {
            if ($this->paymentReference===null) $this->paymentReference=new Payment_reference((object)['db'=>$this->db], true);
        }
        $invoices=[]; $receipts=[]; $paymentKeys=[]; $pettyNumbers=[]; $expenseNumbers=[];
        foreach ($rows as $row) {
            $source=$this->source($row['jenis_transaksi']);
            if ($source==='INV') $invoices[]=$row['no_transaksi'];
            if ($source==='EXP') $expenseNumbers[$row['jenis_transaksi']][]=$row['no_transaksi'];
            if ($source==='PCS'&&$row['jenis_transaksi']==='Petty Cash') $pettyNumbers[]=$row['no_transaksi'];
            if ($source==='RCV') $receipts[]=$row['no_transaksi'];
            if ($source==='PAY'||($source==='PCS'&&$row['jenis_transaksi']!=='Petty Cash')) {
                $lookupKey=$this->paymentLookupKey($row,$posted);
                if ($lookupKey!==null) $paymentKeys[]=$lookupKey;
            }
        }
        $receiptMap=[];
        if ($receipts) foreach ($this->read($this->db->select('id_header, MIN(id_inv) AS id_inv')->where_in('id_header',array_unique($receipts))->group_by('id_header')->get('tr_penerimaan_piutang_detail'))->result_array() as $r) {
            $receiptMap[$r['id_header']]=$r['id_inv']; $invoices[]=$r['id_inv'];
        }
        $invoiceMap=$this->invoiceMap(array_unique($invoices));
        $pettyReports=$this->pettyReportMap(array_unique($pettyNumbers));
        $pettyDivisions=$this->pettyDivisionMap(array_unique($pettyNumbers));
        $expenseMap=$this->expenseMap($expenseNumbers);
        $paymentMap=$paymentKeys ? $this->paymentMap($paymentKeys) : [];
        $paymentApprovals=[];
        foreach(($paymentMap['id'] ?? []) as $bucket) foreach($bucket as $approval) $paymentApprovals[$approval['id']]=$approval;
        $paymentDivisions=$this->pettyPaymentDivisionMap($paymentApprovals);
        $periodicDivisions=$this->periodicDivisionMap($paymentApprovals);
        if (!$posted) {
            $keys=[];foreach($rows as $row)if($row['jenis_transaksi']==='Payment')$keys[]=$row['no_transaksi'];
            $known=[];foreach(($paymentMap['id'] ?? []) as $bucket)foreach($bucket as $p)$known[$p['id']]=$p;
            try { $this->paymentReference->prime($keys,$known); }
            catch (Throwable $error) { Payment_reference::log('Payment: batch relasi voucher belum dapat diperiksa.'); }
        }
        $paymentCompanions=$this->paymentCompanionMap($rows);
        foreach ($rows as &$row) {
            $row['source']=$this->source($row['jenis_transaksi']); $row['tab']=$this->tab($row['jenis_transaksi']);
            $row['display_no_transaksi']=$row['source']==='INV'?'':$row['no_transaksi'];
            $row['no_referensi']=$row['source']==='INV'?'':$row['no_transaksi']; $row['categories']=[$row['jenis_transaksi']];
            $row['staging_numbers']=$row['source_numbers'] ?? null; $row['source_numbers']=null;
            $row['posted_at']=!empty($row['db_tgl_posting']) ? $row['db_tgl_posting'] : (!empty($row['tgl_posting']) ? $row['tgl_posting'] : null);
            $row['posted_by']=null;
            $row['posted_by_id']=!empty($row['db_posted_by']) ? (string)$row['db_posted_by'] : (!empty($row['posted_by']) ? (string)$row['posted_by'] : null);
            $row['invoice_available']=false; $row['payment_available']=false;
            $row['companies']=[['id'=>(string)$row['id_company'],'name'=>(string)$row['nm_company']]];
            if (in_array($row['source'],['INV','RCV'],true)) {
                $invoice=$invoiceMap[$row['source']==='INV'?$row['no_transaksi']:($receiptMap[$row['no_transaksi']] ?? '')] ?? null;
                if ($invoice) {
                    $row['invoice_available']=$invoice['no_invoice']!==null && ($row['source']==='RCV'||$invoice['no_invoice']!=='');
                    $row['no_referensi']=$invoice['no_invoice']; $row['id_company']=$invoice['company_id']; $row['nm_company']=$invoice['company_name'] ?? '';
                    $row['companies']=[['id'=>(string)$row['id_company'],'name'=>$row['nm_company']]];
                    if (empty($row['nm_divisi']) && !empty($invoice['division_code'])) {
                        $row['nm_divisi'] = $invoice['division_code'];
                    }
                    $clean=function($v){$s=trim((string)$v);return ($s===''||$s==='-')?'':$s;};
                    $spk=$clean($invoice['id_spk_penawaran'] ?? '');
                    $penawaran=$clean($invoice['id_penawaran'] ?? '');
                    $po=$clean($invoice['no_po'] ?? '');
                    $row['extra_search']=implode(' ',array_filter([$invoice['nm_customer'],$invoice['nm_project'],$spk,$penawaran,$po],function($v){return $v!=='';}));
                    if ($row['source']==='INV') {
                        $row['invoice_number']=trim((string)$invoice['no_invoice']);
                        $row['display_no_transaksi']=$row['invoice_number'];
                        $isNonKons=(string)($invoice['non_kons'] ?? '')==='1'||strpos($penawaran,'NON-KONS')!==false;
                        $row['no_referensi']=$spk!==''?$spk:(($isNonKons&&$penawaran!=='')?$penawaran:$po);
                        $names=[trim((string)$invoice['nm_customer']),trim((string)$invoice['nm_project'])];
                        $row['keterangan']=implode(' - ',array_filter($names,function($name){return $name!=='';}));
                    } else {
                        $number=trim((string)$invoice['no_invoice']);
                        $customer=trim((string)$invoice['nm_customer']);
                        $row['keterangan']='Pelunasan '.($number!==''?$number:'—').' - '.($customer!==''?$customer:'—');
                    }
                }
            }
            if ($row['source']==='PAY'||($row['source']==='PCS'&&$row['jenis_transaksi']!=='Petty Cash')) {
                $voucherOnly=$posted && $row['source']==='PAY';
                $lookupKey=$this->paymentLookupKey($row,$posted);
                $matches=[];
                foreach ($lookupKey===null?[]:array_unique(array_map('trim',explode(',',$lookupKey))) as $key) {
                    foreach ($this->resolvePayments($paymentMap,$key,$row['jenis_transaksi'],$paymentCompanions[$row['no_transaksi']] ?? [],$voucherOnly) as $p) $matches[$p['id']]=$p;
                }
                if ($voucherOnly) {
                    $row['display_no_transaksi']='-';
                    $row['no_referensi']='-';
                    $row['categories']=['-'];
                }
                $docs=[]; $categories=[]; $vouchers=[];
                $categoryNames=['kasbon'=>'Kasbon','transport'=>'Transport','transportasi'=>'Transport','periodik'=>'Periodik','expense'=>'Expense','refill_pettycash'=>'Refill Petty Cash','petty_cash_hutang'=>'Payment Hutang Petty Cash'];
                foreach ($matches as $p) {
                    $docs[]=$p['no_doc'];
                    $voucher=trim((string)($p['id_payment'] ?? ''));
                    if ($voucher!=='') $vouchers[]=$voucher;
                    $category=$categoryNames[$p['tipe']] ?? ucwords(str_replace('_',' ',$p['tipe']));
                    if ($category!=='') $categories[]=$category;
                }
                if (empty($row['nm_divisi'])) {
                    $divisions=[];
                    foreach($matches as $p) foreach(($paymentDivisions[$p['id']] ?? []) as $name) $divisions[$name]=true;
                    $names=array_keys($divisions);sort($names,SORT_NATURAL|SORT_FLAG_CASE);
                    $row['nm_divisi']=implode(', ',$names);
                }
                $periodicNames=[];$periodicLines=[];
                foreach($matches as $p) foreach(($periodicDivisions[$p['no_doc']] ?? []) as $budget) {
                    $periodicNames[$budget['department_name']]=true;$periodicLines[]=$budget;
                }
                if($periodicNames) {
                    if(count(array_filter($matches,function($p){return $p['tipe']!=='periodik';})) && !empty($row['nm_divisi'])) $periodicNames[$row['nm_divisi']]=true;
                    $names=array_keys($periodicNames);sort($names,SORT_NATURAL|SORT_FLAG_CASE);
                    $row['nm_divisi']=implode(', ',$names);
                    $row['periodic_department_lines']=$periodicLines;
                }
                if ($docs) $row['no_referensi']=implode(', ',array_unique($docs));
                if ($vouchers) {
                    $uniqueVouchers=array_values(array_unique($vouchers));
                    $row['display_no_transaksi']=implode(', ',$uniqueVouchers);
                }
                if ($categories) $row['categories']=array_values(array_unique($categories));
                if ($row['jenis_transaksi']==='Payment') {
                    $headerCategories=$row['categories'];sort($headerCategories,SORT_NATURAL|SORT_FLAG_CASE);
                    $row['keterangan']='Pembayaran '.implode(', ',$headerCategories).' - '.$row['display_no_transaksi'];
                }
                $row['extra_search']=implode(' ',array_column($matches,'id'));
                $row['payment_available']=(bool)$matches;
                $row['petty_payment_eligible']=false;
                foreach ($matches as $p) if (in_array($p['tipe'],['refill_pettycash','petty_cash_hutang'],true)||stripos($p['no_doc'],'RPC')!==false) $row['petty_payment_eligible']=true;
                if (!$posted && $row['jenis_transaksi']==='Payment') {
                    try {
                        if ($this->paymentReference===null) $this->paymentReference=new Payment_reference((object)['db'=>$this->db], true);
                        $reference=$this->paymentReference->resolve($row['no_transaksi']);
                        $row['payment_voucher']=$reference['voucher'];
                        $row['display_no_transaksi']=$reference['voucher'];
                        $row['no_referensi']=implode(', ',array_unique(array_column($reference['approvals'],'no_doc')));
                        $categories=[];
                        foreach($reference['approvals'] as $p)$categories[]=$categoryNames[$p['tipe']] ?? ucwords(str_replace('_',' ',$p['tipe']));
                        $row['categories']=array_values(array_unique(array_filter($categories,'strlen')));
                        $headerCategories=$row['categories'];sort($headerCategories,SORT_NATURAL|SORT_FLAG_CASE);
                        $row['extra_search']=implode(' ',array_column($reference['approvals'],'id'));
                        $row['payment_available']=true;
                        $row['keterangan']='Pembayaran '.implode(', ',$headerCategories).' - '.$reference['voucher'];
                    } catch (Throwable $error) {
                        $row['display_no_transaksi']='-';
                        $row['payment_reference_error']=$error instanceof DomainException?$error->getMessage():'Relasi nomor pembayaran belum dapat diverifikasi.';
                    }
                }
            }
            if ($row['source']==='EXP') {
                $expense=$expenseMap[$row['jenis_transaksi']][$row['no_transaksi']];
                $row['no_referensi']=$expense['reference']; $row['keterangan']=$expense['description'];
                if (empty($row['nm_divisi']) && !empty($expense['departement'])) {
                    $row['nm_divisi'] = $expense['departement'];
                }
                $rawDivision=trim((string)($row['nm_divisi'] ?? ''));
                if(isset($expense['division_name']) && ($rawDivision==='' || ctype_digit($rawDivision) || preg_match('/^(DEP|DIV)[A-Za-z0-9_-]+$/i',$rawDivision))) {
                    $row['nm_divisi']=$expense['division_name'];
                    $row['expense_division_name']=$expense['division_name'];
                }
                if (!$posted) {
                    try { Expense_company::storedCompany([$row]); }
                    catch (DomainException $error) { $row['can_post']=false; $row['post_block_reason']=$error->getMessage(); }
                }
            }
            if ($row['source']==='PCS'&&$row['jenis_transaksi']==='Petty Cash') {
                $row['no_referensi']=implode(', ',$pettyReports[$row['no_transaksi']] ?? []);
                if (empty($row['nm_divisi']) && !empty($pettyDivisions[$row['no_transaksi']])) {
                    $row['nm_divisi'] = $pettyDivisions[$row['no_transaksi']];
                }
            }
            $row['category']=implode(', ',$row['categories']);
        }
        unset($row);
        // Keep all company sides in filters while preserving one posting boundary.
        $numbers=[]; foreach ($rows as $row) if ($row['source']==='PCS') $numbers[]=$row['no_transaksi'];
        if ($numbers) {
            $sides=$this->read($this->db->select('no_transaksi,jenis_transaksi,id_company,nm_company')->where_in('no_transaksi',array_unique($numbers))->group_by(['no_transaksi','jenis_transaksi','id_company','nm_company'])->get('tr_jurnal'))->result_array();
            foreach ($rows as &$row) if ($row['source']==='PCS') {
                $row['companies']=[];
                foreach ($sides as $side) if ($side['no_transaksi']===$row['no_transaksi']&&$side['jenis_transaksi']===$row['jenis_transaksi']) $row['companies'][]=['id'=>(string)$side['id_company'],'name'=>$side['nm_company']];
                $row['nm_company']=implode(' / ',array_unique(array_column($row['companies'],'name')));
            }
            unset($row);
        }
        $rawDivs = [];
        foreach ($rows as $r) {
            if (!empty($r['nm_divisi'])) $rawDivs[] = $r['nm_divisi'];
        }
        if ($rawDivs) {
            $divMap = $this->divisionMap($rawDivs);
            if ($divMap) {
                foreach ($rows as &$r) {
                    $c = trim((string)($r['nm_divisi'] ?? ''));
                    if (isset($divMap[$c])) $r['nm_divisi'] = $divMap[$c];
                }
                unset($r);
            }
        }
        $this->postingMetadata($rows);
    }

    private function expenseMap($numbers)
    {
        $map=[]; $headers=[]; $links=[]; $totals=[];
        $regular=array_unique(array_merge($numbers['Expense Report'] ?? [],$numbers['Pengembalian Kasbon'] ?? []));
        foreach (array_chunk($regular,200) as $chunk) {
            foreach ($this->read($this->db->where_in('no_doc',$chunk)->get('tr_expense'))->result_array() as $expense) {
                $headers[$expense['no_doc']]=$expense;
                if (!empty($expense['id_kasbon'])) $links[$expense['no_doc']][]=$expense['id_kasbon'];
            }
            foreach ($this->read($this->db->where_in('no_doc',$chunk)->get('tr_expense_detail'))->result_array() as $line) {
                foreach (['id_kasbon','kasbon_pr_non_po_pett'] as $field) if (!empty($line[$field])) $links[$line['no_doc']][]=$line[$field];
                if (!isset($totals[$line['no_doc']])) $totals[$line['no_doc']]=['kasbon'=>0,'expense'=>0];
                foreach (['kasbon','expense'] as $field) $totals[$line['no_doc']][$field]+=$this->cents($line[$field] ?? '0');
            }
            foreach ($this->read($this->db->select('no_doc,id_expense_pett_pr_non_po')->where_in('id_expense_pett_pr_non_po',$chunk)->get('tr_kasbon'))->result_array() as $kasbon) {
                $links[$kasbon['id_expense_pett_pr_non_po']][]=$kasbon['no_doc'];
            }
        }
        $keys=[]; foreach ($links as $values) $keys=array_merge($keys,$values);
        $kasbons=[];
        foreach (array_chunk(array_unique($keys),200) as $chunk) {
            foreach ($this->read($this->db->select('no_doc,id_pr,tipe_pr,nama,created_by,departement')->where_in('no_doc',$chunk)->get('tr_kasbon'))->result_array() as $kasbon) $kasbons[$kasbon['no_doc']]=$kasbon;
        }
        $requesters=$this->expenseRequesters($kasbons);
        $divisionNames=$this->expenseDivisionNames($headers,$kasbons,$links);
        foreach ($regular as $number) {
            $refs=[]; $names=[];
            foreach (array_unique($links[$number] ?? []) as $key) if (isset($kasbons[$key])) {
                $refs[]=$key;
                foreach ($requesters[$key] ?? [] as $name) $names[$name]=$name;
            }
            sort($refs,SORT_NATURAL); $names=array_values($names); sort($names,SORT_NATURAL);
            $expense=$headers[$number] ?? []; $kind='';
            if (is_numeric($expense['lebih_bayar'] ?? null) && (float)$expense['lebih_bayar']>0) $kind='Pengembalian';
            elseif (is_numeric($expense['kurang_bayar'] ?? null) && (float)$expense['kurang_bayar']>0) $kind='Reimburse';
            elseif (isset($totals[$number])) {
                $comparison=$totals[$number]['kasbon'] <=> $totals[$number]['expense'];
                $kind=$comparison>0?'Pengembalian':($comparison<0?'Reimburse':'');
            }
            $dept = trim((string)($expense['departement'] ?? ''));
            if ($dept === '') {
                foreach ($refs as $ref) {
                    if (!empty($kasbons[$ref]['departement'])) {
                        $dept = trim((string)$kasbons[$ref]['departement']);
                        break;
                    }
                }
            }
            $map['Expense Report'][$number]=['reference'=>$refs?implode(', ',$refs):'-',
                'description'=>'Realisasi Kasbon'.($kind!==''?' '.$kind:'').($names?' - '.implode(', ',$names):''),
                'departement'=>$dept,'division_name'=>$divisionNames[$number] ?? null];
            if (in_array($number,$numbers['Pengembalian Kasbon'] ?? [],true)) {
                $map['Pengembalian Kasbon'][$number]=['reference'=>$refs?implode(', ',$refs):'-',
                    'description'=>'Pengembalian Kasbon'.($names?' - '.implode(', ',$names):''),
                    'departement'=>$dept,'division_name'=>$divisionNames[$number] ?? null];
            }
        }
        foreach (array_chunk(array_unique($numbers['Expense Report Consultant'] ?? []),200) as $chunk) {
            $refs=[];
            foreach ($this->read($this->db->select('e.id,k.id AS kasbon')->from(DBCNL.'.kons_tr_expense_report_project_header e')
                ->join(DBCNL.'.kons_tr_kasbon_project_header k','k.id=e.id_header')->where_in('e.id',$chunk)->get())->result_array() as $row) {
                $reference=trim((string)$row['kasbon']); if ($reference!=='') $refs[$row['id']][$reference]=$reference;
            }
            foreach ($chunk as $number) {
                $values=array_values($refs[$number] ?? []); sort($values,SORT_NATURAL);
                $map['Expense Report Consultant'][$number]=['reference'=>$values?implode(', ',$values):'-','description'=>'Expense Report Consultant - '.$number];
            }
        }
        return $map;
    }

    private function expenseLineDivision(array $header,array $line)
    {
        $raw=trim((string)($line['nm_divisi'] ?? ''));
        if(isset($header['expense_division_name']) && ($raw==='' || ctype_digit($raw) || preg_match('/^(DEP|DIV)[A-Za-z0-9_-]+$/i',$raw))) {
            $line['nm_divisi']=$header['expense_division_name'];
        }
        return $line;
    }

    private function expenseDivisionNames(array $headers,array $kasbons,array $links)
    {
        $raw=[];$candidates=[];$codes=[];$legacy=[];
        foreach($headers as $number=>$expense) {
            $dept=trim((string)($expense['departement'] ?? ''));
            $names=[];$name=trim((string)($expense['nama'] ?? ''));if($name!=='')$names[]=$name;
            foreach(($links[$number] ?? []) as $ref) {
                $kasbon=$kasbons[$ref] ?? [];
                if($dept==='')$dept=trim((string)($kasbon['departement'] ?? ''));
                $name=trim((string)($kasbon['nama'] ?? ''));if($name!=='')$names[]=$name;
            }
            if($dept==='')continue;
            $raw[$number]=$dept;$codes[]=$dept;
            if(ctype_digit($dept)) {$legacy[]=$dept;$candidates[$number]=array_unique($names);}
        }
        $departments=$this->invoiceDepartmentMap($codes);$legacyNames=[];
        if($legacy && $this->db->table_exists('ms_department'))foreach(array_chunk(array_unique($legacy),200) as $chunk) {
            foreach($this->read($this->db->select('id,nama')->where_in('id',$chunk)->get('ms_department'))->result_array() as $r) {
                if(trim((string)$r['nama'])!=='')$legacyNames[(string)$r['id']]=trim($r['nama']);
            }
        }
        $names=[];foreach($candidates as $values)$names=array_merge($names,$values);
        $users=[];$userCodes=[];
        foreach(array_chunk(array_unique($names),200) as $chunk) {
            $rows=$this->read($this->db->select('username,nm_lengkap,department_id')->from('users')->group_start()
                ->where_in('username',$chunk)->or_where_in('nm_lengkap',$chunk)->group_end()->get())->result_array();
            foreach($rows as $r)if(trim((string)$r['department_id'])!=='') {
                foreach(['username','nm_lengkap'] as $field)$users[trim((string)$r[$field])][$r['department_id']]=true;
                $userCodes[]=$r['department_id'];
            }
        }
        $userDepartments=$this->invoiceDepartmentMap($userCodes);$map=[];
        foreach($raw as $number=>$dept) {
            if(isset($departments[$dept])){$map[$number]=$departments[$dept];continue;}
            if(isset($legacyNames[$dept])){$map[$number]=$legacyNames[$dept];continue;}
            if(!ctype_digit($dept))continue;
            $resolved=[];
            foreach(($candidates[$number] ?? []) as $name) {
                $matches=array_keys($users[$name] ?? []);
                if(count($matches)===1 && !empty($userDepartments[$matches[0]]))$resolved[$userDepartments[$matches[0]]]=true;
            }
            $values=array_keys($resolved);sort($values,SORT_NATURAL|SORT_FLAG_CASE);
            $map[$number]=$values?implode(', ',$values):'-';
        }
        return $map;
    }

    private function expenseRequesters($kasbons)
    {
        $tables=['pr departemen'=>'rutin_non_planning_header','pr stok'=>'material_planning_base_on_produksi','pr asset'=>'tran_pr_header'];
        $numbers=[];
        foreach ($kasbons as $kasbon) {
            $type=strtolower(trim((string)$kasbon['tipe_pr']));
            if (isset($tables[$type]) && !empty($kasbon['id_pr'])) $numbers[$type][]=$kasbon['id_pr'];
        }
        $users=[];
        foreach ($numbers as $type=>$keys) foreach (array_chunk(array_unique($keys),200) as $chunk) {
            foreach ($this->read($this->db->select('p.no_pr,u.nm_lengkap')->from($tables[$type].' p')
                ->join('users u','u.id_user=p.created_by')->where_in('p.no_pr',$chunk)->get())->result_array() as $row) {
                $name=trim((string)$row['nm_lengkap']); if ($name!=='') $users[$type][$row['no_pr']][$name]=$name;
            }
        }
        $map=[];
        $candidates=[];
        foreach ($kasbons as $kasbon) {
            $type=strtolower(trim((string)($kasbon['tipe_pr'] ?? '')));
            $hasPr=!empty($kasbon['id_pr']) && !empty($users[$type][$kasbon['id_pr']]);
            if (!$hasPr) {
                $directName=trim((string)($kasbon['nama'] ?? ''));
                if ($directName!=='') $candidates[]=$directName;
                $creator=trim((string)($kasbon['created_by'] ?? ''));
                if ($creator!=='') $candidates[]=$creator;
            }
        }
        $userNames=[];
        if ($candidates) {
            foreach (array_chunk(array_unique($candidates),200) as $chunk) {
                foreach ($this->read($this->db->select('username,nm_lengkap')->from('users')->where_in('username',$chunk)->or_where_in('nm_lengkap',$chunk)->get())->result_array() as $row) {
                    $full=trim((string)$row['nm_lengkap']); $u=trim((string)($row['username'] ?? ''));
                    if ($full!=='') {
                        if ($u!=='') $userNames[$u]=$full;
                        $userNames[$full]=$full;
                    }
                }
            }
        }
        foreach ($kasbons as $number=>$kasbon) {
            $type=strtolower(trim((string)($kasbon['tipe_pr'] ?? '')));
            $resolved=array_values($users[$type][$kasbon['id_pr'] ?? ''] ?? []);
            if (!$resolved) {
                $direct=trim((string)($kasbon['nama'] ?? ''));
                $creator=trim((string)($kasbon['created_by'] ?? ''));
                $matched=$userNames[$direct] ?? ($direct!==''?$direct:($userNames[$creator] ?? ($creator!==''?$creator:'')));
                if ($matched!=='') $resolved=[$matched];
            }
            $map[$number]=$resolved;
        }
        return $map;
    }

    private function periodicDivisionMap(array $approvals)
    {
        $docs=[];
        foreach($approvals as $p) if($p['tipe']==='periodik') $docs[]=$p['no_doc'];
        if(!$docs || !$this->db->table_exists('tr_pengajuan_rutin_detail') || !$this->db->table_exists('ms_budget_rutin'))return [];
        $rows=[];$codes=[];
        foreach(array_chunk(array_unique($docs),200) as $chunk) {
            $found=$this->read($this->db->select('d.no_doc,d.coa,d.nama,d.nilai,b.departement')->from('tr_pengajuan_rutin_detail d')
                ->join('ms_budget_rutin b','b.id=d.id_budget')->where_in('d.no_doc',$chunk)->get())->result_array();
            foreach($found as $r) {$rows[]=$r;$codes[]=$r['departement'];}
        }
        $departments=$this->invoiceDepartmentMap($codes);$map=[];
        foreach($rows as $r) if(!empty($departments[$r['departement']])) {
            $r['department_name']=$departments[$r['departement']];$map[$r['no_doc']][]=$r;
        }
        return $map;
    }

    private function periodicLineDivision(array $header,array $line)
    {
        $budgets=$header['periodic_department_lines'] ?? [];
        if(!$budgets)return $line;
        $names=[];
        foreach($budgets as $budget) {
            $name=trim((string)$budget['nama']);$description=trim((string)$line['keterangan']);
            if((string)$budget['coa']===(string)$line['coa'] && $this->cents($budget['nilai'])===$this->cents($line['debit'])
                && $name!=='' && ($description===$name || strpos($description,$name.' - ')===0)) $names[$budget['department_name']]=true;
        }
        if($names) {
            $names=array_keys($names);sort($names,SORT_NATURAL|SORT_FLAG_CASE);$line['nm_divisi']=implode(', ',$names);
        } elseif($this->cents($line['debit'])===0 && $this->cents($line['kredit'])>0) {
            $line['nm_divisi']=$header['nm_divisi'];
        }
        return $line;
    }

    public function pettyPaymentDivisionMap(array $approvals)
    {
        $docs=[];
        foreach($approvals as $p) if(in_array($p['tipe'],['refill_pettycash','petty_cash_hutang'],true)) $docs[]=$p['no_doc'];
        if(!$docs) return [];
        foreach(['tr_pelaporan_petty_cash','tr_pelaporan_petty_cash_detail','tr_expense_petty_cash'] as $table) if(!$this->db->table_exists($table))return [];
        $sourceReports=[];
        if($this->db->table_exists('tr_petty_cash_vuca_sustain') && $this->db->field_exists('pelaporan_id','tr_petty_cash_vuca_sustain')) {
            foreach(array_chunk(array_unique($docs),200) as $chunk) {
                $links=$this->read($this->db->select('h.no_payment_hutang,p.no_pelaporan')->from('tr_petty_cash_vuca_sustain h')
                    ->join('tr_pelaporan_petty_cash p','p.id=h.pelaporan_id')->where_in('h.no_payment_hutang',$chunk)->get())->result_array();
                foreach($links as $link){$sourceReports[$link['no_payment_hutang']][]=$link['no_pelaporan'];$docs[]=$link['no_pelaporan'];}
            }
        }
        $requesters=[];$names=[];
        foreach(array_chunk(array_unique($docs),200) as $chunk) {
            $rows=$this->read($this->db->select('p.no_pelaporan,e.request_by')->from('tr_pelaporan_petty_cash p')
                ->join('tr_pelaporan_petty_cash_detail d','d.pelaporan_id=p.id')
                ->join('tr_expense_petty_cash e','e.id=d.pencatatan_id')->where_in('p.no_pelaporan',$chunk)->get())->result_array();
            foreach($rows as $row) {
                $name=trim((string)$row['request_by']);
                if($name!=='') {$requesters[$row['no_pelaporan']][]=$name;$names[]=$name;}
            }
        }
        $users=[];$codes=[];
        foreach(array_chunk(array_unique($names),200) as $chunk) {
            $rows=$this->read($this->db->select('username,nm_lengkap,department_id')->from('users')
                ->group_start()->where_in('username',$chunk)->or_where_in('nm_lengkap',$chunk)->group_end()->get())->result_array();
            foreach($rows as $row) {
                $code=trim((string)$row['department_id']);
                if($code==='')continue;
                foreach(['username','nm_lengkap'] as $field) $users[trim((string)$row[$field])][$code]=true;
                $codes[]=$code;
            }
        }
        $departments=$this->invoiceDepartmentMap($codes);
        $map=[];
        foreach($approvals as $p) if(in_array($p['tipe'],['refill_pettycash','petty_cash_hutang'],true)) {
            $reportNames=[];
            foreach(($sourceReports[$p['no_doc']] ?? [$p['no_doc']]) as $report) $reportNames=array_merge($reportNames,$requesters[$report] ?? []);
            foreach(array_unique($reportNames) as $name) {
                $matches=array_keys($users[$name] ?? []);
                // Ambiguous requester identities must not assign an arbitrary department.
                if(count($matches)===1 && !empty($departments[$matches[0]])) $map[$p['id']][]=$departments[$matches[0]];
            }
            if(isset($map[$p['id']]))$map[$p['id']]=array_values(array_unique($map[$p['id']]));
        }
        return $map;
    }

    private function pettyDivisionMap($numbers)
    {
        if (!$numbers) return [];
        $map = [];
        $requesters = [];
        foreach (array_chunk($numbers, 200) as $chunk) {
            $rows = $this->read($this->db->select('no_pencatatan, request_by')
                ->where_in('no_pencatatan', $chunk)->get('tr_expense_petty_cash'))->result_array();
            foreach ($rows as $r) {
                $req = trim((string)($r['request_by'] ?? ''));
                if ($req !== '') $requesters[$r['no_pencatatan']] = $req;
            }
        }
        if (!$requesters) return [];

        $hrisDb = defined('DBHRIS') ? DBHRIS : 'hr_sentral';
        $empMap = [];
        try {
            $debug = $this->db->db_debug;
            $this->db->db_debug = false;
            $names = array_unique(array_values($requesters));
            $likeClauses = [];
            foreach ($names as $name) {
                $first = explode(' ', $name)[0];
                if (strlen($first) >= 3) {
                    $escaped = $this->db->escape_like_str($first);
                    $likeClauses[] = "name LIKE '%{$escaped}%'";
                }
            }
            if ($likeClauses) {
                $sql = "SELECT name, division_id, department_id FROM {$hrisDb}.employees WHERE " . implode(' OR ', $likeClauses);
                $query = $this->db->query($sql);
                if ($query && $query->num_rows() > 0) {
                    $emps = $query->result_array();
                    foreach ($names as $name) {
                        $first = strtolower(explode(' ', $name)[0]);
                        foreach ($emps as $e) {
                            if (stripos(strtolower($e['name']), $first) !== false) {
                                $div = !empty($e['division_id']) ? $e['division_id'] : $e['department_id'];
                                if (!empty($div)) {
                                    $empMap[$name] = $div;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            $this->db->db_debug = $debug;
        } catch (Throwable $e) {}

        foreach ($requesters as $no => $name) {
            if (isset($empMap[$name])) $map[$no] = $empMap[$name];
        }
        return $map;
    }

    private function pettyReportMap($numbers)
    {
        $map=[];
        foreach (array_chunk($numbers,200) as $chunk) {
            $reports=$this->read($this->db->select('e.no_pencatatan,p.no_pelaporan')
                ->from('tr_expense_petty_cash e')
                ->join('tr_pelaporan_petty_cash_detail d','d.pencatatan_id=e.id')
                ->join('tr_pelaporan_petty_cash p','p.id=d.pelaporan_id')
                ->where_in('e.no_pencatatan',$chunk)->where_in('p.status',['draft','waiting','approved'])->get())->result_array();
            foreach ($reports as $report) {
                $number=trim((string)$report['no_pelaporan']);
                if ($number!=='') $map[$report['no_pencatatan']][$number]=$number;
            }
        }
        foreach ($map as &$reports) { $reports=array_values($reports); sort($reports,SORT_NATURAL); }
        unset($reports);
        return $map;
    }

    private function invoiceMap($ids)
    {
        if (!$ids) return [];
        $company="COALESCE(NULLIF(c.company,''),NULLIF(s.id_company,''),n.id_company)";
        $division="CASE WHEN n.id_penawaran IS NOT NULL THEN COALESCE(NULLIF(TRIM(n.id_divisi),''),NULLIF(TRIM(n.nm_divisi),'')) ELSE COALESCE(NULLIF(TRIM(s.nm_divisi),''),NULLIF(TRIM(c.nm_divisi),''),NULLIF(TRIM(s.id_divisi),''),NULLIF(TRIM(c.id_divisi),'')) END";
        $rows=$this->read($this->db->select("b.id,b.no_invoice,b.nm_customer,COALESCE(NULLIF(TRIM(b.nm_project),''),s.nm_project) AS nm_project,b.id_spk_penawaran,b.id_penawaran,b.no_po,b.non_kons, ".$company.' AS company_id,co.nm_company AS company_name, '.$division.' AS division_code',false)
            ->from('tr_invoicing b')->join(DBCNL.'.kons_tr_penawaran c','c.id_quotation=b.id_penawaran','left')
            ->join(DBCNL.'.kons_tr_spk_penawaran s','s.id_spk_penawaran=b.id_spk_penawaran','left')
            ->join(DBCNL.'.kons_tr_penawaran_non_konsultasi n','n.id_penawaran=b.id_penawaran','left')
            ->join(DBCNL.'.kons_tr_company co','co.id='.$company,'left',false)
            ->where_in('b.id',$ids)->get())->result_array();
        $departmentCodes=[];
        foreach ($rows as $row) {
            if ((string)$row['non_kons']==='1' || strpos((string)$row['id_penawaran'],'NON-KONS')!==false) {
                $departmentCodes[]=$row['division_code'];
            }
        }
        // Non-consulting offers select HRIS departments, not their parent divisions.
        $departments=$this->invoiceDepartmentMap($departmentCodes);
        $map=[];
        foreach ($rows as $row) {
            if ((string)$row['non_kons']==='1' || strpos((string)$row['id_penawaran'],'NON-KONS')!==false) {
                if (preg_match('/^(DEP|DIV)[A-Za-z0-9_-]+$/i',(string)$row['division_code'])) {
                    $row['division_code']=$departments[$row['division_code']] ?? '';
                }
            }
            $map[$row['id']]=$row;
        }
        return $map;
    }

    private function paymentLookupKey($row,$posted)
    {
        $native=$row['no_transaksi'];
        if (!$posted || $this->source($row['jenis_transaksi'])!=='PAY') return $native;
        if ($this->reportPaymentHistory===null) $this->reportPaymentHistory=require dirname(__DIR__).'/config/report_payment_history.php';
        $history=$this->reportPaymentHistory[$native] ?? null;
        if ($history===null) return $native;
        foreach (['jenis_transaksi','id_company','tgl_jurnal'] as $field) {
            if ((string)$row[$field]!==$history[$field]) return null;
        }
        foreach (['debit','kredit'] as $field) {
            if ($this->cents($row[$field])!==$this->cents($history[$field])) return null;
        }
        return $history['voucher'];
    }

    private function paymentMap($numbers)
    {
        $keys=[];
        foreach ($numbers as $number) $keys=array_merge($keys,[$number],array_map('trim',explode(',',$number)));
        $missing=array_values(array_diff(array_unique($keys),$this->paymentKeys));
        foreach (array_chunk($missing,200) as $chunk) {
            $rows=$this->read($this->db->select('id,id_payment,no_doc,tipe')->group_start()->where_in('id',$chunk)
                ->or_where_in('id_payment',$chunk)->or_where_in('no_doc',$chunk)->group_end()->get('payment_approve'))->result_array();
            foreach ($rows as $row) foreach (['id','id_payment','no_doc'] as $field) {
                $key=(string)$row[$field];
                if ($key!=='') $this->payments[$field][$key][$row['id']]=$row;
            }
        }
        $this->paymentKeys=array_merge($this->paymentKeys,$missing);
        return $this->payments;
    }

    private function paymentCompanionMap($rows)
    {
        $keys=[];
        foreach ($rows as $row) if ($row['jenis_transaksi']==='Payment') $keys[]=$row['no_transaksi'];
        $missing=array_values(array_diff(array_unique($keys),$this->paymentCompanionKeys));
        $types=['Refill Pettycash'=>'refill_pettycash','Payment Hutang Petty Cash'=>'petty_cash_hutang'];
        foreach (array_chunk($missing,200) as $chunk) {
            $siblings=$this->read($this->db->select('no_transaksi,jenis_transaksi')->where_in('no_transaksi',$chunk)
                ->where_in('jenis_transaksi',array_keys($types))->group_by(['no_transaksi','jenis_transaksi'])->get('tr_jurnal'))->result_array();
            foreach ($siblings as $sibling) $this->paymentCompanions[$sibling['no_transaksi']][]=$types[$sibling['jenis_transaksi']];
        }
        $this->paymentCompanionKeys=array_merge($this->paymentCompanionKeys,$missing);
        return $this->paymentCompanions;
    }

    private function resolvePayments($map,$key,$type,$excludedTypes=[],$voucherOnly=false)
    {
        $requiredType=['Payment Hutang Petty Cash'=>'petty_cash_hutang','Refill Pettycash'=>'refill_pettycash'][$type] ?? null;
        foreach ($voucherOnly?['id_payment']:['id','id_payment','no_doc'] as $field) {
            $matches=$map[$field][$key] ?? [];
            if ($requiredType!==null) $matches=array_filter($matches,function($payment)use($requiredType){return $payment['tipe']===$requiredType;});
            if ($type==='Payment' && $excludedTypes) $matches=array_filter($matches,function($payment)use($excludedTypes){return !in_array($payment['tipe'],$excludedTypes,true);});
            if ($matches) return $matches;
        }
        return [];
    }

    protected function invoiceDepartmentDatabase()
    {
        if ($this->invoiceHrisDb===null) {
            $ci =& get_instance();
            $this->invoiceHrisDb=$ci->load->database('hris',true);
        }
        return $this->invoiceHrisDb;
    }

    private function invoiceDepartmentMap(array $codes)
    {
        $codes=array_values(array_unique(array_filter($codes,function($code){
            return preg_match('/^(DEP|DIV)[A-Za-z0-9_-]+$/i',(string)$code);
        })));
        if (!$codes) return [];
        try {
            // HRIS is hosted separately; querying its schema on the finance connection returns different data.
            $hris=$this->invoiceDepartmentDatabase();
            $query=$hris->select('id,name')->where_in('id',$codes)->get('departments');
            if (!$query) return [];
            $map=[];
            foreach($query->result_array() as $row) $map[$row['id']]=trim((string)$row['name']);
            return $map;
        } catch (Throwable $error) {
            log_message('error','Jurnal All: HRIS department lookup unavailable.');
            return [];
        }
    }

    public function divisionMap(array $rawCodes)
    {
        $codes = [];
        foreach ($rawCodes as $c) {
            $c = trim((string)$c);
            if ($c !== '' && preg_match('/^(DEP|DIV)[A-Za-z0-9_-]+$/i', $c)) {
                $codes[$c] = true;
            }
        }
        if (!$codes) return [];

        $map = [];
        $keys = array_keys($codes);
        $hrisDb = defined('DBHRIS') ? DBHRIS : 'hr_sentral';

        // 1. Resolve via HRIS database (hr_sentral)
        try {
            $debug = $this->db->db_debug;
            $this->db->db_debug = false;

            $depKeys = array_values(array_filter($keys, function ($k) { return stripos($k, 'DEP') === 0; }));
            if ($depKeys) {
                $escaped = array_map(function ($k) { return "'" . $this->db->escape_str($k) . "'"; }, $depKeys);
                $sql = "SELECT dep.id AS code, COALESCE(d.name, dep.name) AS division_name "
                     . "FROM {$hrisDb}.departments dep "
                     . "LEFT JOIN {$hrisDb}.divisions d ON d.id = dep.division_id "
                     . "WHERE dep.id IN (" . implode(',', $escaped) . ")";
                $query = $this->db->query($sql);
                if ($query && $query->num_rows() > 0) {
                    foreach ($query->result_array() as $r) {
                        if (!empty($r['division_name'])) {
                            $name = trim((string)$r['division_name']);
                            $map[$r['code']] = $name;
                            $map[strtoupper($r['code'])] = $name;
                            $map[strtolower($r['code'])] = $name;
                        }
                    }
                }
            }

            $divKeys = array_values(array_filter($keys, function ($k) { return stripos($k, 'DIV') === 0; }));
            if ($divKeys) {
                $escaped = array_map(function ($k) { return "'" . $this->db->escape_str($k) . "'"; }, $divKeys);
                $sql = "SELECT d.id AS code, d.name AS division_name "
                     . "FROM {$hrisDb}.divisions d "
                     . "WHERE d.id IN (" . implode(',', $escaped) . ")";
                $query = $this->db->query($sql);
                if ($query && $query->num_rows() > 0) {
                    foreach ($query->result_array() as $r) {
                        if (!empty($r['division_name'])) {
                            $name = trim((string)$r['division_name']);
                            $map[$r['code']] = $name;
                            $map[strtoupper($r['code'])] = $name;
                            $map[strtolower($r['code'])] = $name;
                        }
                    }
                }
            }
            $this->db->db_debug = $debug;
        } catch (Throwable $e) {
            // HRIS database inaccessible or mock environment
        }

        // 2. Fallback to local tables if any codes remain unresolved
        $remaining = array_values(array_diff($keys, array_keys($map)));
        if ($remaining) {
            try {
                $debug = $this->db->db_debug;
                $this->db->db_debug = false;
                $query = $this->db->select('id AS code, name AS division_name')
                                  ->where_in('id', $remaining)
                                  ->get('hris_divisions');
                if ($query && $query->num_rows() > 0) {
                    foreach ($query->result_array() as $r) {
                        if (!empty($r['division_name'])) {
                            $name = trim((string)$r['division_name']);
                            $map[$r['code']] = $name;
                            $map[strtoupper($r['code'])] = $name;
                            $map[strtolower($r['code'])] = $name;
                        }
                    }
                }
                $this->db->db_debug = $debug;
            } catch (Throwable $e) {}
        }

        return $map;
    }

    private function eligible($row)
    {
        if ($row['source']==='INV') return $row['invoice_available']&&$row['nm_company']!==''&&(int)$row['active_lines']>0;
        if ($row['source']==='RCV') return $row['invoice_available']&&(int)$row['receipt_lines']>0;
        if ($row['source']==='PAY') return trim((string)$row['nm_company'])!=='';
        if ($row['source']==='PCS'&&$row['jenis_transaksi']!=='Petty Cash') return $row['petty_payment_eligible'];
        return true;
    }

    private function companyMatches($row,$value)
    {
        foreach ($row['companies'] as $company) if ($company['id']===$value) return true;
        return false;
    }

    private function search($headers,$query,$posted)
    {
        $words=preg_split('/\s+/u',mb_strtolower(trim($query),'UTF-8'),-1,PREG_SPLIT_NO_EMPTY);
        if (!$words||!$headers) return $headers;
        $numbers=[];$matches=[];
        foreach($headers as $header){
            if(!empty($header['debt_group']))foreach($this->debt_groups()->find($header['id'])['rows'] as $line)$matches[$this->key($header)][]=mb_strtolower(implode(' ',[$line['coa'],$line['nm_coa'],$line['keterangan']]),'UTF-8');
            else $numbers[]=$header['no_transaksi'];
        }
        $numbers=array_unique($numbers);
        foreach (array_chunk($numbers,200) as $chunk) {
            $this->db->select('no_transaksi,jenis_transaksi,id_company,coa,nm_coa,keterangan')->where_in('no_transaksi',$chunk)->group_start();
            foreach ($words as $word) foreach (['coa','nm_coa','keterangan'] as $field) $this->db->or_like($field,$word);
            $rows=$this->read($this->db->group_end()->get('tr_jurnal'))->result_array();
            foreach ($rows as $row) $matches[$this->key($row)][]=mb_strtolower(implode(' ',[$row['coa'],$row['nm_coa'],$row['keterangan']]),'UTF-8');
        }
        return array_values(array_filter($headers,function($row)use($words,$matches){
            $hay=mb_strtolower(implode(' ',[$row['no_transaksi'],$row['display_no_transaksi'],$row['no_referensi'],$row['category'],$row['keterangan'],$row['nm_company'],$row['nm_divisi'],$row['tgl_jurnal'],$row['source_numbers'] ?? '',$row['posted_at'] ?? '',$row['posted_by'] ?? '',$row['extra_search'] ?? '',$row['debit'],$row['kredit'],implode(' ',$matches[$this->key($row)] ?? [])]),'UTF-8');
            foreach ($words as $word) if (mb_strpos($hay,$word,0,'UTF-8')===false) return false;
            return true;
        }));
    }
}
