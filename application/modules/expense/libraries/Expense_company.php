<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Expense_company
{
    private $db;
    private $consultant;

    public function __construct($db, $consultant)
    {
        $this->db = $db;
        $this->consultant = $consultant;
    }

    public static function target($id)
    {
        $targets = ['1'=>'accounting_vuca', '4'=>'accounting_vuca', '3'=>'accounting_sustain', '6'=>'accounting_sustain', '7'=>'accounting_stm'];
        if (!is_scalar($id) || !isset($targets[(string)$id])) {
            throw new DomainException('Company jurnal belum tersedia atau tidak didukung. Perbaiki sumber company sebelum melanjutkan.');
        }
        return $targets[(string)$id];
    }

    public static function schema($id)
    {
        $constants = ['accounting_vuca'=>'DBACC_VUCA', 'accounting_sustain'=>'DBACC_SUST', 'accounting_stm'=>'DBACC_STM'];
        $constant = $constants[self::target($id)];
        if (!defined($constant) || !preg_match('/^[a-zA-Z0-9_]+$/D', constant($constant))) {
            throw new RuntimeException('Konfigurasi accounting tidak valid.');
        }
        return constant($constant);
    }

    public static function storedCompany($rows)
    {
        if (!$rows) throw new DomainException('Detail jurnal tidak tersedia.');
        $first = (array)reset($rows);
        $id = $first['id_company'] ?? '';
        $target = self::target($id);
        $name = trim((string)($first['nm_company'] ?? ''));
        if ($name === '') throw new DomainException('Nama company jurnal belum tersedia.');
        foreach ($rows as $row) {
            $row = (array)$row;
            if ((string)($row['id_company'] ?? '') !== (string)$id || trim((string)($row['nm_company'] ?? '')) !== $name) {
                throw new DomainException('Company antarbaris jurnal tidak konsisten.');
            }
        }
        return ['id'=>(string)$id, 'name'=>$name, 'target'=>$target];
    }

    public function resolve($number)
    {
        $context = $this->sourceRows($number);
        $company = $this->resolveKasbons($context['references']);
        $company['source_snapshot'] = hash('sha256', json_encode([$context,$company['evidence']], JSON_THROW_ON_ERROR));
        return $company;
    }

    public static function bankCompanyId($bank)
    {
        $bank = (array)$bank;
        $owners = ['PT. SENTRAL TEHNOLOGI MANAGEMEN'=>'7', 'PT. VUCA STRATEGI BISNIS'=>'4', 'PT. SENTRAL SUSTAINABILITY CONSULTING'=>'3'];
        $owner = preg_replace('/\s+/', ' ', strtoupper(trim((string)($bank['nama'] ?? ''))));
        if (!isset($owners[$owner])) throw new DomainException('Pemilik rekening bank belum memiliki mapping company terverifikasi.');
        return $owners[$owner];
    }

    public static function selectedReturnBank($expense, $banks)
    {
        $expense = (array)$expense;
        $id = trim((string)($expense['id_bank_pengembalian'] ?? ''));
        $matches = [];
        foreach ($banks as $bank) {
            $bank = (array)$bank;
            if ($id !== '' ? (string)$bank['id'] === $id : (!empty($expense['transfer_coa_bank']) && (string)$bank['coa_bank'] === (string)$expense['transfer_coa_bank'])) $matches[] = (string)$bank['id'];
        }
        return count($matches) === 1 ? $matches[0] : '';
    }

    public function resolveReturn($number)
    {
        $context = $this->sourceRows($number);
        $expense = $context['headers'][0];
        $id = trim((string)($expense['id_bank_pengembalian'] ?? ''));
        $coa = trim((string)($expense['transfer_coa_bank'] ?? ''));
        if ($id === '' && $coa === '') throw new DomainException('Bank pengembalian tersimpan belum tersedia.');
        $banks = $this->read($this->db->get_where('ms_bank', $id !== '' ? ['id'=>$id,'deleted'=>'0'] : ['coa_bank'=>$coa,'deleted'=>'0']))->result_array();
        if (count($banks) !== 1) throw new DomainException('Bank pengembalian tersimpan tidak tersedia atau COA bank ambigu.');
        $bank = $banks[0];
        if (trim((string)$bank['rekening']) === '' || trim((string)$bank['coa_bank']) === '' || ($coa !== '' && $coa !== (string)$bank['coa_bank'])) {
            throw new DomainException('Rekening/COA bank pengembalian tidak sesuai data tersimpan.');
        }
        $company = $this->master(self::bankCompanyId($bank));
        $known = []; $kasbons = [];
        foreach (array_unique($context['references']) as $reference) {
            $rows = $this->read($this->db->get_where('tr_kasbon', ['no_doc'=>trim($reference)]))->result_array();
            if (count($rows) !== 1) throw new DomainException('Kasbon sumber pengembalian tidak tersedia atau tidak unik.');
            $kasbons[] = $rows[0];
            if (trim((string)($rows[0]['project'] ?? '')) !== '') $known[] = trim($reference);
        }
        if (!$kasbons) throw new DomainException('Relasi kasbon pengembalian belum tersedia.');
        $project = $known ? $this->resolveKasbons($known) : null;
        if ($project && $project['target'] !== $company['target']) throw new DomainException('Company project dan bank pengembalian berbeda entitas; perlu pemeriksaan antarcompany.');
        $company['source_kind'] = 'return_bank';
        $company['bank_id'] = (string)$bank['id'];
        $company['bank_coa'] = (string)$bank['coa_bank'];
        $company['project_proven'] = $project !== null;
        $company['evidence'] = [['type'=>'return_bank','bank_id'=>(string)$bank['id'],'company'=>$company['id'],'fingerprint'=>hash('sha256',json_encode($bank,JSON_THROW_ON_ERROR))]];
        $company['source_snapshot'] = hash('sha256', json_encode([$context,$kasbons,$project,$bank,$company], JSON_THROW_ON_ERROR));
        return $company;
    }

    public function validateReturnRealization($number, $company)
    {
        $rows = $this->read($this->db->get_where('tr_jurnal', ['no_transaksi'=>$number,'jenis_transaksi'=>'Expense Report']))->result_array();
        if (!$rows) throw new DomainException('Jurnal realisasi sumber pengembalian belum tersedia.');
        if ($company['project_proven'] && self::storedCompany($rows)['target'] !== $company['target']) throw new DomainException('Company realisasi tidak sesuai entitas project dan bank.');
        return $rows;
    }

    private function sourceRows($number)
    {
        $headers = $this->read($this->db->get_where('tr_expense', ['no_doc'=>$number]))->result_array();
        if (count($headers) !== 1) throw new DomainException('Sumber expense '.$number.' tidak tersedia atau tidak unik.');
        $references = $this->references($headers[0]['id_kasbon'] ?? '');
        $details = $this->read($this->db->get_where('tr_expense_detail', ['no_doc'=>$number]))->result_array();
        foreach ($details as $line) {
            foreach (['id_kasbon','kasbon_pr_non_po_pett'] as $field) {
                $references = array_merge($references, $this->references($line[$field] ?? ''));
            }
        }
        $reverse = $this->read($this->db->get_where('tr_kasbon', ['id_expense_pett_pr_non_po'=>$number]))->result_array();
        foreach ($reverse as $kasbon) {
            $references[] = $kasbon['no_doc'];
        }
        return ['headers'=>$headers,'details'=>$details,'reverse'=>$reverse,'references'=>array_values(array_unique(array_filter(array_map('trim',$references),'strlen')))];
    }

    public function resolveKasbons($references)
    {
        if (!is_array($references)) throw new DomainException('Referensi kasbon tidak valid.');
        foreach ($references as $reference) if (!is_scalar($reference)) throw new DomainException('Referensi kasbon tidak valid.');
        $references = array_values(array_unique(array_filter(array_map('trim', $references), 'strlen')));
        sort($references, SORT_STRING);
        if (!$references) throw new DomainException('Relasi kasbon/project untuk menentukan company belum tersedia.');
        $company = null;
        $evidence = [];
        foreach ($references as $reference) {
            $kasbons = $this->read($this->db->get_where('tr_kasbon', ['no_doc'=>$reference]))->result_array();
            if (count($kasbons) !== 1 || trim((string)($kasbons[0]['project'] ?? '')) === '') {
                throw new DomainException('Sumber project kasbon '.$reference.' belum tersedia atau tidak unik.');
            }
            $project = (string)$kasbons[0]['project'];
            $quotations = $this->read($this->consultant->get_where('kons_tr_penawaran', ['id_quotation'=>$project]))->result_array();
            if (count($quotations) !== 1) throw new DomainException('Company project '.$project.' tidak tersedia atau ambigu.');
            $resolved = $this->master($quotations[0]['company'] ?? '');
            if ($company !== null && $company['id'] !== $resolved['id']) {
                throw new DomainException('Kasbon pada expense mengarah ke beberapa company. Perbaiki sumber sebelum melanjutkan.');
            }
            $company = $resolved;
            $evidence[] = ['kasbon'=>$reference, 'project'=>$project, 'company'=>$company['id'], 'fingerprint'=>hash('sha256', json_encode([$kasbons,$quotations,$company], JSON_THROW_ON_ERROR))];
        }
        $company['evidence'] = $evidence;
        return $company;
    }

    public function lockSource($number)
    {
        $schema = $this->consultant->database ?? '';
        if ($schema !== '' && !preg_match('/^[a-zA-Z0-9_]+$/D', $schema)) throw new RuntimeException('Schema sumber company tidak valid.');
        if (($this->db->dbdriver ?? '') === 'mysqli') {
            $engines = $this->read($this->db->query("SELECT ENGINE FROM information_schema.TABLES WHERE (TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('tr_expense','tr_expense_detail','tr_kasbon','tr_jurnal','ms_bank')) OR (TABLE_SCHEMA=? AND TABLE_NAME IN ('kons_tr_penawaran','kons_tr_company'))", [$schema]))->result_array();
            if (count($engines) !== 7) throw new DomainException('Schema sumber company belum lengkap.');
            foreach ($engines as $table) if (strtoupper($table['ENGINE']) !== 'INNODB') throw new DomainException('Sumber company dan jurnal wajib memakai InnoDB untuk transaksi atomik.');
        }
        $headers = $this->read($this->db->query('SELECT * FROM tr_expense WHERE no_doc = ? FOR UPDATE', [$number]))->result_array();
        $details = $this->read($this->db->query('SELECT * FROM tr_expense_detail WHERE no_doc = ? FOR UPDATE', [$number]))->result_array();
        $reverse = $this->read($this->db->query('SELECT * FROM tr_kasbon WHERE id_expense_pett_pr_non_po = ? FOR UPDATE', [$number]))->result_array();
        $references = [];
        foreach (array_merge($headers,$details) as $row) foreach (['id_kasbon','kasbon_pr_non_po_pett'] as $field) {
            $references = array_merge($references, $this->references($row[$field] ?? ''));
        }
        foreach ($reverse as $row) $references[] = $row['no_doc'];
        $prefix = $schema !== '' ? $schema.'.' : '';
        foreach ($headers as $header) {
            $bankRows = [];
            if (!empty($header['id_bank_pengembalian'])) $bankRows = $this->read($this->db->query('SELECT * FROM ms_bank WHERE id = ? FOR UPDATE', [$header['id_bank_pengembalian']]))->result_array();
            elseif (!empty($header['transfer_coa_bank'])) $bankRows = $this->read($this->db->query('SELECT * FROM ms_bank WHERE coa_bank = ? FOR UPDATE', [$header['transfer_coa_bank']]))->result_array();
            foreach ($bankRows as $bank) $this->read($this->db->query('SELECT * FROM '.$prefix.'kons_tr_company WHERE id = ? FOR UPDATE', [self::bankCompanyId($bank)]));
        }
        foreach (array_unique($references) as $reference) {
            $kasbons = $this->read($this->db->query('SELECT * FROM tr_kasbon WHERE no_doc = ? FOR UPDATE', [trim($reference)]))->result_array();
            foreach ($kasbons as $kasbon) {
                $quotations = $this->read($this->db->query('SELECT * FROM '.$prefix.'kons_tr_penawaran WHERE id_quotation = ? FOR UPDATE', [$kasbon['project'] ?? '']))->result_array();
                foreach ($quotations as $quotation) $this->read($this->db->query('SELECT * FROM '.$prefix.'kons_tr_company WHERE id = ? FOR UPDATE', [$quotation['company'] ?? '']));
            }
        }
    }

    public function validateJournal($rows, $lock = false)
    {
        $stored = self::storedCompany($rows);
        $master = $this->master($stored['id']);
        if ($master['name'] !== $stored['name']) throw new DomainException('Nama company jurnal tidak sesuai master company.');
        $first = (array)reset($rows);
        if (in_array($first['jenis_transaksi'], ['Expense Report','Pengembalian Kasbon'], true)) {
            if ($lock) $this->lockSource($first['no_transaksi']);
            $source = $first['jenis_transaksi'] === 'Pengembalian Kasbon' ? $this->resolveReturn($first['no_transaksi']) : $this->resolve($first['no_transaksi']);
            if ($source['id'] !== $stored['id']) throw new DomainException('Company jurnal berbeda dengan company sumber transaksi.');
            if ($first['jenis_transaksi'] === 'Pengembalian Kasbon') {
                $this->validateReturnRealization($first['no_transaksi'], $source);
                foreach ($rows as $line) {
                    $line = (array)$line;
                    if (((float)$line['debit'] > 0 && $line['coa'] !== $source['bank_coa']) || ((float)$line['kredit'] > 0 && $line['coa'] !== '1103-01-15')) throw new DomainException('COA jurnal pengembalian tidak sesuai bank tersimpan/piutang kasbon.');
                }
            }
            return $source;
        }
        return $master;
    }

    private function master($id)
    {
        $target = self::target($id);
        $rows = $this->read($this->consultant->get_where('kons_tr_company', ['id'=>(string)$id]))->result_array();
        if (count($rows) !== 1 || trim((string)$rows[0]['nm_company']) === '') throw new DomainException('Master company tidak tersedia atau tidak unik.');
        return ['id'=>(string)$id, 'name'=>trim((string)$rows[0]['nm_company']), 'target'=>$target];
    }

    private function references($value)
    {
        return preg_split('/[,;\r\n]+/', trim((string)$value), -1, PREG_SPLIT_NO_EMPTY);
    }

    private function read($query)
    {
        if ($query === false) throw new RuntimeException('Validasi sumber company tidak dapat dilakukan.');
        return $query;
    }
}
