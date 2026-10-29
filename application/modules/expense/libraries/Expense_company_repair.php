<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__.'/Expense_company.php';

class Expense_company_repair
{
    private $db;
    private $company;
    private $accounting;
    private $audit;

    public function __construct($db, $company, $accounting, $audit = null)
    {
        $this->db = $db;
        $this->company = $company;
        $this->accounting = $accounting;
        $this->audit = $audit;
    }

    public function dryRun($documents = [])
    {
        $query = $this->db->where_in('jenis_transaksi', ['Expense Report','Pengembalian Kasbon']);
        if ($documents) $query->where_in('no_transaksi', $documents);
        $rows = $this->read($query->order_by('id')->get('tr_jurnal'))->result_array();
        $groups = [];
        foreach ($rows as $row) $groups[json_encode([$row['no_transaksi'],$row['jenis_transaksi']])][] = $row;
        $entries = [];
        foreach ($groups as $rows) $entries[] = $this->inspect($rows);
        return ['version'=>1, 'entries'=>$entries];
    }

    public function inspect($rows, $lock = false)
    {
        $first = reset($rows);
        $entry = ['document'=>$first['no_transaksi'], 'type'=>$first['jenis_transaksi'], 'ids'=>array_map('intval', array_column($rows,'id')),
            'before'=>array_map(function($r){return array_intersect_key($r,array_flip(['id','id_company','nm_company','sts','no_jurnal','tgl_jurnal','coa','debit','kredit']));},$rows),
            'eligible'=>false, 'reason'=>'', 'company'=>null];
        $dependencies = [];
        try {
            if ($entry['type'] !== 'Pengembalian Kasbon') {
                $entry['company'] = $this->company->validateJournal($rows);
                throw new DomainException('Company realisasi sesuai sumber; hanya diaudit, tidak dikoreksi otomatis.');
            }
            foreach ($rows as $row) {
                if (!in_array($row['sts'], [null,'','0',0], true)) throw new DomainException('Kelompok sudah/parsial posted; koreksi otomatis ditolak.');
                if (trim((string)$row['id_company']) !== '' || trim((string)$row['nm_company']) !== '') throw new DomainException('Company sudah terisi atau kelompok tidak sepenuhnya kosong; perlu pemeriksaan terpisah.');
            }
            if (count(array_unique(array_column($rows,'no_jurnal'))) !== 1 || count(array_unique(array_column($rows,'tgl_jurnal'))) !== 1 || count($rows) !== 2) {
                throw new DomainException('Struktur jurnal pengembalian tidak tunggal/utuh.');
            }
            $debit = $credit = 0;
            foreach ($rows as $row) {
                $d = (int)round((float)$row['debit']*100); $c = (int)round((float)$row['kredit']*100);
                if ($d < 0 || $c < 0 || ($d > 0 && $c > 0)) throw new DomainException('Nominal jurnal tidak valid.');
                $debit += $d; $credit += $c;
            }
            if ($debit <= 0 || $debit !== $credit) throw new DomainException('Jurnal pengembalian tidak balance.');
            if ($lock) $this->company->lockSource($entry['document']);
            $entry['company'] = $this->company->resolveReturn($entry['document']);
            $realization = $this->company->validateReturnRealization($entry['document'], $entry['company']);
            $dependencies['realization'] = $realization;
            $candidates = array_map(function($row)use($entry){return array_merge($row,['id_company'=>$entry['company']['id'],'nm_company'=>$entry['company']['name']]);},$rows);
            $this->company->validateJournal($candidates);
            $expense = $this->read($this->db->get_where('tr_expense',['no_doc'=>$entry['document']]))->row_array();
            $amount = (int)round((float)(($expense['transfer_jumlah'] ?? 0) > 0 ? $expense['transfer_jumlah'] : ($expense['lebih_bayar'] ?? 0))*100);
            if ((string)($expense['status'] ?? '') !== '2' || $amount !== $debit || (string)($expense['transfer_tanggal'] ?? '') !== (string)$first['tgl_jurnal']) throw new DomainException('Status/nominal/tanggal pengembalian berbeda dari sumber terverifikasi.');
            if (!$this->db->table_exists('tr_jurnal_postings')) throw new DomainException('Metadata posting belum tersedia untuk verifikasi histori.');
            $metadata = $this->read($this->db->get_where('tr_jurnal_postings', ['no_transaksi'=>$entry['document'], 'jenis_transaksi'=>$entry['type']]))->result_array();
            $dependencies['metadata'] = $metadata;
            if ($metadata) throw new DomainException('Metadata menunjukkan posting pernah dilakukan; koreksi otomatis ditolak.');
            $refs = [$entry['document']];
            foreach ($rows as $row) if (preg_match('/\{REF:(.*?)\}/', (string)$row['keterangan'], $match)) $refs[] = $match[1];
            foreach (['accounting_vuca','accounting_sustain','accounting_stm'] as $target) {
                if (!isset($this->accounting[$target])) throw new RuntimeException('Koneksi accounting untuk audit tidak lengkap.');
                $conn = $this->accounting[$target];
                $posted = $this->read($conn->group_start()->where_in('no_reff', array_unique($refs))->or_like('keterangan', $entry['document'])->group_end()->get('jurnal'))->result_array();
                foreach ($posted as $line) if (empty($line['nomor']) || !$this->matchesPostedRealization($conn,$line['nomor'],$realization)) {
                    throw new DomainException('Ditemukan bukti accounting selain realisasi terverifikasi di '.$target.'; koreksi otomatis ditolak.');
                }
                $headers = $this->read($conn->like('keterangan', $entry['document'])->get('javh'))->result_array();
                foreach ($headers as $header) if (empty($header['nomor']) || !$this->matchesPostedRealization($conn,$header['nomor'],$realization)) {
                    throw new DomainException('Ditemukan header accounting selain realisasi terverifikasi di '.$target.'; koreksi otomatis ditolak.');
                }
            }
            $entry['eligible'] = true;
            $entry['reason'] = 'Sumber tunggal terverifikasi; jurnal pending tanpa indikasi posting.';
        } catch (DomainException $error) {
            $entry['reason'] = $error->getMessage();
        } catch (Throwable $error) {
            $entry['reason'] = 'Audit dependensi belum dapat dipastikan; koreksi diblokir.';
        }
        $entry['snapshot'] = hash('sha256', json_encode([$rows,$entry['company'],$dependencies], JSON_THROW_ON_ERROR));
        return $entry;
    }

    public function apply($entry, $actor)
    {
        if (!is_callable($this->audit) || !is_string($actor) || !preg_match('/^[A-Za-z0-9_.@-]{1,100}$/D', $actor)) throw new DomainException('Identitas pelaksana dan penulis audit wajib tersedia.');
        if (empty($entry['eligible']) || ($entry['type'] ?? '') !== 'Pengembalian Kasbon') throw new DomainException('Manifest tidak memenuhi syarat koreksi.');
        $locks = [];
        $transaction = false;
        try {
            $names = ['expense_journal_build'];
            foreach (['1','3','7'] as $id) $names[] = 'jurnal_expense:'.Expense_company::schema($id).':101';
            foreach ($names as $name) {
                $lock = $this->read($this->db->query('SELECT GET_LOCK(?, 10) AS acquired', [$name]))->row();
                if (!$lock || (int)$lock->acquired !== 1) throw new RuntimeException('Jurnal sedang diproses. Ulangi audit sebelum koreksi.');
                $locks[] = $name;
            }
            if ($this->db->trans_begin() === false) throw new RuntimeException('Gagal memulai transaksi koreksi.');
            $transaction = true;
            $rows = $this->read($this->db->query('SELECT * FROM tr_jurnal WHERE no_transaksi = ? AND jenis_transaksi = ? ORDER BY id FOR UPDATE', [$entry['document'],$entry['type']]))->result_array();
            if (!$rows) throw new DomainException('Kelompok jurnal tidak tersedia.');
            $this->read($this->db->query('SELECT * FROM tr_jurnal WHERE no_transaksi = ? ORDER BY id FOR UPDATE', [$entry['document']]));
            $fresh = $this->inspect($rows, true);
            if (!$fresh['eligible'] || $fresh !== $entry) throw new DomainException('Snapshot/sumber/status berubah; jalankan dry-run kembali.');
            $event = ['event'=>'expense_company_repair', 'actor'=>$actor, 'document'=>$entry['document'], 'ids'=>$entry['ids'],
                'before'=>['id_company'=>'','nm_company'=>''], 'after'=>['id_company'=>$fresh['company']['id'],'nm_company'=>$fresh['company']['name']], 'snapshot'=>$entry['snapshot']];
            if (call_user_func($this->audit, $event + ['outcome'=>'intent']) === false) throw new RuntimeException('Gagal menulis audit intent koreksi.');
            if ($this->db->where_in('id',$entry['ids'])->update('tr_jurnal',$event['after']) === false || $this->db->affected_rows() !== count($entry['ids'])) {
                throw new RuntimeException('Jumlah baris koreksi tidak sesuai snapshot.');
            }
            if ($this->db->trans_status() === false || $this->db->trans_commit() === false) throw new RuntimeException('Gagal commit koreksi company.');
            $transaction = false;
            $result = ['status'=>'applied', 'document'=>$entry['document'], 'ids'=>$entry['ids']];
            try {
                if (call_user_func($this->audit, $event + ['outcome'=>'committed']) === false) throw new RuntimeException('audit');
            } catch (Throwable $error) {
                $result['audit_warning'] = 'Koreksi sudah commit; audit final gagal. Periksa database dan intent sebelum tindakan berikutnya.';
            }
            return $result;
        } finally {
            try { if ($transaction) $this->db->trans_rollback(); }
            finally {
                foreach (array_reverse($locks) as $name) {
                    try { $this->db->query('SELECT RELEASE_LOCK(?)', [$name]); }
                    catch (Throwable $ignored) { log_message('error', 'Expense company repair: gagal melepas lock.'); }
                }
            }
        }
    }

    private function read($query)
    {
        if ($query === false) throw new RuntimeException('Gagal membaca bukti koreksi company.');
        return $query;
    }

    private function matchesPostedRealization($conn, $number, $realization)
    {
        $expected = [];
        foreach ($realization as $row) {
            if ((string)($row['sts'] ?? '') !== '1') return false;
            if ((float)$row['debit'] == 0 && (float)$row['kredit'] == 0) continue;
            $reference = $row['no_transaksi']; $description = $row['keterangan'];
            if (preg_match('/\{REF:(.*?)\}/',(string)$description,$match)) { $reference=$match[1]; $description=trim(str_replace($match[0],'',$description)); }
            $expected[] = json_encode([(string)$row['tgl_jurnal'],(string)$row['coa'],(string)$reference,(int)round((float)$row['debit']*100),(int)round((float)$row['kredit']*100),(string)$description]);
        }
        $actual = [];
        foreach ($this->read($conn->get_where('jurnal',['nomor'=>$number]))->result_array() as $line) {
            foreach (['tanggal','no_perkiraan','no_reff','debet','kredit','keterangan'] as $field) if (!array_key_exists($field,$line)) return false;
            $actual[] = json_encode([(string)$line['tanggal'],(string)$line['no_perkiraan'],(string)$line['no_reff'],(int)round((float)$line['debet']*100),(int)round((float)$line['kredit']*100),(string)$line['keterangan']]);
        }
        sort($expected,SORT_STRING); sort($actual,SORT_STRING);
        return $expected && $expected === $actual;
    }
}
