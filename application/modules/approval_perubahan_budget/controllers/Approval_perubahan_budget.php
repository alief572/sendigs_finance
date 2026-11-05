<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * Approval Perubahan Budget Pembayaran Periodik (b-appr di specs/input/Periodik.html).
 * Perubahan budget (BG-YYMM-NNN) yang diajukan dari Budget Pembayaran Periodik baru berlaku di master
 * (ms_budget_rutin) setelah disetujui di sini. Keputusan per baris, final, reject wajib beralasan.
 * Direktur = grup yang diberi izin Approval_Perubahan_Budget.Manage.
 */
class Approval_perubahan_budget extends Admin_Controller
{
	protected $viewPermission   = 'Approval_Perubahan_Budget.View';
	protected $managePermission = 'Approval_Perubahan_Budget.Manage';

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('All/All_model', 'Budget_periodik/Budget_periodik_model'));
		$this->template->page_icon('fa fa-check-square-o');
		date_default_timezone_set('Asia/Bangkok');
	}

	private function deptNames()
	{
		$names = $this->All_model->GetDeptCombo();
		return is_array($names) ? $names : array();
	}

	private function userName()
	{
		return method_exists($this->auth, 'user_name') ? (string) $this->auth->user_name() : '';
	}

	private function jsonOut($payload)
	{
		$this->output->set_content_type('application/json');
		echo json_encode($payload);
	}

	/** Halaman daftar: hanya kerangka, isi tabel datang dari get_data (DataTables server-side). */
	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$this->template->title('Approval Perubahan Budget');
		$this->template->render('index');
	}

	/**
	 * Sumber data DataTables server-side. POST: tab = wait (BG menunggu) | rec (baris yang sudah diputuskan), lalu parameter DataTables.
	 * Respons membawa pending_total supaya judul tab "Menunggu approval (n)" ikut diperbarui.
	 * shortcut: semua BG dimuat lalu disaring/diurut/dipotong di memori (nama departemen dari DB HRIS tidak bisa di-JOIN); pindahkan ke SQL bila BG sudah ribuan.
	 */
	public function get_data()
	{
		$this->auth->restrict($this->viewPermission);
		$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
		$names = $this->deptNames();
		$dname = function ($d) use ($names) { return isset($names[$d]) ? $names[$d] : $d; };
		$itemName = function ($l) { return $l['kind'] === 'new' ? $l['after']['nama'] : $l['before']['nama']; };
		$bgs = $this->Budget_periodik_model->GetBgs();
		$bgs = is_array($bgs) ? $bgs : array();
		$canDecide = has_permission($this->managePermission);

		$tab = $this->input->post('tab') === 'rec' ? 'rec' : 'wait';
		$rows = array();
		$pendingTotal = 0;
		foreach ($bgs as $bg) {
			if ($bg['status'] === 'pending') {
				$pendingTotal++;
				if ($tab === 'wait') {
					$rows[] = array('bg' => $bg, 'dept' => $dname($bg['dept']), 'item' => implode(' ', array_map($itemName, $bg['lines'])));
				}
				continue;
			}
			if ($tab === 'rec') {
				foreach ($bg['lines'] as $l) {
					if ($l['decision']) {
						$rows[] = array('bg' => $bg, 'line' => $l, 'dept' => $dname($bg['dept']), 'item' => $itemName($l));
					}
				}
			}
		}
		$total = count($rows);

		$box = (array) $this->input->post('search');
		$search = mb_strtolower(trim((string) (isset($box['value']) ? $box['value'] : '')));
		if ($search !== '') {
			$rows = array_values(array_filter($rows, function ($r) use ($search, $tab) {
				$hay = $r['dept'] . ' ' . $r['bg']['no'] . ' ' . $r['item'] . ' ' . ($tab === 'wait' ? $r['bg']['by'] . ' ' . $r['bg']['at']
					: $r['line']['decided_by'] . ' ' . $r['line']['note'] . ' ' . ($r['line']['decision'] === 'A' ? 'approved' : 'rejected') . ' ' . $r['line']['decided_at']);
				return mb_strpos(mb_strtolower($hay), $search) !== false;
			}));
		}
		$filtered = count($rows);

		$keys = $tab === 'wait'
			? array(1 => function ($r) { return mb_strtolower($r['dept']); }, 2 => function ($r) { return $r['bg']['no']; }, 3 => function ($r) { return mb_strtolower($r['item']); }, 4 => function ($r) { return (string) $r['bg']['at']; })
			: array(0 => function ($r) { return (string) $r['line']['decided_at']; }, 1 => function ($r) { return $r['bg']['no']; }, 2 => function ($r) { return mb_strtolower($r['dept']); },
				3 => function ($r) { return mb_strtolower($r['item']); }, 5 => function ($r) { return $r['line']['decision']; });
		$order = (array) $this->input->post('order');
		$col = isset($order[0]['column']) ? (int) $order[0]['column'] : -1;
		$desc = !isset($order[0]['dir']) || $order[0]['dir'] !== 'asc';
		$key = isset($keys[$col]) ? $keys[$col] : ($tab === 'wait' ? $keys[2] : $keys[0]); // bawaan: terbaru dulu
		if (!isset($keys[$col])) { $desc = true; }
		usort($rows, function ($a, $b) use ($key, $desc) {
			$cmp = $key($a) <=> $key($b);
			return $desc ? -$cmp : $cmp;
		});

		$start = max(0, (int) $this->input->post('start'));
		$length = (int) $this->input->post('length');
		$page = $length > 0 ? array_slice($rows, $start, min($length, 100)) : array_slice($rows, $start);

		$data = array();
		foreach ($page as $i => $r) {
			$bg = $r['bg'];
			if ($tab === 'wait') {
				$lines = '';
				foreach ($bg['lines'] as $l) {
					$k = Budget_logic::KINDS[$l['kind']];
					$lines .= '<div><span class="pill ' . $e($k[0]) . '">' . $e($k[1]) . '</span> ' . $e($itemName($l)) . '</div>';
				}
				$data[] = array(
					'DT_RowAttr' => array('data-bg' => $bg['no']),
					'no' => $start + $i + 1,
					'department' => $e($r['dept']),
					'nomor' => $e($bg['no']),
					'perubahan' => $lines,
					'diajukan' => $e(substr((string) $bg['at'], 0, 16)) . '<span class="sub">' . $e($bg['by']) . '</span>',
					'action' => '<button type="button" class="btn c sm" data-view="' . $e($bg['no']) . '">Lihat</button>'
						. ($canDecide ? ' <a class="btn sm" href="' . $e(base_url('approval_perubahan_budget/proses/' . rawurlencode($bg['no']))) . '" style="text-decoration:none">Proses approval</a>' : ''),
				);
				continue;
			}
			$l = $r['line'];
			$k = Budget_logic::KINDS[$l['kind']];
			$diff = '';
			if ($l['kind'] !== 'new') {
				foreach ($l['fields'] as $f) {
					$val = function ($row) use ($f) { return $f === 'tanggal' ? periodik_schedule_text($row['tipe'], $row['tanggal']) : Budget_logic::value_text($f, $row[$f]); };
					$diff .= '<div><span class="sub" style="display:inline">' . $e(Budget_logic::LABELS[$f]) . ':</span> ' . $e($val($l['before'])) . ' &rarr; ' . $e($val($l['after'])) . '</div>';
				}
			}
			$data[] = array(
				'DT_RowAttr' => array('data-bg' => $bg['no']),
				'diputuskan' => $e(substr((string) $l['decided_at'], 0, 16)) . '<span class="sub">' . $e($l['decided_by']) . '</span>',
				'nomor' => $e($bg['no']),
				'department' => $e($r['dept']),
				'item' => $e($r['item']),
				'perubahan' => '<span class="pill ' . $e($k[0]) . '">' . $e($k[1]) . '</span>' . $diff,
				'keputusan' => '<span class="pill ' . ($l['decision'] === 'A' ? 'A' : 'R') . '">' . ($l['decision'] === 'A' ? 'Approved' : 'Rejected') . '</span>',
				'note' => $l['note'] !== '' ? '<span class="note">' . $e($l['note']) . '</span>' : '',
			);
		}
		$this->jsonOut(array('draw' => (int) $this->input->post('draw'), 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data, 'pending_total' => $pendingTotal));
	}

	/** Modal "Lihat" satu BG: sebelum dan sesudah tiap baris. */
	public function view($no = '')
	{
		$this->auth->restrict($this->viewPermission);
		$bgs = $this->Budget_periodik_model->GetBgs(null, null, $no);
		$bg = is_array($bgs) && isset($bgs[$no]) ? $bgs[$no] : null;
		$names = $this->deptNames();
		$this->load->view('view', array(
			'bg' => $bg, 'dept_name' => $bg && isset($names[$bg['dept']]) ? $names[$bg['dept']] : ($bg ? $bg['dept'] : ''),
			'can_decide' => $bg && $bg['status'] === 'pending' && has_permission($this->managePermission),
		));
	}

	/** Form keputusan per baris. */
	public function proses($no = '')
	{
		$this->auth->restrict($this->managePermission);
		$bgs = $this->Budget_periodik_model->GetBgs(null, null, $no);
		$bg = is_array($bgs) && isset($bgs[$no]) ? $bgs[$no] : null;
		if (!$bg || $bg['status'] !== 'pending') {
			$this->template->set_message($bg ? 'Perubahan ini sudah diputuskan dan tidak bisa diubah.' : 'Invalid Data', 'error');
			redirect('approval_perubahan_budget');
			return;
		}
		$names = $this->deptNames();
		$this->template->set('bg', $bg);
		$this->template->set('dept_name', isset($names[$bg['dept']]) ? $names[$bg['dept']] : $bg['dept']);
		$this->template->title('Approval Perubahan Budget');
		$this->template->render('form');
	}

	/** POST: no_bg, decision[id_baris] = A|R, note[id_baris]. */
	public function save_decision()
	{
		$this->auth->restrict($this->managePermission);
		$no = trim((string) $this->input->post('no_bg'));
		$decision = (array) $this->input->post('decision');
		$note = (array) $this->input->post('note');
		$decisions = array();
		foreach ($decision as $lineId => $d) {
			$decisions[(int) $lineId] = array($d === 'R' ? 'R' : 'A', (string) ($note[$lineId] ?? ''));
		}
		list($ok, $msg, $a, $r) = $this->Budget_periodik_model->DecideBg($no, $decisions, $this->userName());
		simpan_aktifitas($this->managePermission, 'UpdateData', ($ok ? 'SUKSES' : 'GAGAL') . ', keputusan perubahan budget ' . $no, $a + $r, '', $ok ? 1 : 0);
		if (!$ok) {
			$this->jsonOut(array('save' => 0, 'msg' => $msg));
			return;
		}
		$this->jsonOut(array('save' => 1, 'no_bg' => $no, 'approved' => $a, 'rejected' => $r));
	}
}
