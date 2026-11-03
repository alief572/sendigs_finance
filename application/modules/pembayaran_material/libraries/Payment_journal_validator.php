<?php
class Invalid_payment_journal extends DomainException {}

class Payment_journal_validator
{
    public static function validate($rows)
    {
        if (!is_array($rows) || !$rows) throw new Invalid_payment_journal('Jurnal pembayaran kosong.');
        $groups=[];
        foreach ($rows as $row) {
            if (!is_array($row)) throw new Invalid_payment_journal('Baris jurnal pembayaran tidak valid.');
            foreach (['no_transaksi','jenis_transaksi'] as $field) {
                if (!isset($row[$field]) || !is_scalar($row[$field]) || trim((string)$row[$field])==='') throw new Invalid_payment_journal('Identitas kelompok jurnal kosong.');
            }
            $label=trim((string)$row['jenis_transaksi']).' / '.trim((string)$row['no_transaksi']);
            $key=serialize([(string)$row['no_transaksi'],(string)$row['jenis_transaksi']]);
            $debit=self::cents($row['debit'] ?? null,$label);
            $credit=self::cents($row['kredit'] ?? null,$label);
            if (($debit>0 || $credit>0) && (!isset($row['coa']) || !is_scalar($row['coa']) || trim((string)$row['coa'])==='')) throw new Invalid_payment_journal('COA kosong pada jurnal '.$label.'.');
            if (!isset($groups[$key])) $groups[$key]=['label'=>$label,'debit'=>0,'credit'=>0];
            foreach (['debit'=>$debit,'credit'=>$credit] as $side=>$amount) {
                if ($amount>PHP_INT_MAX-$groups[$key][$side]) throw new Invalid_payment_journal('Total jurnal melampaui batas aman: '.$label.'.');
                $groups[$key][$side]+=$amount;
            }
        }
        foreach ($groups as $group) {
            if ($group['debit']===0 || $group['debit']!==$group['credit']) throw new Invalid_payment_journal(
                'Jurnal '.$group['label'].' tidak balance atau nominal kosong: debit '.self::decimal($group['debit']).
                ', kredit '.self::decimal($group['credit']).', selisih '.self::decimal($group['debit']-$group['credit']).'.');
        }
    }

    public static function cents($value,$label='rekonsiliasi')
    {
        if (!is_scalar($value) || is_bool($value) || !preg_match('/^(\d+)(?:\.(\d{1,2}))?$/D',trim((string)$value),$m)) throw new Invalid_payment_journal('Nominal jurnal tidak valid: '.$label.'.');
        $digits=ltrim($m[1].str_pad($m[2] ?? '',2,'0'),'0') ?: '0';
        $limit=(string)PHP_INT_MAX;
        if (strlen($digits)>strlen($limit) || (strlen($digits)===strlen($limit) && strcmp($digits,$limit)>0)) throw new Invalid_payment_journal('Nominal jurnal melampaui batas aman: '.$label.'.');
        return (int)$digits;
    }

    private static function decimal($amount)
    {
        return ($amount<0?'-':'').intdiv(abs($amount),100).'.'.str_pad((string)(abs($amount)%100),2,'0',STR_PAD_LEFT);
    }
}
