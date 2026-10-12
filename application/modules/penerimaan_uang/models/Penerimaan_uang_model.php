<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once dirname(__DIR__) . '/libraries/Penerimaan_calculation.php';

class Penerimaan_uang_model extends BF_Model
{

    protected $ENABLE_ADD;
    protected $ENABLE_MANAGE;
    protected $ENABLE_VIEW;
    protected $ENABLE_DELETE;

    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Penerimaan_Uang.Add');
        $this->ENABLE_MANAGE  = has_permission('Penerimaan_Uang.Manage');
        $this->ENABLE_VIEW    = has_permission('Penerimaan_Uang.View');
        $this->ENABLE_DELETE  = has_permission('Penerimaan_Uang.Delete');
    }

    public function generate_id()
    {
        $Ym             = date('ym');
        $srcMtr            = "SELECT MAX(no_surat) as maxP FROM tr_penerimaan_piutang WHERE no_surat LIKE '%/" . int_to_roman(date('m')) . "/" . date('y') . "%' ";
        $resultMtr        = $this->db->query($srcMtr)->result_array();
        $angkaUrut2        = $resultMtr[0]['maxP'];
        $urutan2        = (int)substr($angkaUrut2, 0, 5);
        $urutan2++;
        $urut2            = sprintf('%05s', $urutan2);
        $kode_trans        = $urut2 . '/RKN-KRG/' . int_to_roman(date('m')) . '/' . date('y');

        return $kode_trans;
    }

    public function generate_id_invoice_jurnal($nomor)
    {
        $Ym             = date('ym');
        $srcMtr            = "SELECT MAX(id) as maxP FROM tr_jurnal WHERE no_jurnal LIKE '%" . int_to_roman(date('m')) . "-" . date('-y') . "%' ";
        $resultMtr        = $this->db->query($srcMtr)->result_array();
        $angkaUrut2        = $resultMtr[0]['maxP'];
        $urutan2        = (int)substr($angkaUrut2, 0, 5);
        $urutan2 = $urutan2 + $nomor;
        $urut2            = sprintf('%05s', $urutan2);
        $kode_trans        = $urut2 . '-AJV-' . int_to_roman(date('m')) . '-' . date('y');

        return $kode_trans;
    }

    public function get_alokasi_penerimaan()
    {
        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');

        $bank = $this->input->post('bank');

        $this->db->select('s.id as split_id, s.nominal as split_nominal, a.id as detail_id, a.tanggal_transaksi, a.keterangan, a.nominal_debit, a.nominal_kredit, a.saldo, a.reference_no, a.nilai_terpakai, b.nama as nama_bank_acc, b.rekening, c.nama_bank as nm_bank');
        $this->db->from('tr_alokasi_split s');
        $this->db->join('tr_alokasi_detail a', 'a.id = s.id_alokasi_detail');
        $this->db->join('ms_bank b', 'b.id = a.tipe_bank', 'left');
        $this->db->join('list_bank c', 'c.id = a.jenis_bank', 'left');
        $this->db->where('s.jenis_alokasi', 1);

        // Filter: Hanya memunculkan data untuk tahun 2025 ke atas (2024 ke bawah tidak dimunculkan)
        $this->db->where('YEAR(a.tanggal_transaksi) >=', 2025);

        if (!empty($bank)) {
            $this->db->where('a.tipe_bank', $bank);
        }
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('DATE_FORMAT(a.tanggal_transaksi,"%d-%M-%Y")', $search['value'], 'both');
            $this->db->or_like('a.reference_no', $search['value'], 'both');
            $this->db->or_like('b.nama', $search['value'], 'both');
            $this->db->or_like('b.rekening', $search['value'], 'both');
            $this->db->or_like('c.nama_bank', $search['value'], 'both');
            $this->db->or_like('a.keterangan', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('s.id');
        $this->db->order_by('a.nilai_terpakai', '');


        $db_clone = clone $this->db;
        $count_all = $db_clone->count_all_results();

        $this->db->limit($length, $start);
        $get_data = $this->db->get()->result_array();

        $hasil = [];

        $no = (0 + $start);
        foreach ($get_data as $item) {
            $no++;

            $tgl_transaksi_bank = '';
            if ($item['tanggal_transaksi'] == '' || $item['tanggal_transaksi'] == '0000-00-00') {
                $tgl_transaksi_bank = 'PEND';
            } else {
                $tgl_transaksi_bank = date('d-F-Y', strtotime($item['tanggal_transaksi']));
            }

            $nominal = $item['split_nominal'];

            $status = '<span class="badge bg-yellow">Draft</span>';

            $action = '<a href="' . base_url('penerimaan_uang/add_penerimaan_uang/' . $item['split_id']) . '" class="btn btn-sm btn-primary" title="Alokasi Penerimaan Uang"><i class="fa fa-plus"></i></a>';

            // Cek status berdasarkan apakah split ini sudah punya record penerimaan piutang
            $get_penerimaan = $this->db->get_where('tr_penerimaan_piutang', ['id_alokasi' => $item['split_id']])->row_array();
            if (!empty($get_penerimaan)) {
                $status = '<span class="badge bg-green">Used</span>';
                $action = '<button type="button" class="btn btn-sm btn-info detail" title="View Penerimaan Piutang" data-id="' . $get_penerimaan['id'] . '"><i class="fa fa-eye"></i></button>';
                
                // Check if journal has been posted
                $check_posted = $this->db->get_where('tr_jurnal', ['no_transaksi' => $get_penerimaan['no_surat'], 'sts' => '1'])->num_rows();
                if ($check_posted == 0) {
                    $action .= '&nbsp;<button type="button" class="btn btn-sm btn-danger rollback" title="Rollback Penerimaan Piutang" data-id="' . $get_penerimaan['id'] . '"><i class="fa fa-undo"></i></button>';
                }
            }

            $hasil[] = [
                'no' => $no,
                'tgl_transaksi_bank' => $tgl_transaksi_bank,
                'reference_no' => $item['reference_no'],
                'bank' => $item['nm_bank'] . ' - ' . $item['rekening'] . ' - ' . $item['nama_bank_acc'],
                'keterangan' => $item['keterangan'],
                'nominal' => number_format($nominal),
                'status' => $status,
                'action' => $action
            ];
        }

        $response = [
            'draw' => intval($draw),
            'recordsTotal' => $count_all,
            'recordsFiltered' => $count_all,
            'data' => $hasil
        ];

        echo json_encode($response);
    }

    public function getListBank()
    {
        $this->db->select('a.id, a.bank, a.rekening, a.nama, a.coa_bank, b.nama_bank');
        $this->db->from('ms_bank a');
        $this->db->join('list_bank b', 'b.id = a.bank', 'left');
        $this->db->where('a.deleted', '0');
        $get_list_bank = $this->db->get()->result_array();

        return $get_list_bank;
    }

    /** Remaining adjustment, based on PPh actually credited in receipt journals. */
    public function remaining_receipt_pph($invoice, $choice)
    {
        if (!in_array($choice, array('Y', 'N'), true)) {
            throw new InvalidArgumentException('Pilihan PPh tidak valid.');
        }
        $total = Penerimaan_calculation::cents($invoice['pph_jurnal']);
        $used = 0;
        $seen = array();
        $credits = array();
        $snapshotCredits = null;
        $coa = (string)$invoice['tipe_invoice'] === '1' ? '1106-01-05' : '1106-01-02';
        $history = $this->db->get_where('tr_penerimaan_piutang_detail', array('id_inv'=>$invoice['id']))->result_array();
        foreach ($history as $receipt) {
            $number = $receipt['id_header'];
            if (isset($seen[$number])) { throw new InvalidArgumentException('Riwayat penerimaan invoice ambigu; perlu rekonsiliasi.'); }
            $seen[$number] = true;
            $headers = $this->db->get_where('tr_penerimaan_piutang', array('no_surat'=>$number))->result_array();
            if (count($headers) !== 1 || $headers[0]['pph23_dipotong'] !== $choice) {
                throw new InvalidArgumentException('Pilihan PPh harus sama dengan riwayat penerimaan invoice.');
            }
            if ($choice === 'Y' || $total === 0) { continue; }
            $journals = $this->db->get_where('tr_jurnal', array('no_transaksi'=>$number))->result_array();
            $matches = array();
            foreach ($journals as $journal) {
                if ($journal['jenis_transaksi'] === 'Penerimaan Piutang' && $journal['coa'] === $coa
                    && $journal['keterangan'] === $journal['nm_coa'] . ' - ' . $invoice['no_invoice']) {
                    $matches[] = $journal;
                }
            }
            if (empty($journals)) {
                // Legacy receipts can retain their balance snapshots without journal rows.
                // Reconstruct only when the entire invoice balance chain is consistent.
                if ($snapshotCredits === null) {
                    $snapshotCredits = $this->receipt_snapshot_pph($invoice, $history);
                }
                $credit = $snapshotCredits[$number];
            } else {
                if (count($matches) !== 1) { throw new InvalidArgumentException('Jurnal PPh riwayat invoice tidak lengkap atau ambigu; perlu rekonsiliasi.'); }
                $credit = Penerimaan_calculation::cents($matches[0]['kredit']) - Penerimaan_calculation::cents($matches[0]['debit']);
            }
            if ($credit < 0) { throw new InvalidArgumentException('Jurnal PPh riwayat tidak valid.'); }
            $credits[$number] = $credit;
            $used += $credit;
        }
        if ($snapshotCredits !== null && $credits != $snapshotCredits) {
            throw new InvalidArgumentException('Jurnal dan saldo riwayat invoice tidak konsisten; perlu rekonsiliasi.');
        }
        if ($used > $total) { throw new InvalidArgumentException('PPh riwayat melebihi PPh invoice; perlu rekonsiliasi.'); }
        return $choice === 'Y' ? 0 : $total - $used;
    }

    /** Prove prior adjustments from the full chain of saved receipt balances. */
    private function receipt_snapshot_pph($invoice, $history)
    {
        foreach ($history as $receipt) {
            foreach (array('id', 'id_header', 'penerimaan', 'biaya_admin', 'sisa_piutang') as $field) {
                if (!isset($receipt[$field]) || $receipt[$field] === '') {
                    throw new InvalidArgumentException('Rincian saldo penerimaan lama tidak lengkap; perlu rekonsiliasi.');
                }
            }
            if (!ctype_digit((string)$receipt['id']) || (int)$receipt['id'] <= 0) {
                throw new InvalidArgumentException('Urutan penerimaan lama tidak valid; perlu rekonsiliasi.');
            }
        }
        usort($history, function ($a, $b) { return (int)$a['id'] <=> (int)$b['id']; });
        $balance = Penerimaan_calculation::cents($invoice['total_akhir_jurnal']);
        $totalPph = Penerimaan_calculation::cents($invoice['pph_jurnal']);
        $used = 0;
        $credits = array();
        $previousId = null;
        foreach ($history as $receipt) {
            $cash = Penerimaan_calculation::cents($receipt['penerimaan']);
            $admin = Penerimaan_calculation::cents($receipt['biaya_admin']);
            $remaining = Penerimaan_calculation::cents($receipt['sisa_piutang']);
            $adjustment = $cash + $admin - ($balance - $remaining);
            if ($previousId === (int)$receipt['id'] || min($balance, $cash, $admin, $remaining, $adjustment) < 0
                || $remaining > $balance || $adjustment > $cash || $used + $adjustment > $totalPph) {
                throw new InvalidArgumentException('Rantai saldo penerimaan lama tidak konsisten; perlu rekonsiliasi.');
            }
            $previousId = (int)$receipt['id'];
            $credits[$receipt['id_header']] = $adjustment;
            $used += $adjustment;
            $balance = $remaining;
        }
        if ($balance !== Penerimaan_calculation::cents($invoice['saldo_piutang'])) {
            throw new InvalidArgumentException('Saldo invoice berbeda dari rincian penerimaan terakhir; perlu rekonsiliasi.');
        }
        return $credits;
    }

    /**
     * Resolve split allocation first, falling back to a legacy detail allocation.
     * @return array|null ['split' => row|null, 'detail' => row, 'is_legacy' => bool]
     */
    public function resolve_alokasi($id_alokasi)
    {
        if (empty($id_alokasi)) {
            return null;
        }

        // Step 1: Check tr_alokasi_split first
        $split = $this->db->get_where('tr_alokasi_split', ['id' => $id_alokasi])->row_array();

        if (!empty($split)) {
            // Found in split table — join to tr_alokasi_detail via id_alokasi_detail
            $detail = $this->db->get_where('tr_alokasi_detail', ['id' => $split['id_alokasi_detail']])->row_array();

            if (empty($detail)) {
                // Data integrity issue: split references non-existent detail
                log_message('error', 'resolve_alokasi: tr_alokasi_split.id=' . $id_alokasi . ' references non-existent tr_alokasi_detail.id=' . $split['id_alokasi_detail']);
                return null;
            }

            return [
                'split'     => $split,
                'detail'    => $detail,
                'is_legacy' => false
            ];
        }

        // Step 2: Not found in split — try tr_alokasi_detail directly (legacy path)
        $detail = $this->db->get_where('tr_alokasi_detail', ['id' => $id_alokasi])->row_array();

        if (!empty($detail)) {
            return [
                'split'     => null,
                'detail'    => $detail,
                'is_legacy' => true
            ];
        }

        // Step 3: Not found in either table
        log_message('error', 'resolve_alokasi: id_alokasi=' . $id_alokasi . ' not found in tr_alokasi_split or tr_alokasi_detail');
        return null;
    }
}
