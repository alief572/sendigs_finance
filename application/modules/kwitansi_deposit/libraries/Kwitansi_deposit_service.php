<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once dirname(__DIR__, 2) . '/invoicing/libraries/Invoice_consulting_service.php';
require_once __DIR__ . '/Kwitansi_company.php';

class Kwitansi_deposit_service
{
    private $s;
    public function __construct($ci) { $this->s = new Invoice_consulting_service($ci); }

    public function create($post, $model)
    {
        $s = $this->s;
        return $s->mutate('create_kwitansi_deposit', function () use ($s, $post, $model) {
            if (!has_permission('Kwitansi_Deposit.Add')) throw new RuntimeException('Tidak memiliki izin pembuatan kwitansi.');
            $s->transactional(array('tr_kwitansi_deposit', 'tr_customer_deposit', 'tr_invoicing', 'tr_jurnal'));
            foreach (array('id_spk_penawaran', 'spk_snapshot') as $field) {
                if (!$s->db->field_exists($field, 'tr_kwitansi_deposit')) throw new RuntimeException('Terapkan migration 003_deposit_contract.');
            }
            $spkId = $s->required($post, 'id_spk_penawaran');
            $spk = $s->one('SELECT * FROM ' . DBCNL . '.kons_tr_spk_penawaran WHERE id_spk_penawaran=? FOR UPDATE', array($spkId));
            if (!$spk || (string)$spk['id_customer'] !== $s->required($post, 'id_customer')) throw new InvalidArgumentException('SPK tidak sesuai customer.');
            if (trim((string)$spk['nm_customer']) === '') throw new RuntimeException('Nama customer SPK belum lengkap.');
            $date = $s->required($post, 'tanggal_kwitansi', 10);
            $parsed = DateTime::createFromFormat('!Y-m-d', $date);
            if (!$parsed || $parsed->format('Y-m-d') !== $date) throw new InvalidArgumentException('Tanggal kwitansi tidak valid.');
            $companySource = Kwitansi_company::resolve($s->db, $spk, true);
            $mapping = $s->mapping($companySource['company']['id'], true);
            $depositAccounts = array_values(array_filter($s->master($mapping['owner']['id']), function ($account) { return $account['no_perkiraan'] === '2102-01-02'; }));
            if (count($depositAccounts) !== 1) throw new RuntimeException('COA deposit tidak tersedia atau ambigu pada company tujuan.');
            $accounts = $mapping['accounts'];
            $accounts['deposit'] = array('code'=>'2102-01-02', 'name'=>$depositAccounts[0]['nama']);
            $amount = Penerimaan_calculation::cents($s->required($post, 'nilai_kwitansi', 32));
            if ($amount <= 0) throw new InvalidArgumentException('Nilai kwitansi harus lebih besar dari 0.');
            $trusted = array('id_customer'=>$spk['id_customer'], 'nm_customer'=>$spk['nm_customer'],
                'id_company'=>$mapping['owner']['id'], 'nm_company'=>$mapping['owner']['nm_company'],
                'id_spk_penawaran'=>$spkId, 'spk_snapshot'=>json_encode(array('spk'=>$spk, 'quote'=>$companySource['quote'], 'source_company'=>$companySource['company'], 'mapping'=>$mapping)),
                'no_spk'=>!empty($spk['no_spk'])?$spk['no_spk']:$spkId, 'accounts'=>$accounts,
                'tanggal_kwitansi'=>$date, 'nilai_kwitansi'=>Penerimaan_calculation::decimal($amount),
                'po_customer'=>!empty($spk['no_po'])?$spk['no_po']:'', 'keterangan'=>isset($post['keterangan'])?(string)$post['keterangan']:'',
                'user_id'=>$s->ci->auth->user_id());
            return $model->save_kwitansi($trusted, true);
        }, 'kwitansi_deposit');
    }
}
