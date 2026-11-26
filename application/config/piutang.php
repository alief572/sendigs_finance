<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Tanggal go-live Follow Up Piutang (YYYY-MM-DD). Invoice dengan tanggal invoice >= tanggal ini selalu tampil
// di Follow Up sampai di-close. Kosong = invoice lunas yang belum pernah dikirim disembunyikan (MP-FR-08).
$config['tanggal_mulai_follow_up'] = '2026-11-01';

// Batas daftar Monitoring Piutang / Follow Up Piutang / Dashboard Plan Bayar (YYYY-MM-DD): invoice dengan tanggal invoice
// sebelum tanggal ini tidak ditampilkan, kecuali 20 invoice Piutang_aging::FOLLOW_UP_INVOICES. Dibaca Piutang_aging::tanggal_mulai().
// Sengaja 2026-10-01 (keputusan user 25-11-2026): 34 invoice Oktober 2026 sudah berjalan di Follow Up dan tidak boleh hilang.
$config['tanggal_mulai_daftar'] = '2026-10-01';
