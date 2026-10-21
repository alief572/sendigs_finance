<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__ . '/Invoice_consulting.php';

class Invoice_consulting_service
{
    public $ci, $db;
    private $connections = array();

    public function __construct($ci) { $this->ci=$ci; $this->db=$ci->db; }

    public function rows($sql, $params=array())
    {
        $result=$this->db->query($sql,$params);
        if (!$result) throw new RuntimeException('Data transaksi tidak dapat dibaca.');
        return $result->result_array();
    }

    public function one($sql, $params=array()) { $rows=$this->rows($sql,$params); return $rows ? $rows[0] : null; }

    public function checked($result)
    {
        if ($result === false || !$this->db->trans_status()) throw new RuntimeException('Penyimpanan gagal; perubahan dibatalkan.');
        return $result;
    }

    public function required($data, $key, $max=100)
    {
        if (!isset($data[$key]) || !is_scalar($data[$key]) || trim((string)$data[$key])==='' || strlen(trim((string)$data[$key]))>$max)
            throw new InvalidArgumentException('Field '.$key.' wajib diisi dengan benar.');
        return trim((string)$data[$key]);
    }

    public function schema()
    {
        foreach (array('tr_invoicing'=>array('consulting_version','consulting_snapshot','consulting_company_id','tax_treatment','consulting_top_key'),
            'tr_penerimaan_piutang'=>array('consulting_batch'),'tr_penerimaan_piutang_detail'=>array('consulting_version','consulting_reduction','consulting_snapshot')) as $table=>$columns)
            foreach($columns as $column) if(!$this->db->field_exists($column,$table)) throw new RuntimeException('Terapkan migration 003_consulting_v3 sebelum memakai invoice konsultasi baru.');
        if(!$this->db->table_exists('ms_invoice_coa_mapping'))throw new RuntimeException('Mapping invoice belum tersedia; terapkan migration 003_consulting_v3.');
    }

    public function serialized($callback)
    {
        $locks=array();
        try {
            foreach(array('sendigs_receipt_v2','sendigs_invoice_write') as $name){
                $row=$this->one('SELECT GET_LOCK(?,10) AS acquired',array($name));
                if(!$row || (int)$row['acquired']!==1)throw new RuntimeException('Transaksi lain sedang berjalan; coba kembali.');
                $locks[]=$name;
            }
            return $callback();
        } catch(Throwable $error){echo json_encode(array('status'=>0,'save'=>0,'msg'=>'Transaksi tidak dapat diproses. Muat ulang sebelum mencoba kembali.','message'=>'Transaksi tidak dapat diproses. Muat ulang sebelum mencoba kembali.'));}
        finally {foreach(array_reverse($locks)as $name)$this->db->query('SELECT RELEASE_LOCK(?)',array($name));}
    }

    public function transactional($tables)
    {
        foreach ($tables as $table) {
            $row=$this->one('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',array($table));
            if (!$row || strtoupper($row['ENGINE'])!=='INNODB') throw new RuntimeException('Tabel '.$table.' harus memakai InnoDB sebelum transaksi ini dapat digunakan.');
        }
    }

    public function mutate($action, $callback, $module='invoicing')
    {
        $started=false; $locks=array(); $recorder=null; $owns=false; $oldDebug=$this->db->db_debug;
        try {
            try {
                $this->ci->load->library('Module_audit_recorder');
                $auditCi =& get_instance();
                $recorder=$auditCi->module_audit_recorder;
                $owns=$recorder->begin($module,$action,array('data'=>array('version'=>3)),array('version','outcome'));
            } catch (Throwable $auditError) { log_message('error','Consulting audit unavailable.'); }
            $this->db->db_debug=false;
            $this->schema();
            foreach (array('sendigs_receipt_v2','sendigs_invoice_write') as $name) {
                $lock=$this->one('SELECT GET_LOCK(?,10) AS acquired',array($name));
                if (!$lock || (int)$lock['acquired']!==1) throw new RuntimeException('Transaksi lain sedang berjalan; coba kembali.');
                $locks[]=$name;
            }
            $this->checked($this->db->trans_begin()); $started=true;
            $payload=$callback();
            $this->checked($this->db->trans_commit()); $started=false;
            $result=array('status'=>1,'msg'=>'Data berhasil disimpan.','message'=>'Data berhasil disimpan.') + (is_array($payload)?$payload:array());
        } catch (Throwable $error) {
            if ($started) $this->db->trans_rollback();
            $message=$error instanceof InvalidArgumentException || $error instanceof RuntimeException ? $error->getMessage() : 'Proses gagal; perubahan dibatalkan.';
            $result=array('status'=>0,'msg'=>$message,'message'=>$message);
        } finally {
            foreach (array_reverse($locks) as $name) {
                try { $this->db->query('SELECT RELEASE_LOCK(?)',array($name)); } catch (Throwable $ignored) { log_message('error','Consulting lock release failed.'); }
            }
            $this->db->db_debug=$oldDebug;
        }
        try {
            if ($owns) $recorder->complete('Invoice konsultasi: '.$action,$result['status'],array('data'=>array('outcome'=>$result['status']?'committed':'rejected'),'sql'=>null));
        } catch (Throwable $auditError) { log_message('error','Consulting audit completion unavailable.'); }
        return $result;
    }

    public static function accounting_group($owner)
    {
        if ((string)$owner==='4') return 'accounting_vuca';
        if (in_array((string)$owner,array('1','6','7'),true)) return 'accounting_stm';
        return 'accounting_sustain';
    }

    public static function is_journal_owner($id)
    {
        return is_scalar($id) && in_array((string)$id,array('7','3','4'),true);
    }

    public function journal_owner($id)
    {
        if (!self::is_journal_owner($id)) throw new RuntimeException('Company pemilik jurnal hanya boleh STM, Sustain, atau Vuca. Pilih ulang pemilik jurnal dan simpan mapping.');
        return $this->company($id);
    }

    public function accounting($owner)
    {
        $group=self::accounting_group($owner);
        if (!isset($this->connections[$group])) $this->connections[$group]=$this->ci->load->database($group,true);
        return $this->connections[$group];
    }

    public function company($id)
    {
        $r=$this->one('SELECT id,nm_company FROM '.DBCNL.'.kons_tr_company WHERE id=?',array($id));
        if (!$r || trim($r['nm_company'])==='') throw new RuntimeException('Company tidak ditemukan.');
        return $r;
    }

    public function master($owner)
    {
        $db=$this->accounting($owner); $old=$db->db_debug; $db->db_debug=false;
        try {
            $result=$db->query('SELECT DISTINCT no_perkiraan,nama FROM coa_master ORDER BY no_perkiraan');
            if (!$result) throw new RuntimeException('Master COA tujuan accounting tidak dapat dibaca.');
            return $result->result_array();
        } finally { $db->db_debug=$old; }
    }

    public function validate_accounts($owner, $codes)
    {
        if (!is_array($codes) || array_diff(array_keys(Invoice_consulting::roles()),array_keys($codes))) throw new InvalidArgumentException('Sembilan mapping COA wajib dilengkapi.');
        $master=array();
        foreach ($this->master($owner) as $row) $master[$row['no_perkiraan']][]=$row['nama'];
        $accounts=array();
        foreach (Invoice_consulting::roles() as $role=>$label) {
            $code=$codes[$role];
            if (!is_string($code) || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D',$code) || substr($code,0,4)==='0000'
                || !isset($master[$code]) || count($master[$code])!==1 || trim($master[$code][0])==='') throw new InvalidArgumentException('Mapping '.$label.' tidak valid pada master COA tujuan.');
            $accounts[$role]=array('code'=>$code,'name'=>$master[$code][0]);
        }
        $fixed=array('receivable','vat_domestic','vat_wapu_prepaid','vat_wapu_payable','pph');
        $fixedCodes=array_map(function($role)use($codes){return $codes[$role];},$fixed);
        if (count(array_unique($fixedCodes))!==5) throw new InvalidArgumentException('Akun piutang dan pajak harus berbeda.');
        foreach (Invoice_consulting::revenues() as $role=>$label) if (in_array($codes[$role],$fixedCodes,true)) throw new InvalidArgumentException('COA penjualan tidak boleh memakai akun piutang/pajak.');
        return $accounts;
    }

    public function mapping($source, $lock=false)
    {
        $this->schema();
        $row=$this->one('SELECT * FROM ms_invoice_coa_mapping WHERE source_company_id=?'.($lock?' FOR UPDATE':''),array($source));
        if (!$row) throw new RuntimeException('Lengkapi Pengaturan COA Invoicing untuk company sumber terlebih dahulu.');
        $codes=json_decode($row['accounts'],true);
        return array('source_company_id'=>(string)$source,'owner'=>$this->journal_owner($row['owner_company_id']),
            'accounts'=>$this->validate_accounts($row['owner_company_id'],$codes));
    }

    public function save_mapping($post)
    {
        return $this->mutate('save_coa_mapping',function()use($post){
            if (!$this->ci->auth->is_admin()) throw new RuntimeException('Pengaturan COA hanya dapat diubah oleh admin.');
            if (!has_permission('Invoicing.Configure')) throw new RuntimeException('Tidak memiliki izin konfigurasi invoice.');
            $this->transactional(array('ms_invoice_coa_mapping'));
            $source=$this->required($post,'source_company_id',20); $this->company($source);
            $owner=$this->required($post,'owner_company_id',20); $this->journal_owner($owner);
            $accounts=$this->validate_accounts($owner,$post['accounts']??null);
            $old=$this->one('SELECT * FROM ms_invoice_coa_mapping WHERE source_company_id=? FOR UPDATE',array($source));
            if (!hash_equals(Invoice_calculation::fingerprint($old?:array()),$this->required($post,'mapping_fingerprint',64))) throw new RuntimeException('Mapping berubah sejak form dibuka; muat ulang.');
            $codes=array_map(function($a){return $a['code'];},$accounts);
            $fields=array('owner_company_id'=>$owner,'accounts'=>json_encode($codes),'updated_by'=>$this->ci->auth->user_id(),'updated_at'=>date('Y-m-d H:i:s.u'));
            if ($old) $this->checked($this->db->update('ms_invoice_coa_mapping',$fields,array('source_company_id'=>$source)));
            else $this->checked($this->db->insert('ms_invoice_coa_mapping',$fields+array('source_company_id'=>$source)));
        });
    }

    public function source($planId, $lock=false)
    {
        $plan=$this->one('SELECT * FROM kons_tr_plan_tagih_detail WHERE id=?'.($lock?' FOR UPDATE':''),array($planId));
        if (!$plan || (string)$plan['status_terakhir']!=='1') throw new RuntimeException('TOP aktif tidak ditemukan.');
        $actual=$this->one("SELECT * FROM kons_tr_actual_plan_tagih WHERE id_detail_plan_tagih=? AND tagih_mundur='1' ORDER BY id DESC".($lock?' FOR UPDATE':''),array($planId));
        if (!$actual) throw new RuntimeException('Actual plan tagih untuk TOP belum tersedia.');
        $spk=$this->one('SELECT * FROM '.DBCNL.'.kons_tr_spk_penawaran WHERE id_spk_penawaran=?',array($plan['id_spk_penawaran']));
        $quote=$this->one('SELECT * FROM '.DBCNL.'.kons_tr_penawaran WHERE id_quotation=?',array($plan['id_penawaran']));
        if (!$spk || !$quote) throw new RuntimeException('SPK atau penawaran tidak ditemukan.');
        $source=!empty($quote['company'])?$quote['company']:$spk['id_company'];
        return array('plan'=>$plan,'actual'=>$actual,'spk'=>$spk,'quote'=>$quote,'source_company_id'=>$source);
    }

    public function save_invoice($post, $edit=false)
    {
        return $this->mutate($edit?'update_invoice':'save_invoice',function()use($post,$edit){
            if (!has_permission($edit?'Invoicing.Manage':'Invoicing.Add')) throw new RuntimeException('Tidak memiliki izin menyimpan invoice.');
            $this->transactional(array('tr_invoicing','tr_jurnal','kons_tr_actual_plan_tagih','kons_tr_plan_tagih_detail','ms_invoice_sequence','ms_invoice_coa_mapping','tr_penerimaan_piutang_detail'));
            $date=$this->required($post,'tanggal_invoice',10); $parsed=DateTime::createFromFormat('!Y-m-d',$date);
            if (!$parsed || $parsed->format('Y-m-d')!==$date) throw new InvalidArgumentException('Tanggal invoice tidak valid.');
            $po=$this->required($post,'nomor_po'); $faktur=$this->required($post,'nomor_faktur');
            $existing=null;
            if ($edit) {
                $existing=$this->one('SELECT * FROM tr_invoicing WHERE id=? FOR UPDATE',array($this->required($post,'id_invoicing')));
                if (!$existing || !Invoice_consulting::is_new($existing)) throw new RuntimeException('Invoice bukan konsultasi versi baru.');
                if (!hash_equals(Invoice_calculation::fingerprint($existing),$this->required($post,'invoice_fingerprint',64))) throw new RuntimeException('Invoice berubah; buka ulang form.');
                $receipts=$this->rows('SELECT id FROM tr_penerimaan_piutang_detail WHERE id_inv=? FOR UPDATE',array($existing['id']));
                $journals=$this->rows("SELECT * FROM tr_jurnal WHERE no_transaksi=? AND jenis_transaksi='Invoicing' FOR UPDATE",array($existing['id']));
                if ($receipts || !$journals || $this->one("SELECT id FROM tr_jurnal WHERE jenis_transaksi='Penerimaan Piutang' AND keterangan LIKE ?",array('% - '.$existing['no_invoice']))) throw new RuntimeException('Invoice memiliki penerimaan atau jurnal tidak lengkap; tidak dapat diedit.');
                foreach ($journals as $j) if ((string)$j['sts']==='1') throw new RuntimeException('Jurnal invoice sudah diposting.');
                if (Invoice_calculation::cents($existing['saldo_piutang'])!==Invoice_calculation::cents($existing['total_akhir_jurnal'])) throw new RuntimeException('Saldo invoice berubah; perlu rekonsiliasi.');
                $snapshot=Invoice_consulting::snapshot($existing);
                $source=array('plan'=>array('id'=>$existing['id_detail_plan_tagih']), 'spk'=>array('id_spk_penawaran'=>$existing['id_spk_penawaran']));
                $amount=$existing['total_nominal']; $number=$existing['no_invoice']; $id=$existing['id'];
            } else {
                $source=$this->source($this->required($post,'id_detail_plan_tagih',50),true);
                $plan=$source['plan']; $actual=$source['actual'];
                if ((string)$plan['sts_invoice']==='1' || (string)$actual['sts_invoice']==='1'
                    || $this->one('SELECT id FROM tr_invoicing WHERE id_detail_plan_tagih=? OR id_actual_plan_tagih=? FOR UPDATE',array($plan['id'],$actual['id']))) throw new RuntimeException('TOP sudah dibuatkan invoice.');
                $snapshot=$this->mapping($source['source_company_id'],true);
                if (!hash_equals(Invoice_calculation::fingerprint(array('source'=>$source,'mapping'=>$snapshot)),$this->required($post,'source_fingerprint',64))) throw new RuntimeException('TOP atau mapping berubah; buka ulang form.');
                $amount=$actual['nominal_payment'];
                $number=$this->ci->Invoicing_model->generate_no_invoice($snapshot['owner']['id'],'0');
                if ($this->one('SELECT id FROM tr_invoicing WHERE no_invoice=? FOR UPDATE',array($number))) throw new RuntimeException('Nomor invoice sudah digunakan; periksa sequence.');
                $id=$this->ci->Invoicing_model->generate_id();
            }
            $type=$this->required($post,'tax_treatment',8); $role=$this->required($post,'sales_role',40);
            $t=Invoice_consulting::totals($amount,$type);
            $lines=Invoice_consulting::journal($t,$snapshot['accounts'],$role,$number,$source['spk']['id_spk_penawaran']);
            $snapshot['sales_role']=$role;
            if (!$edit) $snapshot['top']=array('term_payment'=>$source['actual']['term_payment']??$source['plan']['term_payment']??'',
                'persen_payment'=>$source['actual']['persen_payment']??$source['plan']['persen_payment']??'0',
                'desc_payment'=>$source['actual']['desc_payment']??$source['plan']['desc_payment']??'');
            $fields=Invoice_consulting::fields($t)+array('tanggal_invoice'=>$date,'no_invoice'=>$number,'no_po'=>$po,'no_faktur'=>$faktur,
                'consulting_version'=>3,'tax_treatment'=>$type,'consulting_company_id'=>$snapshot['owner']['id'],'consulting_snapshot'=>json_encode($snapshot),
                'consulting_top_key'=>(string)$source['plan']['id']);
            if ($edit) {
                $this->checked($this->db->delete('tr_jurnal',array('no_transaksi'=>$id,'jenis_transaksi'=>'Invoicing')));
                $this->checked($this->db->update('tr_invoicing',$fields,array('id'=>$id)));
            } else {
                $spk=$source['spk'];
                $project=$this->one('SELECT nm_paket FROM '.DBCNL.'.kons_master_konsultasi_header WHERE id_konsultasi_h=?',array($spk['id_project']));
                if (!$project) throw new RuntimeException('Project invoice tidak ditemukan.');
                $fields+=array('id'=>$id,'id_actual_plan_tagih'=>$source['actual']['id'],'id_detail_plan_tagih'=>$source['plan']['id'],
                    'id_penawaran'=>$source['plan']['id_penawaran'],'id_spk_penawaran'=>$spk['id_spk_penawaran'],'nm_project'=>$project['nm_paket'],
                    'tipe_invoice'=>'0','non_kons'=>'0','created_by'=>$this->ci->auth->user_id(),'created_date'=>date('Y-m-d H:i:s'));
                foreach (array('id_customer','nm_customer','address','id_project','id_project_leader','nm_project_leader','id_sales','nm_sales') as $key) $fields[$key]=$spk[$key]??'';
                $this->checked($this->db->insert('tr_invoicing',$fields));
                $this->checked($this->db->update('kons_tr_actual_plan_tagih',array('sts_invoice'=>'1'),array('id'=>$source['actual']['id'])));
                $this->checked($this->db->update('kons_tr_plan_tagih_detail',array('sts_invoice'=>'1','tgl_invoice'=>$date),array('id'=>$source['plan']['id'])));
            }
            $journals=array();
            foreach ($lines as $offset=>$line) $journals[]=array('no_jurnal'=>Invoice_calculation::journal_number($this->db,$offset+1),
                'tgl_jurnal'=>$date,'coa'=>$line['coa'],'nm_coa'=>$line['name'],'id_company'=>$snapshot['owner']['id'],'nm_company'=>$snapshot['owner']['nm_company'],
                'debit'=>Invoice_calculation::decimal($line['debit']),'kredit'=>Invoice_calculation::decimal($line['credit']),
                'keterangan'=>$line['description'],'sts'=>'0','no_transaksi'=>$id,'jenis_transaksi'=>'Invoicing',
                'created_by'=>$this->ci->auth->user_id(),'created_date'=>date('Y-m-d H:i:s'));
            $this->checked($this->db->insert_batch('tr_jurnal',$journals));
            return array('id'=>$id);
        });
    }
}
