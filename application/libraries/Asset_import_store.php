<?php
require_once __DIR__.'/Asset_import.php';

class Asset_import_failure extends RuntimeException
{
    public $errors;
    public function __construct($message, array $errors = []) { parent::__construct($message); $this->errors=$errors; }
}

/** Uses the application's existing mysqli connection; never opens another transaction connection. */
class Asset_import_store
{
    private $db;
    public function __construct(mysqli $db) { $this->db=$db; }

    private function query($sql, array $args = [])
    {
        $stmt=$this->db->prepare($sql);
        if (!$stmt) { throw new RuntimeException('Query impor gagal disiapkan: '.$this->db->error); }
        try {
            if ($args) { $stmt->bind_param(str_repeat('s',count($args)), ...$args); }
            if (!$stmt->execute()) { throw new RuntimeException('Penulisan impor gagal: '.$stmt->error); }
            if ($this->db->warning_count) { throw new RuntimeException('Database memberi peringatan konversi data. Batch dibatalkan agar nilai tidak berubah.'); }
            $result=$stmt->get_result();
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        } finally { $stmt->close(); }
    }

    public function ready()
    {
        $tables=$this->query("SELECT TABLE_NAME,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('asset','asset_generate','asset_import_batches')");
        if (count($tables)!==3) { return false; }
        foreach ($tables as $table) { if (strtoupper($table['ENGINE'])!=='INNODB') { return false; } }
        $indexes=$this->query("SHOW INDEX FROM asset"); $keys=[];
        foreach ($indexes as $index) { if (!$index['Non_unique']) { $keys[$index['Key_name']][]=$index['Column_name']; } }
        return in_array(['kd_asset'],array_values($keys),true);
    }

    public function preview(array $validated, $user, $branch)
    {
        if ($validated['errors']) { throw new Asset_import_failure('Perbaiki semua baris sebelum menyimpan.', $validated['errors']); }
        $token=bin2hex(random_bytes(32));
        // Opportunistic cleanup only removes expired unsaved previews, never completed batch receipts.
        $this->query("DELETE FROM asset_import_batches WHERE status='preview' AND expires_at < ? LIMIT 100",[gmdate('Y-m-d H:i:s')]);
        $payload=json_encode($validated,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
        $this->query("INSERT INTO asset_import_batches (token,user_id,kdcab,status,data_json,created_at,expires_at) VALUES (?,?,?,'preview',?,?,?)",[$token,$user,$branch,$payload,gmdate('Y-m-d H:i:s'),gmdate('Y-m-d H:i:s',time()+1800)]);
        return $token;
    }

    public function save($token, $user, $branch, callable $masters)
    {
        if (!preg_match('/^[a-f0-9]{64}$/D',$token) || !preg_match('/^[A-Za-z0-9]{3}$/D',$branch)) { throw new Asset_import_failure('Token preview atau cabang tidak valid.'); }
        $database=$this->query('SELECT DATABASE() AS db')[0]['db'];
        $lock='asset-import:'.substr(hash('sha256',$database.':'.$branch),0,40);
        if ((int)$this->query('SELECT GET_LOCK(?,10) AS acquired',[$lock])[0]['acquired']!==1) { throw new Asset_import_failure('Impor cabang sedang diproses. Coba simpan kembali.'); }
        $inTransaction=false;
        try {
            if (!$this->db->begin_transaction()) { throw new RuntimeException('Transaksi tidak dapat dimulai.'); }
            $inTransaction=true;
            $batches=$this->query('SELECT * FROM asset_import_batches WHERE token=? AND user_id=? AND kdcab=? FOR UPDATE',[$token,$user,$branch]);
            if (!$batches) { throw new Asset_import_failure('Preview tidak ditemukan atau bukan milik user/cabang ini. Upload ulang.'); }
            $batch=$batches[0];
            if ($batch['status']==='complete') {
                $result=json_decode($batch['result_json'],true,512,JSON_THROW_ON_ERROR);
                $this->db->commit(); $inTransaction=false; $result['replayed']=true; return $result;
            }
            if (strtotime($batch['expires_at'].' UTC') <= time()) { throw new Asset_import_failure('Preview kedaluwarsa (30 menit). Upload ulang file.'); }
            $data=json_decode($batch['data_json'],true,512,JSON_THROW_ON_ERROR);
            $currentMasters=$masters();
            $errors=(new Asset_import())->revalidate($data['rows'],$currentMasters);
            if ($errors) { throw new Asset_import_failure('Master berubah. Periksa kesalahan dan upload ulang.', $errors); }
            $sequences=[]; $schedule=[]; $first=null; $last=null;
            foreach ($data['rows'] as $row) {
                $category=(int)$row['category'];
                if ($category < 1 || $category > 99) { throw new Asset_import_failure('ID kategori harus 1–99 untuk pola kode asset existing.'); }
                $prefix='AST-'.$branch.str_replace('-','',substr($row['date'],0,7)).'-'.sprintf('%02d',$category).'-';
                if (!isset($sequences[$prefix])) {
                    $max=$this->query('SELECT MAX(kd_asset) AS code FROM asset WHERE kd_asset LIKE ?',[$prefix.'%'])[0]['code'];
                    if ($max && !preg_match('/^'.preg_quote($prefix,'/').'[0-9]{5}$/D',$max)) { throw new Asset_import_failure('Pola kode asset existing tidak sesuai. Hubungi administrator.'); }
                    $sequences[$prefix]=$max ? (int)substr($max,strlen($prefix),3) : 0;
                }
                $number=++$sequences[$prefix];
                if ($number>999) { throw new Asset_import_failure('Nomor asset bulan/kategori sudah mencapai 999. Batch dibatalkan.'); }
                $categoryName=strtoupper($currentMasters['category'][$row['category_name']]['name']);
                for ($unit=1;$unit<=$row['qty'];$unit++) {
                    $code=$prefix.sprintf('%03d%02d',$number,$unit); $first=$first ?: $code; $last=$code;
                    $this->query('INSERT INTO asset (kd_asset,nm_asset,tgl_perolehan,category,nm_category,nilai_asset,qty,asset_ke,depresiasi,value,kdcab,lokasi_asset,cost_center,id_company,created_by,created_date) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[$code,$row['nm_asset'],$row['date'],$category,$categoryName,$row['amount'],$row['qty'],$unit,$row['years'],$row['monthly'],$branch,$row['department'],'',$row['company'],$user,date('Y-m-d H:i:s')]);
                    $start=new DateTimeImmutable(substr($row['date'],0,7).'-01');
                    for ($month=0;$month<$row['years']*12;$month++) {
                        $date=$start->modify('+'.$month.' months');
                        $schedule[]=[$code,$row['nm_asset'],$category,$categoryName,$date->format('m'),$date->format('Y'),$row['monthly'],$branch];
                        if (count($schedule)>=500) { $this->schedules($schedule); $schedule=[]; }
                    }
                }
            }
            if ($schedule) { $this->schedules($schedule); }
            $result=['status'=>1,'pesan'=>'Semua asset berhasil disimpan.','ringkasan'=>$data['summary'],'kode_pertama'=>$first,'kode_terakhir'=>$last,'replayed'=>false];
            $this->query("UPDATE asset_import_batches SET status='complete',data_json=NULL,result_json=? WHERE token=?",[json_encode($result,JSON_THROW_ON_ERROR),$token]);
            if (!$this->db->commit()) { throw new RuntimeException('Commit impor gagal.'); }
            $inTransaction=false; return $result;
        } catch (Throwable $error) {
            if ($inTransaction) { $this->db->rollback(); }
            throw $error;
        } finally { $this->query('SELECT RELEASE_LOCK(?)',[$lock]); }
    }

    private function schedules(array $rows)
    {
        $values=[]; foreach ($rows as $row) { foreach ($row as $value) { $values[]=$value; } }
        $this->query('INSERT INTO asset_generate (kd_asset,nm_asset,category,nm_category,bulan,tahun,nilai_susut,kdcab) VALUES '.implode(',',array_fill(0,count($rows),'(?,?,?,?,?,?,?,?)')),$values);
    }
}
