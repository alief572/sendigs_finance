<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('company_override')) {
    /**
     * Pengecualian company per dokumen: biayanya dibebankan ke company lain dari departemen pemohon.
     * Nilai = id kons_tr_company ('4' Vuca). Jangan dihapus selama dokumennya masih tampil di
     * daftar/cetakan, karena company di sana dihitung ulang setiap kali dibuka.
     *
     * @return array|null ['id' => string, 'nama' => string]
     */
    function company_override($no_doc)
    {
        $overrides = [
            'RQ-2026-00225' => ['id' => '4', 'nama' => 'Vuca'],
        ];

        return $overrides[(string) $no_doc] ?? null;
    }
}
