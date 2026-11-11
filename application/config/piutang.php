<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Tanggal go-live Follow Up Piutang (YYYY-MM-DD). Invoice dengan tanggal invoice >= tanggal ini selalu tampil
// di Follow Up sampai di-close. Kosong = invoice lunas yang belum pernah dikirim disembunyikan (MP-FR-08).
$config['tanggal_mulai_follow_up'] = '2019-01-01';
