<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once dirname(__DIR__, 2).'/expense/libraries/Expense_company.php';

class Jurnal_expense_posting_model extends BF_Model
{
    public function post_journal($id, $userId)
    {
        $schema = $number = $lockName = null;
        $headerWritten = $detailsAttempted = $counterWritten = $transaction = false;
        $counter = 0;
        try {
            $rows = $this->Jurnal_expense_model->get_pending_group($id);
            $first = $rows[0];
            $company = $this->Jurnal_expense_model->validate_company($rows);
            $schema = $this->accounting_schema($first->id_company);
            $lockName = 'jurnal_expense:' . $schema . ':101';
            $locked = $this->read($this->db->query('SELECT GET_LOCK(?, 10) AS acquired', [$lockName]))->row();
            if (!$locked || (int) $locked->acquired !== 1) { $lockName = null; throw new RuntimeException('Posting lain sedang berjalan. Silakan coba lagi.'); }
            if ($this->db->trans_begin() === false) { throw new RuntimeException('Gagal memulai transaksi jurnal.'); }
            $transaction = true;
            $rows = $this->Jurnal_expense_model->get_pending_group($id, true);
            $first = $rows[0];
            $freshCompany = $this->Jurnal_expense_model->validate_company($rows, true);
            if ($freshCompany !== $company) { throw new DomainException('Sumber company jurnal berubah. Muat ulang sebelum posting.'); }
            if ($this->accounting_schema($first->id_company) !== $schema) { throw new InvalidArgumentException('Company jurnal berubah. Silakan coba lagi.'); }
            $debit = $credit = 0;
            $details = [];
            $ids = [];
            $checkedCoa = [];
            foreach ($rows as $row) {
                $d = (int) round((float) $row->debit * 100);
                $c = (int) round((float) $row->kredit * 100);
                if ($d < 0 || $c < 0) { throw new InvalidArgumentException('Nominal jurnal tidak valid.'); }
                $debit += $d; $credit += $c; $ids[] = (int) $row->id;
                if ($d === 0 && $c === 0) { continue; }
                if (!isset($checkedCoa[$row->coa])) {
                    if ($this->read($this->db->get_where($schema.'.coa_master', ['no_perkiraan'=>$row->coa]))->num_rows() !== 1) {
                        throw new DomainException('COA '.$row->coa.' tidak tersedia atau tidak unik pada TRAS tujuan.');
                    }
                    $checkedCoa[$row->coa] = true;
                }
                $reference = $row->no_transaksi;
                $description = $row->keterangan;
                if (preg_match('/\{REF:(.*?)\}/', $description, $matches)) {
                    $reference = $matches[1];
                    $description = trim(str_replace($matches[0], '', $description));
                }
                $details[] = ['tipe'=>'JV', 'tanggal'=>$row->tgl_jurnal, 'no_perkiraan'=>$row->coa,
                    'keterangan'=>$description, 'no_reff'=>$reference, 'debet'=>$row->debit, 'kredit'=>$row->kredit, 'stspos'=>1];
            }
            if ($debit === 0 || $debit !== $credit) { throw new InvalidArgumentException('Data jurnal tidak balance untuk diposting.'); }
            $branch = $this->read($this->db->get_where($schema . '.pastibisa_tb_cabang', ['nocab'=>'101']))->row();
            if (!$branch) { throw new RuntimeException('Nomor cabang accounting tidak tersedia.'); }
            $counter = (int) $branch->nomorJC;
            $number = $this->Jurnal_payment_nomor_model->get_Nomor_Jurnal_Sales('101', $first->tgl_jurnal, $first->id_company);
            if (!$number || $this->read($this->db->get_where($schema . '.javh', ['nomor'=>$number]))->num_rows()
                || $this->read($this->db->get_where($schema . '.jurnal', ['nomor'=>$number]))->num_rows()) {
                throw new RuntimeException('Nomor jurnal tujuan sudah terpakai. Hubungi administrator.');
            }
            $header = ['nomor'=>$number, 'tgl'=>$first->tgl_jurnal, 'jml'=>$debit / 100, 'koreksi_no'=>'-', 'kdcab'=>'101',
                'jenis'=>'JV', 'keterangan'=>$first->keterangan, 'bulan'=>substr($first->tgl_jurnal, 5, 2),
                'tahun'=>substr($first->tgl_jurnal, 0, 4), 'user_id'=>$userId, 'memo'=>'', 'tgl_jvkoreksi'=>$first->tgl_jurnal, 'ho_valid'=>''];
            $this->write($this->db->insert($schema . '.javh', $header), 'Gagal menyimpan header jurnal.');
            $headerWritten = true;
            foreach ($details as &$detail) { $detail['nomor'] = $number; }
            unset($detail);
            $detailsAttempted = true;
            $this->write($this->db->insert_batch($schema . '.jurnal', $details), 'Gagal menyimpan detail jurnal.');
            $this->write($this->db->where_in('id', $ids)->update('tr_jurnal', ['sts'=>'1']), 'Gagal memperbarui status jurnal.');
            if ($this->db->affected_rows() !== count($ids)) { throw new RuntimeException('Status jurnal berubah. Silakan coba lagi.'); }
            // Accounting legacy is MyISAM: only restore the counter if nobody else advanced it.
            $this->write($this->db->where(['nocab'=>'101', 'nomorJC'=>$counter])->update($schema . '.pastibisa_tb_cabang', ['nomorJC'=>$counter + 1]), 'Gagal memperbarui nomor cabang.');
            if ($this->db->affected_rows() !== 1) { throw new RuntimeException('Nomor cabang berubah. Silakan coba lagi.'); }
            $counterWritten = true;
            if ($this->db->trans_status() === false || $this->db->trans_commit() === false) { throw new RuntimeException('Gagal commit transaksi jurnal.'); }
            $transaction = false;
            return ['save'=>1, 'msg'=>'SUKSES, simpan data..!!!', 'nomor'=>$number, 'accounting_numbers'=>[['company'=>(string)$first->id_company,'number'=>$number]], 'row_count'=>count($ids), 'cleanup_ok'=>true];
        } catch (Throwable $error) {
            $cleanupOk = true;
            if ($transaction) {
                try { $cleanupOk = $this->db->trans_rollback() !== false; } catch (Throwable $ignored) { $cleanupOk = false; }
            }
            // A transaction cannot undo MyISAM writes. Delete only this request's newly owned JV.
            if ($headerWritten) {
                try {
                    if ($detailsAttempted) { $cleanupOk = $this->db->delete($schema . '.jurnal', ['nomor'=>$number]) !== false && $cleanupOk; }
                    $cleanupOk = $this->db->delete($schema . '.javh', ['nomor'=>$number]) !== false && $cleanupOk;
                    if ($counterWritten && $cleanupOk) {
                        $cleanupOk = $this->db->where(['nocab'=>'101', 'nomorJC'=>$counter + 1])->update($schema . '.pastibisa_tb_cabang', ['nomorJC'=>$counter]) !== false && $cleanupOk;
                    }
                } catch (Throwable $ignored) { $cleanupOk = false; }
            }
            if (!$cleanupOk) { log_message('error', 'Jurnal Expense: cleanup accounting gagal; periksa JV ' . $number); }
            $databaseError = $error instanceof mysqli_sql_exception || $error instanceof PDOException;
            $message = !$databaseError && ($error instanceof DomainException || $error instanceof InvalidArgumentException || $error instanceof RuntimeException) ? $error->getMessage() : 'Gagal memproses jurnal. Silakan coba lagi.';
            return ['save'=>0, 'msg'=>$cleanupOk ? $message : 'Posting gagal dan perlu pemeriksaan accounting oleh administrator.', 'cleanup_ok'=>$cleanupOk];
        } finally {
            if ($lockName !== null) {
                try { $this->db->query('SELECT RELEASE_LOCK(?)', [$lockName]); } catch (Throwable $ignored) { log_message('error', 'Jurnal Expense: gagal melepas lock.'); }
            }
        }
    }

    public function accounting_schema($company)
    {
        return Expense_company::schema($company);
    }
    private function read($query) { if ($query === false) { throw new RuntimeException('Gagal membaca data accounting.'); } return $query; }
    private function write($ok, $message) { if ($ok === false) { throw new RuntimeException($message); } }
}
