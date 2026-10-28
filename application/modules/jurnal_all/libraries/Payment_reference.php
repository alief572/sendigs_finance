<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Payment_reference_issue extends DomainException
{
    public $category;
    public function __construct($category, $message) { parent::__construct($message); $this->category=$category; }
}

/** Resolves Payment identities without changing journal rows or accounting data. */
class Payment_reference
{
    const LOCK = 'payment_reference:write';
    private $ci;
    private $cache;
    private $resolved = [];
    private $preloaded = [];
    private $aliasesReady = null;

    public function aliasesReady()
    {
        if ($this->aliasesReady===null) $this->aliasesReady=(bool)$this->ci->db->table_exists('tr_jurnal_payment_reference_aliases');
        return $this->aliasesReady;
    }

    public function __construct($ci, $cache = false) { $this->ci = $ci; $this->cache = $cache; }

    public static function acquire($db)
    {
        $q = $db->query('SELECT GET_LOCK(?, 10) AS acquired', [self::LOCK]);
        if (!$q || !$q->row() || (int)$q->row()->acquired !== 1) throw new RuntimeException('Payment sedang diproses; coba kembali setelah selesai.');
    }

    public static function release($db)
    {
        try { if (!$db->query('SELECT RELEASE_LOCK(?)', [self::LOCK])) throw new RuntimeException(); }
        catch (Throwable $e) { self::log('Payment: gagal melepas lock referensi.'); }
    }

    public static function log($message)
    {
        try { log_message('error',$message); } catch (Throwable $ignore) {}
    }

    public function rows($key)
    {
        return $this->read($this->ci->db->order_by('id', 'asc')->get_where('tr_jurnal', ['jenis_transaksi'=>'Payment', 'no_transaksi'=>$key]));
    }

    private function read($result)
    {
        if ($result === false) throw new RuntimeException('Pemeriksaan Payment tidak dapat dipastikan.');
        return $result->result_array();
    }

    public static function fingerprint($rows)
    {
        usort($rows, function($a, $b) { return (int)$a['id'] <=> (int)$b['id']; });
        foreach ($rows as &$row) { ksort($row); foreach ($row as &$value) if ($value !== null) $value = (string)$value; unset($value); } unset($row);
        return hash('sha256', json_encode($rows, JSON_THROW_ON_ERROR));
    }

    public static function cents($amount)
    {
        $value = (string)$amount;
        if (!preg_match('/^\d+(?:\.\d{1,2})?$/D', $value)) throw new DomainException('Nominal jurnal Payment tidak valid.');
        $parts = explode('.', $value);
        if (strlen($parts[0]) > 13) throw new DomainException('Nominal jurnal Payment terlalu besar.');
        return (int)$parts[0] * 100 + (int)str_pad($parts[1] ?? '', 2, '0');
    }

    public function prime($keys, $knownApprovals = null)
    {
        $keys = array_values(array_diff(array_unique($keys), array_keys($this->resolved), array_keys($this->preloaded)));
        if (!$keys) return;
        $tokens = [];
        foreach ($keys as $key) $tokens = array_merge($tokens, array_map('trim',explode(',',$key)));
        $tokens = array_values(array_unique($tokens));
        $db = $this->ci->db; $approvals = []; $companions = []; $paid = []; $journals = [];
        if ($knownApprovals !== null) $approvals = $knownApprovals;
        else foreach (array_chunk($tokens,200) as $chunk) {
            foreach ($this->read($db->group_start()->where_in('id',$chunk)->or_where_in('id_payment',$chunk)->group_end()->get('payment_approve')) as $row) $approvals[$row['id']]=$row;
        }
        if (array_intersect(array_column($approvals,'tipe'),['refill_pettycash','petty_cash_hutang'])) {
            $companions = $this->read($db->select('no_transaksi,jenis_transaksi')->where_in('jenis_transaksi',['Refill Pettycash','Payment Hutang Petty Cash'])->group_by(['no_transaksi','jenis_transaksi'])->get('tr_jurnal'));
        }
        $vouchers = array_values(array_unique(array_filter(array_column($approvals,'id_payment'),'strlen')));
        foreach (array_chunk($vouchers,200) as $chunk) {
            foreach ($this->read($db->where_in('id',$chunk)->get('tr_payment_paid')) as $row) $paid[$row['id']]=($paid[$row['id']] ?? 0)+1;
        }
        foreach (array_chunk($keys,200) as $chunk) {
            foreach ($this->read($db->where('jenis_transaksi','Payment')->where_in('no_transaksi',$chunk)->get('tr_jurnal')) as $row) $journals[$row['no_transaksi']][]=$row;
        }
        $aliases=[];
        if ($this->aliasesReady()) {
            $ids=[];foreach($journals as $group)foreach($group as $row)$ids[]=$row['id'];
            foreach(array_chunk($ids,200) as $chunk)foreach($this->read($db->where('active',1)->where_in('journal_id',$chunk)->get('tr_jurnal_payment_reference_aliases')) as $alias)$aliases[$alias['journal_id']][]=$alias;
        }
        foreach ($keys as $key) {
            $parts = array_map('trim',explode(',',$key));
            $groupAliases=[];foreach(($journals[$key] ?? []) as $row)$groupAliases=array_merge($groupAliases,$aliases[$row['id']] ?? []);
            $this->preloaded[$key] = [
                'all'=>array_values(array_filter($approvals,function($p)use($parts){return in_array($p['id'],$parts,true)||in_array($p['id_payment'],$parts,true);})),
                'companions'=>$companions,
                'paid'=>$paid,
                'journal_rows'=>$journals[$key] ?? [],
                'aliases'=>$groupAliases
            ];
        }
    }

    private function paidCount($voucher, &$paid)
    {
        if (!array_key_exists($voucher,$paid)) $paid[$voucher]=count($this->read($this->ci->db->get_where('tr_payment_paid',['id'=>$voucher])));
        return $paid[$voucher];
    }

    private function validCandidate($approvals, &$paid)
    {
        if (!$approvals) return false;
        foreach ($approvals as $approval) {
            $voucher=trim((string)($approval['id_payment'] ?? ''));
            if ($voucher==='' || $this->paidCount($voucher,$paid)!==1) return false;
        }
        return true;
    }

    private function journalSupports($approvals, $rows)
    {
        $references=array_merge(array_map('strval',array_column($approvals,'id')),array_map('strval',array_column($approvals,'no_doc')));
        foreach ($rows as $row) {
            $description=trim((string)($row['keterangan'] ?? ''));
            if (preg_match_all('/\{REF:(.*?)\}/',$description,$matches)) {
                foreach ($matches[1] as $reference) if (in_array(trim($reference),$references,true)) return true;
            }
            $description=trim(preg_replace('/\{REF:.*?\}/','',$description));
            foreach ($approvals as $approval) {
                $suffix=' - '.$approval['id'];
                if (substr($description,-strlen($suffix))===$suffix) return true;
            }
        }
        return false;
    }

    private function resolveAliases($key, $rows, $loaded)
    {
        if (!$this->aliasesReady() || !$rows) return null;
        $aliases=$loaded!==null ? $loaded['aliases'] : $this->read($this->ci->db->where('active',1)->where_in('journal_id',array_column($rows,'id'))->get('tr_jurnal_payment_reference_aliases'));
        if (!$aliases) return null;
        $byId=[];$approvalIds=[];$references=[$key];
        foreach($aliases as $alias){
            $id=(string)$alias['journal_id'];
            if(isset($byId[$id]) || (string)$alias['voucher']!==$key) throw new Payment_reference_issue('ambiguous','Alias Payment bertentangan dengan identitas jurnal.');
            $ids=json_decode($alias['approval_ids'],true);
            if(!is_array($ids)||!$ids||trim((string)$alias['original_no_transaksi'])==='')throw new Payment_reference_issue('ambiguous','Relasi alias Payment tidak lengkap.');
            foreach($ids as $approvalId){if(!is_string($approvalId)||$approvalId==='')throw new Payment_reference_issue('ambiguous','ID approval alias Payment tidak valid.');$approvalIds[]=$approvalId;}
            $byId[$id]=$alias;
            $references[]=$alias['original_no_transaksi'];
            $references=array_merge($references,array_map('trim',explode(',',$alias['original_no_transaksi'])));
        }
        if(count($byId)!==count($rows))throw new Payment_reference_issue('ambiguous','Coverage alias Payment tidak lengkap; periksa anggota jurnal.');
        foreach($rows as $row)if(!isset($byId[(string)$row['id']])||$row['jenis_transaksi']!=='Payment'||$row['no_transaksi']!==$key)throw new Payment_reference_issue('ambiguous','Anggota alias Payment berubah.');
        $approvalIds=array_values(array_unique($approvalIds));
        $candidates=$loaded!==null ? $loaded['all'] : $this->read($this->ci->db->where_in('id',$approvalIds)->get('payment_approve'));
        $approvals=[];foreach($candidates as $approval)if(in_array((string)$approval['id'],$approvalIds,true))$approvals[(string)$approval['id']]=$approval;
        if(count($approvals)!==count($approvalIds))throw new Payment_reference_issue('ambiguous','Approval sumber alias Payment tidak tersedia.');
        foreach($approvals as $approval)if(trim((string)$approval['id_payment'])!==$key)throw new Payment_reference_issue('ambiguous','Relasi pembayaran alias berubah; posting diblokir.');
        $paid=$loaded!==null ? $loaded['paid'] : [];
        if($this->paidCount($key,$paid)!==1)throw new DomainException('Record pembayaran aktual alias tidak valid.');
        $references=array_merge($references,$approvalIds,array_column($approvals,'no_doc'));
        return ['voucher'=>$key,'approvals'=>array_values($approvals),'references'=>array_values(array_unique($references)),'source_keys'=>array_values(array_unique(array_column($aliases,'original_no_transaksi'))),'excluded_types'=>[],'alias_run_ids'=>array_values(array_unique(array_column($aliases,'run_id')))];
    }

    public function resolve($key, $journalRows = null)
    {
        $projection=$journalRows!==null;
        if (!$projection && $this->cache && isset($this->resolved[$key])) return $this->resolved[$key];
        $db = $this->ci->db;
        $tokens = array_values(array_unique(array_filter(array_map('trim', explode(',', $key)), 'strlen')));
        if (!$tokens) throw new DomainException('Nomor Payment kosong.');
        $loaded = $this->preloaded[$key] ?? null;
        if ($this->aliasesReady()) {
            $aliasRows=$journalRows!==null ? $journalRows : ($loaded!==null ? $loaded['journal_rows'] : $this->rows($key));
            $aliased=$this->resolveAliases($key,$aliasRows,$loaded);
            if ($aliased!==null) {
                if (!$projection && $this->cache)$this->resolved[$key]=$aliased;
                return $aliased;
            }
        }
        $paid=$loaded ? $loaded['paid'] : [];
        $all = $loaded ? $loaded['all'] : $this->read($db->group_start()->where_in('id', $tokens)->or_where_in('id_payment', $tokens)->group_end()->get('payment_approve'));
        $types = ['Refill Pettycash'=>'refill_pettycash', 'Payment Hutang Petty Cash'=>'petty_cash_hutang'];
        $companions = $loaded ? $loaded['companions'] : (array_intersect(array_column($all,'tipe'),array_values($types)) ? $this->read($db->select('no_transaksi,jenis_transaksi')->where_in('jenis_transaksi',array_keys($types))->group_by(['no_transaksi','jenis_transaksi'])->get('tr_jurnal')) : []);
        $excluded = [];
        $related = array_merge($tokens,array_column($all,'id'));
        foreach ($companions as $row) if (isset($types[$row['jenis_transaksi']]) && ($row['no_transaksi']===$key || array_intersect(array_map('trim',explode(',',$row['no_transaksi'])),$related))) $excluded[] = $types[$row['jenis_transaksi']];
        $compatible = function($row) use ($excluded) { return !in_array($row['tipe'], $excluded, true); };
        $selected = [];
        foreach ($tokens as $token) {
            $ids = []; $vouchers = [];
            foreach ($all as $row) {
                if ((string)$row['id'] === $token) $ids[] = $row;
                if ((string)($row['id_payment'] ?? '') === $token) $vouchers[] = $row;
            }
            if ($ids && $vouchers) {
                $different=false;
                foreach ($ids as $row) if (trim((string)($row['id_payment'] ?? '')) !== $token) $different=true;
                if ($different) {
                    $idCandidates=array_values(array_filter($ids,$compatible));
                    $voucherCandidates=array_values(array_filter($vouchers,$compatible));
                    if ($journalRows===null) $journalRows=$loaded ? $loaded['journal_rows'] : $this->rows($key);
                    $idSupported=$this->journalSupports($idCandidates,$journalRows);
                    $voucherSupported=$this->journalSupports($voucherCandidates,$journalRows);
                    if ($idSupported && !$voucherSupported) $vouchers=[];
                    elseif ($voucherSupported && !$idSupported) $ids=[];
                    elseif (!$idSupported && !$voucherSupported) {
                        $idValid=$this->validCandidate($idCandidates,$paid);
                        $voucherValid=$this->validCandidate($voucherCandidates,$paid);
                        if ($idValid && !$voucherValid) $vouchers=[];
                        elseif ($voucherValid && !$idValid) $ids=[];
                        else throw new Payment_reference_issue('ambiguous','Nomor approval dan voucher bertabrakan; relasi jurnal Payment masih ambigu.');
                    } else throw new Payment_reference_issue('ambiguous','Referensi jurnal mendukung dua relasi Payment berbeda; mapping ambigu.');
                }
            }
            $matches = $vouchers ?: $ids;
            if (!$matches) throw new DomainException('Approval/voucher Payment tidak ditemukan.');
            foreach ($matches as $row) if ($compatible($row)) $selected[$row['id']] = $row;
        }
        if (!$selected) throw new DomainException('Dokumen sumber Payment tidak ditemukan setelah pemisahan jurnal pendamping.');
        $numbers = [];
        foreach ($selected as $row) {
            $voucher = trim((string)($row['id_payment'] ?? ''));
            if ($voucher === '') throw new DomainException('Approval belum memiliki nomor pembayaran aktual.');
            $numbers[$voucher] = true;
        }
        if (count($numbers) !== 1) throw new Payment_reference_issue('ambiguous','Kelompok Payment memiliki beberapa voucher; perlu pemeriksaan terpisah.');
        $voucher = (string)array_key_first($numbers);
        $paidCount = $this->paidCount($voucher,$paid);
        if ($paidCount !== 1) throw new DomainException('Record pembayaran aktual tidak valid.');
        $references = array_merge([$key, $voucher], $tokens, array_keys($selected), array_column($selected, 'no_doc'));
        $result = ['voucher'=>$voucher, 'approvals'=>array_values($selected), 'references'=>array_values(array_unique($references)), 'excluded_types'=>array_values(array_unique($excluded))];
        if (!$projection && $this->cache) $this->resolved[$key] = $result;
        return $result;
    }

    public static function target($company)
    {
        if ((string)$company === '4') return 'accounting_vuca';
        if (in_array((string)$company, ['1','6','7'], true)) return 'accounting_stm';
        if ((string)$company === '3') return 'accounting_sustain';
        throw new DomainException('Company Payment tidak dikenali.');
    }

    public function inspect($key, $accounting = true, $targetOnly = false)
    {
        $db = $this->ci->db;
        $rows = $this->rows($key);
        if (!$rows) throw new DomainException('Kelompok Payment tidak tersedia.');
        foreach ($rows as $row) {
            if ((string)($row['sts'] ?? '')==='1' || !empty($row['tgl_posting']) || !empty($row['posted_by'])) throw new Payment_reference_issue('posted','Kelompok Payment sudah diposting atau statusnya tidak aman.');
            if (!isset($row['sts']) || !in_array((string)$row['sts'], ['', '0'], true)) throw new Payment_reference_issue('invalid','Status kelompok Payment tidak aman.');
        }
        if (!$db->table_exists('tr_jurnal_postings')) throw new Payment_reference_issue('schema_missing','Schema metadata posting belum tersedia; jalankan migrasi metadata posting terlebih dahulu.');
        $metadata = $this->read($db->get_where('tr_jurnal_postings', ['no_transaksi'=>$key, 'jenis_transaksi'=>'Payment']));
        if ($metadata) throw new Payment_reference_issue('posted','Metadata posting Payment sudah ada.');
        $resolved = $this->resolve($key);
        $voucher = $resolved['voucher'];
        if (!empty($resolved['source_keys']) && $this->read($db->where('jenis_transaksi','Payment')->where_in('no_transaksi',$resolved['source_keys'])->get('tr_jurnal_postings'))) throw new Payment_reference_issue('posted','Metadata posting nomor sumber alias sudah ada.');
        // The voucher is already established by the source relationship, not a free approval key.
        if ($voucher !== $key && $this->rows($voucher)) throw new Payment_reference_issue('collision','Nomor tujuan bertabrakan dengan kelompok Payment lain.');
        $voucherApprovals = $this->read($db->get_where('payment_approve', ['id_payment'=>$voucher]));
        $identities = array_merge([$voucher], array_column($voucherApprovals, 'id'));
        $groups = $this->read($db->select('no_transaksi')->where('jenis_transaksi','Payment')->group_by('no_transaksi')->get('tr_jurnal'));
        foreach ($groups as $group) {
            if ($group['no_transaksi'] === $key) continue;
            if (array_intersect(array_map('trim',explode(',',$group['no_transaksi'])), $identities)) {
                try { $other=$this->resolve($group['no_transaksi']); }
                catch (Throwable $error) { throw new Payment_reference_issue('collision','Relasi kelompok Payment lain belum dapat dipastikan; posting diblokir.'); }
                if ($other['voucher']===$voucher) throw new Payment_reference_issue('collision','Voucher dipakai kelompok Payment lain; penggabungan/posting diblokir.');
            }
        }
        // A legacy group must not be posted after a canonical sibling was posted.
        $metadata = $this->read($db->get_where('tr_jurnal_postings', ['no_transaksi'=>$voucher, 'jenis_transaksi'=>'Payment']));
        if ($metadata) throw new Payment_reference_issue('posted','Metadata posting voucher sudah ada.');
        $companies = array_values(array_unique(array_map('strval', array_column($rows, 'id_company'))));
        if (count($companies) !== 1) throw new DomainException('Kelompok Payment melintasi company.');
        $target = self::target($companies[0]);
        $debit = 0; $credit = 0;
        foreach ($rows as $row) { $debit += self::cents($row['debit']); $credit += self::cents($row['kredit']); }
        if (!$debit || $debit !== $credit) throw new DomainException('Jurnal Payment tidak balance atau kosong.');
        $result = array_merge($resolved, ['key'=>$key, 'rows'=>$rows, 'fingerprint'=>self::fingerprint($rows), 'company'=>$companies[0], 'target'=>$target]);
        if ($accounting) $this->assertNoAccounting($result,$targetOnly);
        return $result;
    }

    public function inspectForPosting($key)
    {
        $context=$this->inspect($key,true,true);
        self::transactional($this->ci->db,['tr_jurnal','tr_jurnal_payment_attempts']);
        $acc=$this->ci->load->database($context['target'],true);
        if(!$acc)throw new Payment_reference_issue('unverified','Koneksi accounting tujuan belum tersedia.');
        $acc->db_debug=false;$context['target_engines']=self::postingEngines($acc);
        $q=$acc->get_where('pastibisa_tb_cabang',['nocab'=>'101']);
        if(!$q||$q->num_rows()!==1||!preg_match('/^\d+$/D',(string)$q->row()->nobuk))throw new Payment_reference_issue('invalid','Counter accounting tujuan tidak valid.');
        $coas=[];foreach($context['rows'] as $row)if(self::cents($row['debit'])||self::cents($row['kredit']))$coas[]=$row['coa'];
        $q=$acc->select('no_perkiraan')->where_in('no_perkiraan',array_unique($coas))->get('coa_master');
        if(!$q||array_diff($coas,array_column($q->result_array(),'no_perkiraan')))throw new Payment_reference_issue('invalid','COA Payment tidak tersedia pada accounting tujuan.');
        return $context;
    }

    public function assertNoAccounting($context, $targetOnly = false)
    {
        $references = $context['references'];
        foreach ($context['rows'] as $row) {
            if (preg_match_all('/\{REF:(.*?)\}/', (string)$row['keterangan'], $matches)) $references = array_merge($references, $matches[1]);
        }
        $references = array_values(array_unique(array_filter(array_map('trim', $references), 'strlen')));
        $db = $this->ci->db;
        $registryReady=$db->table_exists('tr_jurnal_payment_attempts');
        $attempts = $registryReady ? $this->read($db->where('voucher', $context['voucher'])->get('tr_jurnal_payment_attempts')) : [];
        foreach ($attempts as $attempt) if ($attempt['status'] !== 'failed_safe') throw new Payment_reference_issue('reconciliation_required','Percobaan posting Payment sudah ada; periksa rekonsiliasi, jangan posting ulang.');
        if (!$targetOnly) {
            if (!$db->table_exists('jurnaltras')) throw new Payment_reference_issue('unverified','Catatan jurnal pembayaran belum dapat diperiksa; posting tetap diblokir.');
            $existing = $this->read($db->group_start()->where_in('no_reff', $references)->or_where_in('no_request', $references)->group_end()->get('jurnaltras'));
            if ($existing) throw new Payment_reference_issue('accounting_exists','Voucher '.$context['voucher'].' sudah memiliki catatan jurnal pembayaran. Status sumber masih pending dan perlu rekonsiliasi; posting ulang diblokir.');
        }
        $names = $targetOnly ? [$context['target']] : array_unique([$context['target'], 'accounting']);
        foreach ($names as $name) {
            $acc = $this->ci->load->database($name, true);
            if (!$acc) throw new Payment_reference_issue('unverified','Koneksi accounting belum dapat diperiksa; posting tetap diblokir.');
            foreach (['jurnal','japh'] as $table) {
                if (!$acc->table_exists($table)) throw new Payment_reference_issue('schema_missing','Tabel accounting belum dapat diperiksa; posting tetap diblokir.');
                if ($this->read($acc->where_in('no_reff', $references)->get($table))) throw new Payment_reference_issue('accounting_exists',$targetOnly?'Jurnal Payment sudah ada pada accounting company tujuan; posting ulang diblokir.':'Hasil accounting terkait sudah ada; Payment dilewati untuk rekonsiliasi.');
            }
        }
        if (!$registryReady) throw new Payment_reference_issue('schema_missing','Registry percobaan Payment belum tersedia; jalankan migrasi 007_payment_reference_attempts.sql sebelum posting.');
    }

    public static function postingEngines($db)
    {
        $q=$db->query("SELECT TABLE_NAME,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN (?,?,?)",['jurnal','japh','pastibisa_tb_cabang']);
        if(!$q||count($q->result_array())!==3)throw new Payment_reference_issue('schema_missing','Tabel posting accounting tujuan belum lengkap.');
        $map=[];foreach($q->result_array() as $row){$engine=strtoupper($row['ENGINE']);if(!in_array($engine,['INNODB','MYISAM'],true))throw new Payment_reference_issue('invalid','Engine accounting tujuan belum didukung untuk posting Payment.');$map[$row['TABLE_NAME']]=$engine;}
        return $map;
    }

    public static function transactional($db, $tables)
    {
        $q = $db->query('SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('.implode(',', array_fill(0, count($tables), '?')).')', $tables);
        if (!$q || count($q->result_array()) !== count($tables)) throw new RuntimeException('Engine tabel Payment belum dapat diverifikasi.');
        foreach ($q->result_array() as $row) if (strtoupper($row['ENGINE']) !== 'INNODB') throw new RuntimeException('Posting/patch Payment memerlukan tabel InnoDB; tidak ada data yang diubah.');
    }
}
