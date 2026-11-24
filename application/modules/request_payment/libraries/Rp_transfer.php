<?php

final class Rp_transfer
{
    private const FILE_TYPES = ['application/pdf', 'image/jpeg', 'image/png', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    private const MAX_FILE = 5242880;

    public static function enabled()
    {
        return getenv('RP_TRANSFER_ENABLED') === '1';
    }

    public static function installed($db)
    {
        return method_exists($db, 'table_exists') && $db->table_exists('tr_rp_transfer_file') && $db->table_exists('tr_rp_transfer');
    }

    public static function requireAjaxPost($input)
    {
        if ($input->method() !== 'post' || !$input->is_ajax_request()) {
            throw new DomainException('Gunakan formulir resmi Request Payment untuk menyimpan perubahan.');
        }
    }

    public static function cents($value, $signed = false)
    {
        if (!is_scalar($value) || is_bool($value) || !preg_match('/^(-?)(\d{1,13})(?:\.(\d{1,2}))?$/D', trim((string) $value), $m) || (!$signed && $m[1] === '-')) {
            throw new DomainException('Nominal pembayaran tidak valid.');
        }
        return ($m[1] === '-' ? -1 : 1) * ((int) $m[2] * 100 + (int) str_pad($m[3] ?? '', 2, '0'));
    }

    public static function money($cents)
    {
        return ($cents < 0 ? '-' : '') . intdiv(abs($cents), 100) . '.' . str_pad((string) (abs($cents) % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function taxes($dpp, $ppn, $pph21, $pph23)
    {
        $dpp = self::cents($dpp);
        $ppn = $ppn ? intdiv($dpp * 11 + 9999, 10000) * 100 : 0;
        $pph = $pph21 ? intdiv($dpp + 3999, 4000) * 100 : ($pph23 ? intdiv($dpp + 4999, 5000) * 100 : 0);
        return ['nilai_ppn' => self::money($ppn), 'nilai_pph' => self::money($pph), 'admin' => 0, 'dibayarkan' => self::money($dpp + $ppn - $pph)];
    }

    public static function totals($net, $admin, $bearer)
    {
        $transfer = $net - ($bearer === 'recipient' ? $admin : 0);
        if ($transfer < 0) {
            throw new DomainException('Admin penerima melebihi nominal transfer.');
        }
        if ($net > 999999999999999 || $transfer + $admin > 999999999999999) {
            throw new DomainException('Total transfer melebihi batas penyimpanan nominal.');
        }
        return ['net' => $net, 'transfer' => $transfer, 'bank' => $transfer + $admin];
    }

    public static function account($input)
    {
        if (!is_array($input)) {
            throw new DomainException('Rekening tujuan belum lengkap.');
        }
        $account = [];
        foreach (['bank' => 100, 'no_rek' => 80, 'nama' => 160] as $key => $limit) {
            $value = $input[$key] ?? null;
            if (!is_string($value) || trim($value) === '' || strlen(trim($value)) > $limit || preg_match('/[\x00-\x1f\x7f]/', $value)) {
                throw new DomainException('Bank, No. Rekening dan Nama Penerima wajib diisi.');
            }
            $account[$key] = trim($value);
        }
        if (!ctype_digit(str_replace(['.', ' ', '-'], '', $account['no_rek']))) {
            throw new DomainException('No. Rekening harus berisi angka.');
        }
        return $account;
    }

    public static function groupType($category)
    {
        $category = str_replace([' ', '_', '-'], '', strtolower(trim((string) $category)));
        if ($category === 'refillpettycash') {
            return 'refill';
        }
        return in_array($category, ['pettycashhutang', 'hutangpettycash'], true) ? 'debt' : 'normal';
    }

    public static function plan($groups, array $source)
    {
        if (!is_array($groups) || count($groups) > count($source)) {
            throw new DomainException('Susunan transfer tidak valid.');
        }
        $plan = [];
        $used = [];
        foreach ($groups as $group) {
            if (!is_array($group) || empty($group['docs']) || !is_array($group['docs']) || !in_array($group['bearer'] ?? '', ['company', 'recipient'], true)) {
                throw new DomainException('Dokumen dan penanggung admin wajib diisi.');
            }
            foreach ($group['docs'] as $doc) {
                if (!is_string($doc) || isset($used[$doc]) || !isset($source[$doc]) || self::groupType($source[$doc]->kategori) === 'debt') {
                    throw new DomainException('Dokumen transfer tidak sesuai dengan pengajuan.');
                }
                if (self::groupType($source[$doc]->kategori) === 'refill' && count($group['docs']) !== 1) {
                    throw new DomainException('Refill Petty Cash harus menggunakan transfer tersendiri.');
                }
                $used[$doc] = true;
            }
            $plan[] = self::account($group) + ['docs' => $group['docs'], 'bearer' => $group['bearer'], 'admin' => self::money(self::cents($group['admin'] ?? ''))];
        }
        foreach ($source as $doc => $row) {
            if (!isset($used[$doc]) && self::groupType($row->kategori) !== 'debt') {
                throw new DomainException('Setiap dokumen wajib masuk dalam satu transfer.');
            }
        }
        return $plan;
    }

    public static function uploadFiles(array $input)
    {
        if (!is_array($input['name'] ?? [])) {
            throw new DomainException('Data lampiran tidak valid.');
        }
        $files = [];
        foreach ($input['name'] ?? [] as $key => $name) {
            $error = $input['error'][$key] ?? UPLOAD_ERR_NO_FILE;
            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $tmp = $input['tmp_name'][$key] ?? '';
            $size = $input['size'][$key] ?? 0;
            if ($error !== UPLOAD_ERR_OK || !is_string($name) || !is_string($tmp) || !is_uploaded_file($tmp) || $size < 1 || $size > self::MAX_FILE || count($files) >= 10) {
                throw new DomainException('Unggah maksimal 10 file, masing-masing maksimal 5 MB.');
            }
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
            $content = file_get_contents($tmp);
            if (!in_array($mime, self::FILE_TYPES, true) || $content === false) {
                throw new DomainException('Gunakan lampiran PDF, JPG, PNG, DOCX atau XLSX.');
            }
            $safeName = preg_replace('/[\x00-\x1f\x7f\/\\\\]/u', '_', $name);
            if (empty($safeName) || strlen($safeName) > 255) {
                throw new DomainException('Nama lampiran tidak valid.');
            }
            $files[$key] = ['nama' => $safeName, 'mime' => $mime, 'content' => $content];
        }
        return $files;
    }

    public static function write($result)
    {
        if ($result === false) {
            throw new RuntimeException('Operasi transfer gagal.');
        }
        return $result;
    }

    public static function rows($db, $batch)
    {
        if (!self::installed($db)) {
            return [];
        }
        return self::write($db->order_by('urutan')->get_where('tr_rp_transfer', ['id_pengajuan' => (int) $batch]))->result_array();
    }

    public static function files($db, $transfer)
    {
        return self::write($db->select('id, id_detail, nama')->order_by('id')->get_where('tr_rp_transfer_file', ['id_transfer' => (int) $transfer]))->result_array();
    }
}
