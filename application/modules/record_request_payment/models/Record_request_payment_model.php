<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Model: Record Request Payment (histori final snapshot - FSD 2026-09)
 * Sumber: tr_rp_pengajuan_h (status=done) + tr_rp_record (snapshot approved)
 */

class Record_request_payment_model extends BF_Model
{
	protected $table_name = 'tr_rp_pengajuan_h';
	protected $key        = 'id';
	protected $soft_deletes = false;
	protected $set_created  = false;
	protected $set_modified = false;

	public function __construct()
	{
		parent::__construct();
	}

	// Server-side DataTables: batch done yang punya >=1 record snapshot
	public function get_data_record()
	{
		$post   = $this->input->post();
		$draw   = isset($post['draw']) ? intval($post['draw']) : 0;
		$length = isset($post['length']) ? intval($post['length']) : 25;
		$start  = isset($post['start']) ? intval($post['start']) : 0;
		$search = isset($post['search']['value']) ? trim($post['search']['value']) : '';

		$build = function () {
			$this->db->from('tr_rp_pengajuan_h h');
			$this->db->where('h.deleted', 0);
			$this->db->where('h.status', 'done');
			// harus punya minimal 1 record snapshot
			$this->db->where('(SELECT COUNT(*) FROM tr_rp_record r WHERE r.id_pengajuan = h.id) >', 0, false);
		};

		// susun from/where SEKALI (count_all_results tanpa reset -> jangan append ulang)
		$build();
		$recordsTotal = $this->db->count_all_results('', false);

		if ($search !== '') {
			$this->db->group_start();
			$this->db->like('h.no_pengajuan', $search, 'both');
			$this->db->or_like('h.company_nama', $search, 'both');
			$this->db->group_end();
		}
		$recordsFiltered = $this->db->count_all_results('', false);

		$this->db->select('h.*, (SELECT COUNT(*) FROM tr_rp_record r WHERE r.id_pengajuan = h.id) as jumlah_approved');
		$this->db->order_by('h.decided_at', 'desc');
		$this->db->limit($length, $start);
		$rows = $this->db->get()->result();

		$data = [];
		foreach ($rows as $r) {
			// total dibayarkan dari snapshot (bukan header, konsisten dgn snapshot)
			$tot = $this->db->query("SELECT IFNULL(SUM(dibayarkan),0) as t FROM tr_rp_record WHERE id_pengajuan = ?", [$r->id])->row();
			$total = $tot ? $tot->t : 0;

			$data[] = [
				'no_pengajuan'     => $r->no_pengajuan,
				'company_nama'     => $r->company_nama ? $r->company_nama : '-',
				'submitted_at'     => !empty($r->submitted_at) ? date('d-M-Y H:i', strtotime($r->submitted_at)) : '',
				'decided_at'       => !empty($r->decided_at) ? date('d-M-Y H:i', strtotime($r->decided_at)) : '',
				'total_dibayarkan' => number_format($total, 0, ',', '.'),
				'jumlah_approved'  => (int) $r->jumlah_approved,
				'aksi'             => $r->id,
			];
		}

		echo json_encode([
			'draw'            => $draw,
			'recordsTotal'    => $recordsTotal,
			'recordsFiltered' => $recordsFiltered,
			'data'            => $data,
		]);
	}

	public function get_header($id_pengajuan)
	{
		$this->db->from('tr_rp_pengajuan_h');
		$this->db->where('id', (int) $id_pengajuan);
		$this->db->where('deleted', 0);
		$this->db->where('status', 'done');
		return $this->db->get()->row();
	}

	// Snapshot records (read-only) untuk 1 batch
	public function get_records($id_pengajuan)
	{
		$this->db->from('tr_rp_record');
		$this->db->where('id_pengajuan', (int) $id_pengajuan);
		$this->db->order_by('id', 'asc');
		$records = $this->db->get()->result();

		foreach ($records as $r) {
			$b = $this->resolve_bank_info($r->no_dokumen, $r->kategori);
			$r->bank_nama   = $b['nama'];
			$r->bank_name   = $b['bank'];
			$r->bank_no_rek = $b['no_rek'];
		}

		return $records;
	}

	/**
	 * Resolusi informasi rekening tujuan pembayaran (Nama, Bank, No. Rek)
	 * langsung dari dokumen sumber tanpa migrasi skema tabel.
	 */
	public function resolve_bank_info($no_dokumen, $kategori = '')
	{
		$res = [
			'nama'   => '',
			'bank'   => '',
			'no_rek' => '',
		];

		if (empty($no_dokumen)) {
			return $res;
		}

		$kat = strtolower(trim((string)$kategori));

		// 1. Kasbon (tr_kasbon)
		if ($kat === 'kasbon' || strpos($no_dokumen, 'KS-') === 0) {
			$row = $this->db->select('nama, bank_id, accnumber, accname')
							->get_where('tr_kasbon', ['no_doc' => $no_dokumen])
							->row();
			if ($row) {
				$res['nama']   = !empty($row->accname) ? trim($row->accname) : trim((string)$row->nama);
				$res['bank']   = trim((string)$row->bank_id);
				$res['no_rek'] = trim((string)$row->accnumber);
				return $res;
			}
		}

		// 2. Transport / Transportasi (tr_transport_req)
		if ($kat === 'transport' || $kat === 'transportasi' || strpos($no_dokumen, 'RQ-') === 0) {
			$row = $this->db->select('nama, bank_id, accnumber, accname')
							->get_where('tr_transport_req', ['no_doc' => $no_dokumen])
							->row();
			if ($row) {
				$res['nama']   = !empty($row->accname) ? trim($row->accname) : trim((string)$row->nama);
				$res['bank']   = trim((string)$row->bank_id);
				$res['no_rek'] = trim((string)$row->accnumber);
				return $res;
			}
		}

		// 3. Expense (tr_expense)
		if ($kat === 'expense' || strpos($no_dokumen, 'EXP-') === 0 || strpos($no_dokumen, 'ER-') === 0 || strpos($no_dokumen, 'PI-') === 0) {
			$row = $this->db->select('nama, bank_id, accnumber, accname')
							->get_where('tr_expense', ['no_doc' => $no_dokumen])
							->row();
			if ($row) {
				$res['nama']   = !empty($row->accname) ? trim($row->accname) : trim((string)$row->nama);
				$res['bank']   = trim((string)$row->bank_id);
				$res['no_rek'] = trim((string)$row->accnumber);
				return $res;
			}
		}

		// 4. Periodik (tr_pengajuan_rutin_detail)
		if ($kat === 'periodik' || strpos($no_dokumen, 'PERIODIK-') === 0) {
			$row = $this->db->select('nama, bank_id, accnumber, accname')
							->get_where('tr_pengajuan_rutin_detail', ['no_doc' => $no_dokumen])
							->row();
			if ($row) {
				$res['nama']   = !empty($row->accname) ? trim($row->accname) : trim((string)$row->nama);
				$res['bank']   = trim((string)$row->bank_id);
				$res['no_rek'] = trim((string)$row->accnumber);
				return $res;
			}
		}

		// 5. Cash (tr_pr_non_po -> rutin_non_planning_header)
		if ($kat === 'cash' || strpos($no_dokumen, 'PRN') === 0) {
			$pr = $this->db->select('no_pr')->get_where('tr_pr_non_po', ['no_non_po' => $no_dokumen])->row();
			if ($pr && !empty($pr->no_pr)) {
				$head = $this->db->select('bank_name, bank_account_no, bank_account_name')
								 ->get_where('rutin_non_planning_header', ['no_pr' => $pr->no_pr])
								 ->row();
				if ($head) {
					$res['nama']   = trim((string)$head->bank_account_name);
					$res['bank']   = trim((string)$head->bank_name);
					$res['no_rek'] = trim((string)$head->bank_account_no);
					return $res;
				}
			}
		}

		// 6. Direct Payment (tr_direct_payment)
		if ($kat === 'direct payment' || $kat === 'direct_payment' || strpos($no_dokumen, 'DPM') === 0 || strpos($no_dokumen, 'DP-') === 0) {
			$row = $this->db->select('bank, bank_number, bank_account')
							->get_where('tr_direct_payment', ['no_doc' => $no_dokumen])
							->row();
			if ($row) {
				$res['nama']   = trim((string)$row->bank_account);
				$res['bank']   = trim((string)$row->bank);
				$res['no_rek'] = trim((string)$row->bank_number);
				if (!empty($res['bank']) || !empty($res['no_rek']) || !empty($res['nama'])) {
					return $res;
				}
			}
		}

		// 7. request_payment fallback (Petty Cash / RPC / PHP)
		$rp = $this->db->select('nama, bank_id, accnumber, accname, bank_name')
					   ->get_where('request_payment', ['no_doc' => $no_dokumen])
					   ->row();
		if ($rp) {
			$res['nama']   = !empty($rp->accname) ? trim($rp->accname) : trim((string)$rp->nama);
			$res['bank']   = !empty($rp->bank_id) ? trim($rp->bank_id) : trim((string)$rp->bank_name);
			$res['no_rek'] = trim((string)$rp->accnumber);
			if (!empty($res['bank']) || !empty($res['no_rek']) || !empty($res['nama'])) {
				return $res;
			}
		}

		// 8. payment_approve fallback
		$pa = $this->db->select('nama, bank_id, accnumber, accname, bank_name')
					   ->get_where('payment_approve', ['no_doc' => $no_dokumen])
					   ->row();
		if ($pa) {
			$res['nama']   = !empty($pa->accname) ? trim($pa->accname) : trim((string)$pa->nama);
			$res['bank']   = !empty($pa->bank_id) ? trim($pa->bank_id) : trim((string)$pa->bank_name);
			$res['no_rek'] = trim((string)$pa->accnumber);
		}

		return $res;
	}

	// Server-side DataTables: histori dokumen lama (pre-cutoff) dari payment_approve
	public function get_data_record_legacy()
	{
		$post   = $this->input->post();
		$draw   = isset($post['draw']) ? intval($post['draw']) : 0;
		$length = isset($post['length']) ? intval($post['length']) : 25;
		$start  = isset($post['start']) ? intval($post['start']) : 0;
		$search = isset($post['search']['value']) ? trim($post['search']['value']) : '';

		$build = function () {
			$this->db->from('payment_approve pa');
			$this->db->where('(pa.deleted IS NULL OR pa.deleted = 0)', null, false);
			// Eksklusif legacy: dokumen yang TIDAK ADA di snapshot batch baru (tr_rp_record)
			$this->db->where('NOT EXISTS (SELECT 1 FROM tr_rp_record r WHERE r.no_dokumen = pa.no_doc)', null, false);
		};

		$build();
		$recordsTotal = $this->db->count_all_results('', false);

		if ($search !== '') {
			$this->db->group_start();
			$this->db->like('pa.no_doc', $search, 'both');
			$this->db->or_like('pa.nama', $search, 'both');
			$this->db->or_like('pa.keperluan', $search, 'both');
			$this->db->or_like('pa.tipe', $search, 'both');
			$this->db->group_end();
		}
		$recordsFiltered = $this->db->count_all_results('', false);

		$this->db->select('pa.id, pa.no_doc, pa.nama, pa.tgl_doc, pa.keperluan, pa.tipe, pa.jumlah, pa.status, pa.tanggal, pa.tgl_bayar, pa.ids, pa.doc_file, pa.doc_file_2, pa.link_doc');
		$this->db->order_by('pa.tgl_doc', 'desc');
		$this->db->order_by('pa.id', 'desc');
		$this->db->limit($length, $start);
		$rows = $this->db->get()->result();

		$this->load->model('request_payment/Request_payment_model');

		$data = [];
		foreach ($rows as $r) {
			$is_paid = (!empty($r->tgl_bayar) && $r->tgl_bayar != '0000-00-00') || $r->status == 2;
			$status_badge = $is_paid
				? '<span class="rec-status-done">Sudah Dibayar</span>'
				: '<span class="label label-warning" style="padding: 4px 8px; border-radius: 10px; font-size: 11px; font-weight: 700;">Menunggu Pembayaran</span>';

			// Format kategori
			$tipe_clean = ucwords(str_replace('_', ' ', strtolower($r->tipe)));
			if (strtolower($r->tipe) == 'cash') {
				$tipe_clean = 'Cash';
			} elseif (strtolower($r->tipe) == 'direct_payment' || strtolower($r->tipe) == 'direct payment') {
				$tipe_clean = 'Direct Payment';
			} elseif (strtolower($r->tipe) == 'refill_pettycash') {
				$tipe_clean = 'Refill Petty Cash';
			}

			// Buat print url
			$dummy_record = (object)[
				'kategori'   => $tipe_clean,
				'no_dokumen' => $r->no_doc,
				'id_dokumen' => $r->ids ? $r->ids : $r->no_doc,
				'id'         => $r->ids ? $r->ids : $r->no_doc,
				'ids'        => $r->ids,
				'no_doc'     => $r->no_doc
			];
			$print_url = $this->Request_payment_model->build_print_url($dummy_record);

			$btn_aksi = '';
			if (!empty($print_url)) {
				$btn_aksi .= '<a href="' . $print_url . '" target="_blank" class="mini-btn print" title="Print Dokumen"><i class="fa fa-print"></i> Print</a>';
			}
			if (!empty($r->doc_file)) {
				$btn_aksi .= ' <a href="' . base_url('assets/expense/' . $r->doc_file) . '" target="_blank" class="mini-btn view" title="Lihat Lampiran"><i class="fa fa-file"></i> File</a>';
			}

			$data[] = [
				'no_doc'        => $r->no_doc,
				'kategori'      => $tipe_clean,
				'nama'          => $r->nama ? $r->nama : '-',
				'keperluan'     => $r->keperluan ? $r->keperluan : '-',
				'tgl_doc'       => !empty($r->tgl_doc) && $r->tgl_doc != '0000-00-00' ? date('d-M-Y', strtotime($r->tgl_doc)) : '-',
				'tgl_bayar'     => !empty($r->tgl_bayar) && $r->tgl_bayar != '0000-00-00' ? date('d-M-Y', strtotime($r->tgl_bayar)) : '-',
				'jumlah'        => number_format((float)$r->jumlah, 0, ',', '.'),
				'status'        => $status_badge,
				'aksi'          => !empty($btn_aksi) ? $btn_aksi : '-',
			];
		}

		echo json_encode([
			'draw'            => $draw,
			'recordsTotal'    => $recordsTotal,
			'recordsFiltered' => $recordsFiltered,
			'data'            => $data,
		]);
	}
}
