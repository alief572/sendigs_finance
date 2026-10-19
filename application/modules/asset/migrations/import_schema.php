<?php
/** CLI-only additive migration. Pass an explicit database; duplicate codes are never corrected. */
function migrate_asset_import(mysqli $db)
{
    $duplicates=$db->query('SELECT kd_asset,COUNT(*) AS n FROM asset GROUP BY kd_asset HAVING COUNT(*)>1 LIMIT 1');
    if (!$duplicates) { throw new RuntimeException('Tidak dapat memeriksa kode asset.'); }
    if ($duplicates->num_rows) { throw new RuntimeException('Migration dihentikan: ditemukan kode asset duplikat. Rekonsiliasi manual diperlukan.'); }
    $engines=$db->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('asset','asset_generate')");
    if (!$engines || $engines->num_rows!==2) { throw new RuntimeException('Tabel asset/asset_generate tidak lengkap.'); }
    while ($row=$engines->fetch_assoc()) { if (strtoupper($row['ENGINE'])!=='INNODB') { throw new RuntimeException('Tabel asset dan jadwal wajib InnoDB.'); } }
    $indexes=$db->query('SHOW INDEX FROM asset'); $keys=[];
    while ($index=$indexes->fetch_assoc()) { if (!$index['Non_unique']) { $keys[$index['Key_name']][]=$index['Column_name']; } }
    if (!in_array(['kd_asset'],array_values($keys),true) && !$db->query('ALTER TABLE asset ADD UNIQUE INDEX uq_asset_kd_asset (kd_asset)')) { throw new RuntimeException('Unique index gagal. Periksa duplikasi/akses DDL: '.$db->error); }
    if (!$db->query("CREATE TABLE IF NOT EXISTS asset_import_batches (
        token CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
        user_id VARCHAR(100) NOT NULL, kdcab CHAR(3) NOT NULL,
        status ENUM('preview','complete') NOT NULL DEFAULT 'preview',
        data_json MEDIUMTEXT NULL, result_json TEXT NULL,
        created_at DATETIME NOT NULL, expires_at DATETIME NOT NULL,
        INDEX ix_asset_import_expiry (status,expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")) { throw new RuntimeException('Tabel batch gagal dibuat: '.$db->error); }
}

if (PHP_SAPI==='cli' && realpath($_SERVER['SCRIPT_FILENAME'])===__FILE__) {
    if (($argv[1] ?? '')!=='--apply' || !isset($argv[2])) { fwrite(STDERR,"Usage: php import_schema.php --apply DATABASE\n"); exit(1); }
    define('BASEPATH',dirname(__DIR__,4).'/system/'); define('ENVIRONMENT',getenv('CI_ENV') ?: 'development');
    if (!preg_match('/^[a-zA-Z0-9_-]+$/D',ENVIRONMENT)) { fwrite(STDERR,"CI_ENV tidak valid.\n"); exit(1); }
    $configRoot=dirname(__DIR__,3).'/config/';
    $configLoaded=false;
    if (is_file($configRoot.'database.php')) { require $configRoot.'database.php'; $configLoaded=true; }
    if (is_file($configRoot.ENVIRONMENT.'/database.php')) { require $configRoot.ENVIRONMENT.'/database.php'; $configLoaded=true; }
    if (!$configLoaded || empty($db['default'])) { fwrite(STDERR,"Config database untuk environment ini tidak ditemukan. Set CI_ENV sesuai konfigurasi aplikasi.\n"); exit(1); }
    $cfg=$db['default'];
    $conn=new mysqli($cfg['hostname'],$cfg['username'],$cfg['password'],$argv[2],isset($cfg['port']) ? (int)$cfg['port'] : 3306);
    try { migrate_asset_import($conn); echo "Migration asset import berhasil (".$argv[2].").\n"; }
    catch (Throwable $e) { fwrite(STDERR,$e->getMessage()."\n"); exit(1); }
}
