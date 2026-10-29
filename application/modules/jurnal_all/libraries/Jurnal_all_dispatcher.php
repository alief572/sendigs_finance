<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Jurnal_all_dispatcher
{
    const ROUTES = [
        'INV'=>['post'=>'jurnal_invoicing/save_posting_jurnal','revise'=>'jurnal_invoicing/update_sts_revisi_jurnal'],
        'KWD'=>['post'=>'kwitansi_deposit/post_journal'],
        'RCV'=>['post'=>'jurnal_penerimaan/save_posting_jurnal','revise'=>'jurnal_penerimaan/update_sts_revisi_jurnal'],
        'PAY'=>['post'=>'jurnal_payment/save_posting_jurnal'],
        'EXP'=>['post'=>'jurnal_expense/save_posting_jurnal'],
        'PCS'=>['post'=>'jurnal_payment_petty_cash/save_posting_jurnal']
    ];
    private $ci;

    public function __construct($params=[])
    {
        $this->ci=$params['ci'] ?? get_instance();
    }

    public function execute($action,$id,$expectedSource,$payload)
    {
        if (!has_permission('Jurnal_All.Manage')) throw new RuntimeException('Anda tidak memiliki akses posting jurnal.');
        $model=$this->ci->Jurnal_all_model;
        $detail=$model->detail($id,false); $header=$detail['header'];
        $source=$header['source'];
        if ($source!==$expectedSource || !isset(self::ROUTES[$source][$action])) throw new InvalidArgumentException('Aksi atau sumber jurnal tidak valid.');
        if(!empty($header['debt_group'])){
            if($action!=='post')throw new InvalidArgumentException('Aksi kelompok hutang tidak tersedia.');
            require_once __DIR__.'/Jurnal_debt_posting.php';
            return (new Jurnal_debt_posting(['ci'=>$this->ci]))->execute($id,$expectedSource);
        }
        if ($action==='post'&&!$model->posting_metadata_ready()) throw new RuntimeException('Jalankan migrasi metadata posting Jurnal All sebelum posting.');
        $actor=$action==='post'?['id'=>$this->ci->auth->user_id(),'name'=>$this->ci->auth->user_name()]:null;
        if ($actor!==null&&(trim((string)$actor['id'])===''||trim((string)$actor['name'])==='')) throw new RuntimeException('User posting tidak tersedia.');
        $reason=is_scalar($payload['alasan_revisi'] ?? null) ? trim((string)$payload['alasan_revisi']) : '';
        if ($action==='revise'&&($reason===''||mb_strlen($reason,'UTF-8')>2000)) throw new InvalidArgumentException('Alasan revisi wajib diisi (maksimal 2000 karakter).');
        $key='jurnal_all:'.substr(hash('sha256',$model->key($header)),0,48);
        $lock=$this->ci->db->query('SELECT GET_LOCK(?, 10) AS acquired',[$key]);
        if (!$lock || !($lock=$lock->row()) || (int)$lock->acquired!==1) throw new RuntimeException('Transaksi sedang diproses. Muat ulang sebelum mencoba kembali.');
        $original=$_POST;
        $bufferLevel=ob_get_level();
        $referenceLocked=false;
        try {
            if ($header['jenis_transaksi']==='Payment') {
                require_once __DIR__.'/Payment_reference.php';
                Payment_reference::acquire($this->ci->db);
                $referenceLocked=true;
            }
            $fresh=$model->detail($id,false);
            if ($model->key($fresh['header'])!==$model->key($header)) throw new InvalidArgumentException('Kelompok jurnal berubah. Silakan muat ulang.');
            if ($action==='post') {
                if (($fresh['can_post'] ?? true) === false) throw new DomainException(($fresh['header']['company_error'] ?? '') ?: 'Posting diblokir. Periksa peringatan jurnal.');
                if (!$fresh['is_balance']) throw new InvalidArgumentException('Jurnal tidak balance atau nominal kosong.');
                foreach ($fresh['rows'] as $row) if ($model->cents($row['debit'])<0||$model->cents($row['kredit'])<0) throw new InvalidArgumentException('Nominal jurnal tidak valid.');
            }
            // Legacy handlers read POST; never forward client-controlled routing or amounts.
            $_POST=['id'=>(int)$id,'no_transaksi'=>$header['no_transaksi'],'jenis_transaksi'=>$header['jenis_transaksi']];
            if ($action==='revise') $_POST['alasan_revisi']=$reason;
            $response=Modules::run(self::ROUTES[$source][$action]);
            $response=is_array($response)?$response:json_decode((string)$response,true);
            if (!is_array($response)||(!array_key_exists('save',$response)&&!array_key_exists('status',$response))) throw new RuntimeException('Respons posting tidak dapat dipastikan. Periksa status jurnal sebelum mencoba kembali.');
            $ok=(string)($response['save'] ?? $response['status'])==='1';
            $result=['status'=>$ok?1:0,'msg'=>is_scalar($response['msg'] ?? null)?(string)$response['msg']:($ok?'Jurnal berhasil diproses.':'Jurnal gagal diproses.')];
            if ($ok&&$action==='post') {
                if (method_exists($this->ci->db, 'where') && method_exists($this->ci->db, 'update')) {
                    try {
                        $nowJakarta = (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s');
                        $this->ci->db->where('no_transaksi', $header['no_transaksi'])
                                     ->where('jenis_transaksi', $header['jenis_transaksi']);
                        if ($source === 'EXP' && !empty($header['id_company'])) {
                            $this->ci->db->where('id_company', $header['id_company']);
                        }
                        $this->ci->db->where('sts', '1')
                                     ->where('tgl_posting IS NULL', null, false)
                                     ->update('tr_jurnal', [
                                         'tgl_posting' => $nowJakarta,
                                         'posted_by' => (string)$actor['id']
                                     ]);
                    } catch (Throwable $ignore) {}
                }
                try { $model->save_posting_metadata($fresh['header'],$actor,$response['accounting_numbers'] ?? null); $result['metadata_saved']=true; $result['outcome']='posted'; }
                catch (Throwable $error) {
                    log_message('error','Jurnal All: posting berhasil tetapi metadata tidak tersimpan; periksa group '.hash('sha256',$model->key($fresh['header'])));
                    $result['metadata_saved']=false; $result['outcome']='posted_metadata_missing';
                    $result['warning']='Posting accounting berhasil, tetapi metadata tanggal/user/nomor final belum tersimpan. Jangan posting ulang; hubungi administrator untuk pemeriksaan.';
                }
            }
            return $result;
        } finally {
            $_POST=$original;
            while (ob_get_level()>$bufferLevel) ob_end_clean();
            if ($referenceLocked) Payment_reference::release($this->ci->db);
            try {
                if ($this->ci->db->query('SELECT RELEASE_LOCK(?)',[$key])===false) log_message('error','Jurnal All: advisory lock release tidak tersedia.');
            } catch (Throwable $error) { log_message('error','Jurnal All: advisory lock release tidak tersedia.'); }
        }
    }
}
