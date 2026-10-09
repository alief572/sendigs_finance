<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Cash PR Stok receipts use the application's main database throughout. */
class Incoming_cash_model extends BF_Model
{
    private function query($sql, $bindings = [])
    {
        $result = $this->db->query($sql, $bindings);
        if ($result === false) throw new RuntimeException('Query penerimaan Cash gagal.');
        return $result;
    }

    private function eligibleSql()
    {
        // sts=2 also means forwarded to payment; explicit rejection flags are authoritative.
        return "c.jenis_pr IN ('pr stok','pr stock') AND c.sts IN ('1','2','3')
            AND COALESCE(c.reject_reason,'') = '' AND COALESCE(c.rejected_by,'') IN ('','0')
            AND c.rejected_date IS NULL AND h.category = 'pr stok' AND h.app_3 = '1'
            AND h.deleted_date IS NULL AND COALESCE(h.deleted_by,'') IN ('','0')
            AND COALESCE(h.rejected,'0') IN ('','0','N')
            AND COALESCE(h.reject_status,'0') IN ('','0','N')
            AND COALESCE(h.sts_reject1,'0') IN ('','0','N')
            AND COALESCE(h.sts_reject2,'0') IN ('','0','N')
            AND COALESCE(h.sts_reject3,'0') IN ('','0','N')
            AND d.status_app = 'Y' AND d.propose_purchase > 0";
    }

    public function items($documents = [])
    {
        $where = '';
        $bindings = [];
        if ($documents) {
            $where = ' AND c.no_non_po IN (' . implode(',', array_fill(0, count($documents), '?')) . ')';
            $bindings = array_values($documents);
        }
        return $this->query("SELECT c.no_non_po, h.no_pr, h.id AS pr_id, d.id AS detail_id,
                d.id_material, d.propose_purchase, d.price_ref, a.stock_name, a.id_stock,
                a.konversi, s.code AS unit,
                COALESCE((SELECT SUM(r.qty_oke) FROM warehouse_adjustment_detail r
                    JOIN warehouse_adjustment w ON w.kode_trans = r.kode_trans
                    WHERE r.tipe_po = 'cash' AND r.id_po_detail = d.id
                    AND w.category = 'incoming stok'),0) AS qty_in
            FROM tr_pr_non_po c JOIN material_planning_base_on_produksi h ON h.id = c.id_pr
            JOIN material_planning_base_on_produksi_detail d ON d.so_number = h.so_number
            LEFT JOIN accessories a ON a.id = d.id_material
            LEFT JOIN ms_satuan s ON s.id = a.id_unit
            WHERE " . $this->eligibleSql() . $where . ' ORDER BY c.id, d.id', $bindings)->result_array();
    }

    public function documents()
    {
        $documents = [];
        foreach ($this->items() as $item) {
            if ($item['propose_purchase'] > $item['qty_in']) {
                $documents[$item['no_non_po']] = ['no_non_po'=>$item['no_non_po'], 'no_pr'=>$item['no_pr']];
            }
        }
        return array_values($documents);
    }

    public function references($csv)
    {
        $docs = array_filter(explode(',', $csv));
        if (!$docs) return [];
        return $this->query('SELECT no_non_po, no_pr FROM tr_pr_non_po WHERE no_non_po IN (' .
            implode(',', array_fill(0, count($docs), '?')) . ') ORDER BY id', array_values($docs))->result_array();
    }

    private function insertReceiptRecord($table, $data)
    {
        if (!$this->db->insert($table, $data)) throw new RuntimeException('Penyimpanan penerimaan Cash gagal.');
    }

    public function receive($data, $user)
    {
        $lock = null;
        $started = false;
        $debug = $this->db->db_debug;
        $this->db->db_debug = false;
        try {
            $docs = isset($data['no_po']) && is_array($data['no_po']) ? $data['no_po'] : [];
            if (!$docs || !isset($data['Detail']) || !is_array($data['Detail'])) throw new InvalidArgumentException('Pilih dokumen dan barang Cash.');
            foreach ($docs as $doc) if (!is_string($doc) || strlen($doc) > 50 || strpos($doc, ',') !== false) throw new InvalidArgumentException('Dokumen Cash tidak valid.');
            $docs = array_values(array_unique($docs));
            sort($docs, SORT_STRING);
            if (empty($data['pic']) || empty($data['id_gudang'])) throw new InvalidArgumentException('Gudang dan PIC wajib diisi.');
            if (!is_string($data['pic']) || strlen($data['pic']) > 255 || !is_scalar($data['id_gudang']) || !ctype_digit((string)$data['id_gudang'])) throw new InvalidArgumentException('Gudang atau PIC tidak valid.');
            $date = DateTime::createFromFormat('!Y-m-d', $data['tanggal'] ?? '');
            if (!$date || $date->format('Y-m-d') !== $data['tanggal']) throw new InvalidArgumentException('Tanggal tidak valid.');
            // Serialize Cash number generation without introducing a schema migration.
            $lock = 'incoming_cash:' . $this->db->database;
            if ((int)$this->query('SELECT GET_LOCK(?,10) AS acquired', [$lock])->row()->acquired !== 1) throw new RuntimeException('Penerimaan sedang diproses. Silakan ulangi.');
            if (!$this->db->trans_begin()) throw new RuntimeException('Transaksi gagal dimulai.');
            $started = true;
            $marks = implode(',', array_fill(0, count($docs), '?'));
            $cash = $this->query("SELECT id, id_pr, no_non_po FROM tr_pr_non_po WHERE no_non_po IN ($marks) ORDER BY id FOR UPDATE", $docs)->result_array();
            if (count($cash) !== count($docs)) throw new InvalidArgumentException('Dokumen Cash tidak ditemukan.');
            $prIds = array_values(array_unique(array_column($cash, 'id_pr')));
            sort($prIds, SORT_NUMERIC);
            $prMarks = implode(',', array_fill(0, count($prIds), '?'));
            $this->query("SELECT id FROM material_planning_base_on_produksi WHERE id IN ($prMarks) ORDER BY id FOR UPDATE", $prIds);
            $details = $this->query("SELECT d.id, d.id_material FROM material_planning_base_on_produksi_detail d JOIN material_planning_base_on_produksi h ON h.so_number=d.so_number WHERE h.id IN ($prMarks) ORDER BY d.id FOR UPDATE", $prIds)->result_array();
            $materials = array_values(array_unique(array_column($details, 'id_material')));
            sort($materials, SORT_STRING);
            foreach ($materials as $material) {
                $this->query('SELECT id FROM accessories WHERE id=? FOR UPDATE', [$material]);
                $this->query('SELECT id FROM warehouse_stock WHERE id_material=? ORDER BY id FOR UPDATE', [$material]);
            }
            $warehouse = $this->query("SELECT * FROM warehouse WHERE id=? AND `desc`='stok' FOR UPDATE", [$data['id_gudang']])->row_array();
            if (!$warehouse) throw new InvalidArgumentException('Gudang stok tidak valid.');
            $items = [];
            $eligibleDocs = [];
            foreach ($this->items($docs) as $item) {
                $items[$item['no_non_po'] . ':' . $item['detail_id']] = $item;
                $eligibleDocs[$item['no_non_po']] = true;
            }
            if (count($eligibleDocs) !== count($docs)) throw new InvalidArgumentException('Dokumen Cash belum approved atau telah ditolak/dihapus.');
            $rows = [];
            $totals = [];
            $seen = [];
            foreach ($data['Detail'] as $row) {
                if (!is_array($row) || !isset($row['id_cash'], $row['id'], $row['qty_in'])
                    || !is_scalar($row['qty_in']) || !is_string($row['id_cash']) || !is_scalar($row['id'])
                    || !ctype_digit((string)$row['id'])) throw new InvalidArgumentException('Detail Cash tidak valid.');
                $qtyText = str_replace(',', '', (string)$row['qty_in']);
                if (!preg_match('/^\d+(?:\.\d{1,5})?$/D', $qtyText)) throw new InvalidArgumentException('Jumlah penerimaan tidak valid atau negatif.');
                $qty = (float)$qtyText;
                $key = $row['id_cash'] . ':' . $row['id'];
                if (isset($seen[$key]) || !isset($items[$key])) throw new InvalidArgumentException('Detail tidak sesuai dokumen Cash approved.');
                $seen[$key] = true;
                $item = $items[$key];
                if (!$qty) continue;
                if (empty($item['stock_name']) || empty($item['id_stock'])) throw new InvalidArgumentException('Barang tidak memiliki master stok.');
                if ($item['price_ref'] < 0) throw new InvalidArgumentException('Harga barang tidak valid.');
                $id = $item['detail_id'];
                $totals[$id] = ($totals[$id] ?? 0) + $qty;
                if ($totals[$id] > (float)$item['propose_purchase'] - (float)$item['qty_in'] + 0.0000001) throw new InvalidArgumentException('Qty diterima melebihi outstanding PR.');
                $rows[] = ['item'=>$item, 'qty'=>$qty, 'ket'=>(string)($row['ket'] ?? '')];
            }
            if (!$rows) throw new InvalidArgumentException('Isi minimal satu jumlah penerimaan positif.');
            usort($rows, function ($a, $b) { return strcmp($a['item']['id_material'], $b['item']['id_material']); });
            $code = generateNoTransaksiLainnya();
            $now = date('Y-m-d H:i:s');
            $this->insertReceiptRecord('warehouse_adjustment', ['kode_trans'=>$code,'tanggal'=>$data['tanggal'],
                'no_ipp'=>implode(',', $docs),'category'=>'incoming stok','jumlah_mat'=>array_sum($totals),
                'pic'=>$data['pic'],'note'=>$data['keterangan'] ?? '', 'kd_gudang_dari'=>'PURCHASE',
                'id_gudang_ke'=>$warehouse['id'],'kd_gudang_ke'=>strtoupper($warehouse['kd_gudang']),
                'created_by'=>$user,'created_date'=>$now]);
            foreach ($rows as $row) {
                $item = $row['item'];
                $qty = $row['qty'];
                $this->insertReceiptRecord('warehouse_adjustment_detail', ['kode_trans'=>$code,'no_ipp'=>$item['no_non_po'],
                    'tipe_po'=>'cash','id_po_detail'=>$item['detail_id'],'id_material'=>$item['id_material'],
                    'nm_material'=>$item['stock_name'],'qty_order'=>$item['propose_purchase'],'qty_oke'=>$qty,
                    'keterangan'=>$row['ket'],'update_by'=>$user,'update_date'=>$now]);
                $this->recordValue($item, $qty, $warehouse, $code, $user, $now);
            }
            if (!$this->db->trans_status() || !$this->db->trans_commit()) throw new RuntimeException('Penerimaan gagal, seluruh perubahan dibatalkan.');
            $started = false;
            return ['status'=>1,'pesan'=>'Penerimaan Pembelian Cash berhasil disimpan.','kode_trans'=>$code];
        } catch (Throwable $e) {
            if ($started) $this->db->trans_rollback();
            log_message('error', 'Incoming Cash: ' . $e->getMessage());
            return ['status'=>0,'pesan'=>$e instanceof InvalidArgumentException ? $e->getMessage() : 'Penerimaan Cash gagal; seluruh perubahan dibatalkan.'];
        } finally {
            if ($lock !== null) $this->db->query('SELECT RELEASE_LOCK(?)', [$lock]);
            $this->db->db_debug = $debug;
        }
    }

    private function recordValue($item, $qty, $warehouse, $code, $user, $now)
    {
        $stock = $this->query('SELECT qty_stock FROM warehouse_stock WHERE id_material=? AND id_gudang=?', [$item['id_material'],$warehouse['id']])->row_array();
        $previous = $this->query('SELECT value_neraca FROM tr_cost_book WHERE id_material=? AND id_gudang_ke=? ORDER BY created_on DESC, id DESC LIMIT 1', [$item['id_material'],$warehouse['id']])->row_array();
        $conversion = $item['konversi'] > 0 ? $item['konversi'] : 1;
        $balanceQty = ($stock['qty_stock'] ?? 0) / $conversion + $qty;
        if ($balanceQty <= 0) throw new InvalidArgumentException('Saldo stok setelah konversi harus positif untuk menghitung nilai persediaan.');
        $value = ($previous['value_neraca'] ?? 0) + $item['price_ref'] * $qty;
        $cost = $value / $balanceQty;
        $prefix = 'CBO-' . date('Y-m-');
        $sequence = $this->query('SELECT COALESCE(MAX(CAST(SUBSTRING(id,13) AS UNSIGNED)),0)+1 AS next_id FROM tr_cost_book WHERE id LIKE ?', [$prefix.'%'])->row()->next_id;
        $this->insertReceiptRecord('tr_cost_book', ['id'=>$prefix.sprintf('%06d', $sequence),'id_material'=>$item['id_material'],
            'nm_material'=>$item['stock_name'],'kode_produk'=>$item['id_stock'],'tipe_material'=>'stok',
            'id_gudang_ke'=>$warehouse['id'],'nm_gudang_ke'=>$warehouse['nm_gudang'], 'tgl'=>date('Y-m-d'),
            'no_transaksi'=>$code,'jenis_transaksi'=>'In pembelian','qty_transaksi'=>$qty,'qty'=>$balanceQty,
            'nilai_in'=>0,'nilai_beli'=>$item['price_ref'],'costbook'=>$cost,'value_transaksi'=>$item['price_ref']*$qty,
            'value_neraca'=>$value,'created_by'=>$user,'created_on'=>$now]);
        // Existing stock conversion/history semantics, now inside the same transaction.
        move_warehouse_stok([['id'=>$item['id_material'],'qty'=>$qty]], null, $warehouse['id'], $code, null);
        if (!$this->db->trans_status()) throw new RuntimeException('Pembaruan stok gagal.');
        $balance = $this->query("SELECT COALESCE(SUM(IF(s.id_gudang=1,s.qty_stock,0)),0) pusat,
            COALESCE(SUM(IF(w.`desc` IN ('stok','subgudang'),s.qty_stock,0)),0) subgudang,
            COALESCE(SUM(IF(w.`desc`='produksi',s.qty_stock,0)),0) produksi
            FROM warehouse_stock s JOIN warehouse w ON w.id=s.id_gudang WHERE s.id_material=?", [$item['id_material']])->row_array();
        $this->insertReceiptRecord('price_book', array_merge($balance, ['id_material'=>$item['id_material'],
            'price_book'=>$cost,'status'=>'Y','kode_trans'=>$code,'updated_by'=>$user,'updated_date'=>$now]));
    }
}
