<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Budget Rutin
 */

$status = array();
$waktu = array();
class Budget_periodik extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Pembayaran_Periodik.View';
	protected $addPermission  	= 'Pembayaran_Periodik.Add';
	protected $managePermission = 'Pembayaran_Periodik.Manage';
	protected $deletePermission = 'Pembayaran_Periodik.Delete';
	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('All/All_model', 'Budget_periodik/Budget_periodik_model'));
		$this->template->title('Master Pembayaran Rutin');
		$this->template->page_icon('fa fa-cubes');
		date_default_timezone_set('Asia/Bangkok');
		$this->waktu = array("bulan" => "bulan", "tahun" => "tahun");
	}

	private function deptNames()
	{
		$names = $this->All_model->GetDeptCombo();
		return is_array($names) ? $names : array();
	}

	/** Peta id master => pembayaran yang sudah disetujui di sistem (dari GetMasters). */
	private function inSysMap(array $masters)
	{
		$map = array();
		foreach ($masters as $m) {
			$map[(int) $m['id']] = (int) $m['in_sys'];
		}
		return $map;
	}

	/** Ringkasan per departemen (baris daftar) + peta budget dan BG pending per departemen. */
	private function overview()
	{
		$masters = $this->Budget_periodik_model->GetMasters();
		$bgs = $this->Budget_periodik_model->GetBgs();
		$masters = is_array($masters) ? $masters : array();
		$bgs = is_array($bgs) ? $bgs : array();
		$names = $this->deptNames();

		$byDept = array();
		foreach ($masters as $m) {
			$byDept[$m['departement']][] = $m;
		}
		$pending = $lastDone = array();
		foreach ($bgs as $bg) { // terbaru dulu
			if ($bg['status'] === 'pending' && !isset($pending[$bg['dept']])) { $pending[$bg['dept']] = $bg; }
			if ($bg['status'] === 'done' && !isset($lastDone[$bg['dept']])) { $lastDone[$bg['dept']] = $bg; }
		}
		$rows = array();
		foreach (array_unique(array_merge(array_keys($byDept), array_keys($pending))) as $d) {
			$list = isset($byDept[$d]) ? $byDept[$d] : array();
			$rows[] = array('dept' => $d, 'name' => isset($names[$d]) ? $names[$d] : $d,
				'sum' => Budget_logic::summary($list, $this->inSysMap($list)),
				'pending' => isset($pending[$d]) ? $pending[$d] : null, 'last' => isset($lastDone[$d]) ? $lastDone[$d] : null);
		}
		return array($rows, $byDept, $pending, $names);
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		list(, $byDept, $pending, $names) = $this->overview();
		$dd = array();
		foreach ($names as $id => $name) {
			if ($id === '') { continue; }
			$n = isset($byDept[$id]) ? count($byDept[$id]) : 0;
			$dd[] = array('id' => (string) $id, 'name' => $name, 'disabled' => false,
				'sub' => isset($pending[$id]) ? 'Menunggu approval ' . $pending[$id]['no'] : ($n ? 'Sudah ada ' . $n . ' item, buka untuk menambah atau mengubah' : 'Belum ada budget'));
		}
		$this->template->set('dd_items', json_encode($dd, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
		$this->template->set('can_add', has_permission($this->addPermission));
		$this->template->set('can_manage', has_permission($this->managePermission));
		$this->template->title('Master Pembayaran Periodik');
		$this->template->render('list');
	}

	/**
	 * Sumber data DataTables server-side untuk daftar budget. Ringkasan per departemen dihitung dari master
	 * dan BG (nama departemen datang dari DB HRIS), jadi cari/urut/halaman dilakukan di sini, bukan di browser.
	 * shortcut: seluruh ringkasan dihitung di memori tiap request, cukup untuk puluhan departemen; pindahkan ke SQL bila lebih.
	 */
	public function get_data()
	{
		$this->auth->restrict($this->viewPermission);
		$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
		$fmt = function ($n) { return number_format((float) $n, 0, ',', '.'); };
		list($rows) = $this->overview();
		$total = count($rows);

		$searchBox = (array) $this->input->post('search');
		$search = mb_strtolower(trim((string) (isset($searchBox['value']) ? $searchBox['value'] : '')));
		if ($search !== '') {
			$rows = array_values(array_filter($rows, function ($r) use ($search) {
				$hay = $r['name'] . ' ' . ($r['pending'] ? 'menunggu approval ' . $r['pending']['no'] : 'aktif ' . ($r['last'] ? $r['last']['no'] : ''));
				return mb_strpos(mb_strtolower($hay), $search) !== false;
			}));
		}
		$filtered = count($rows);

		$order = (array) $this->input->post('order');
		$col = isset($order[0]['column']) ? (int) $order[0]['column'] : 1;
		$desc = isset($order[0]['dir']) && $order[0]['dir'] === 'desc';
		$key = array(1 => function ($r) { return mb_strtolower($r['name']); }, 2 => function ($r) { return $r['sum']['act']; },
			3 => function ($r) { return $r['sum']['bln']; }, 4 => function ($r) { return $r['sum']['thn']; }, 5 => function ($r) { return $r['pending'] ? 0 : 1; });
		$key = isset($key[$col]) ? $key[$col] : $key[1];
		usort($rows, function ($a, $b) use ($key, $desc) {
			$cmp = $key($a) <=> $key($b);
			return $cmp === 0 ? strcmp($a['name'], $b['name']) : ($desc ? -$cmp : $cmp);
		});

		$start = max(0, (int) $this->input->post('start'));
		$length = (int) $this->input->post('length');
		$page = $length > 0 ? array_slice($rows, $start, min($length, 100)) : array_slice($rows, $start);

		$canAdd = has_permission($this->addPermission);
		$data = array();
		foreach ($page as $i => $r) {
			$s = $r['sum'];
			$p = $r['pending'];
			$status = $p ? '<span class="pill W">Menunggu approval</span><span class="sub">' . $e($p['no']) . ' · ' . count($p['lines']) . ' perubahan</span>'
				: '<span class="pill A">Aktif</span>' . ($r['last'] ? '<span class="sub">Terakhir diputuskan ' . $e($r['last']['no']) . '</span>' : '');
			$data[] = array(
				'DT_RowAttr' => array('data-dept' => $r['dept']),
				'no' => $start + $i + 1,
				'department' => $e($r['name']),
				'aktif' => (int) $s['act'] . ($s['n'] > $s['act'] ? '<span class="sub">dari ' . (int) $s['n'] . ' item</span>' : ''),
				'bulan' => $fmt($s['bln']),
				'tahun' => $fmt($s['thn']),
				'status' => $status,
				'action' => '<button type="button" class="btn c sm" data-view="' . $e($r['dept']) . '">Lihat</button>'
					. ($canAdd ? ' <a class="btn o sm" href="' . $e(base_url('budget_periodik/edit/' . rawurlencode($r['dept']))) . '" style="text-decoration:none">Edit</a>' : ''),
			);
		}
		$this->jsonOut(array('draw' => (int) $this->input->post('draw'), 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data));
	}

	/** Modal "Lihat": rincian budget dan riwayat perubahan satu departemen. */
	public function view($dept = '')
	{
		$this->auth->restrict($this->viewPermission);
		$masters = $this->Budget_periodik_model->GetMasters($dept);
		$bgs = $this->Budget_periodik_model->GetBgs($dept);
		$masters = is_array($masters) ? $masters : array();
		$names = $this->deptNames();
		$this->load->view('view', array(
			'dept' => $dept, 'dept_name' => isset($names[$dept]) ? $names[$dept] : $dept, 'masters' => $masters,
			'sum' => Budget_logic::summary($masters, $this->inSysMap($masters)), 'bgs' => is_array($bgs) ? $bgs : array(),
			'can_edit' => has_permission($this->addPermission),
		));
	}

	private function renderForm($dept, $title)
	{
		$masters = $this->Budget_periodik_model->GetMasters($dept);
		$bgs = $this->Budget_periodik_model->GetBgs($dept);
		$masters = is_array($masters) ? $masters : array();
		$bgs = is_array($bgs) ? $bgs : array();
		$names = $this->deptNames();

		$rows = array();
		foreach ($masters as $m) {
			$n = Budget_logic::normalize($m);
			$n['in_sys'] = (int) $m['in_sys'];
			$n['pend'] = (int) $m['pend'];
			$rows[] = $n;
		}
		$pending = null;
		$rejected = array(); // id master => penolakan terakhir (hanya bila keputusan terakhir untuk item itu adalah reject)
		foreach ($bgs as $bg) { // terbaru dulu
			if ($bg['status'] === 'pending' && $pending === null) {
				$pending = array('no' => $bg['no'], 'at' => $bg['at'], 'lines' => array_map(function ($l) {
					return array('kind' => $l['kind'], 'id' => $l['id_budget'], 'fields' => $l['fields'], 'nama' => $l['after']['nama']);
				}, $bg['lines']));
			}
			if ($bg['status'] === 'done') {
				foreach ($bg['lines'] as $l) {
					if ($l['id_budget'] !== null && !array_key_exists($l['id_budget'], $rejected)) {
						$rejected[$l['id_budget']] = $l['decision'] === 'R' ? array('no' => $bg['no'], 'note' => $l['note']) : null;
					}
				}
			}
		}
		$this->template->set('state', json_encode(array(
			'dept' => (string) $dept, 'rows' => $rows, 'pending' => $pending, 'rejected' => (object) array_filter($rejected),
			'coa' => (object) (array) $this->All_model->GetCoaCombo(), 'baseYm' => date('Y-m'), 'nextBg' => 'BG-' . date('ym') . '-NNN',
		), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
		$this->template->set('dept', $dept);
		$this->template->set('dept_name', isset($names[$dept]) ? $names[$dept] : $dept);
		$this->template->set('is_new_dept', !$rows);
		$this->template->title($title);
		$this->template->render('input_form');
	}

	public function create($key)
	{
		$this->auth->restrict($this->addPermission);
		$this->renderForm($key, 'Input Master Pembayaran Periodik');
	}

	public function edit($key)
	{
		$this->auth->restrict($this->addPermission);
		$this->renderForm($key, 'Input Master Pembayaran Periodik');
	}

	private function jsonOut($payload)
	{
		$this->output->set_content_type('application/json');
		echo json_encode($payload);
	}

	/**
	 * Ajukan perubahan budget satu departemen. Master (ms_budget_rutin) TIDAK berubah di sini: perubahan masuk
	 * sebagai BG-YYMM-NNN pending dan baru berlaku setelah disetujui di Approval Perubahan Budget.
	 * POST: departement, rows (JSON: seluruh baris form, baris lama dengan id, baris baru tanpa id).
	 */
	public function save_data()
	{
		$this->auth->restrict($this->addPermission);
		$dept = trim((string) $this->input->post('departement'));
		$draft = json_decode((string) $this->input->post('rows'), true);
		$fail = function ($msg) use ($dept) {
			simpan_aktifitas($this->addPermission, 'NewData', 'GAGAL, ajukan perubahan budget ' . $dept, 0, '', 0);
			$this->jsonOut(array('save' => 0, 'msg' => $msg));
		};
		if ($dept === '' || !is_array($draft)) {
			return $fail('Data tidak valid. Muat ulang halaman lalu coba lagi.');
		}
		$masters = $this->Budget_periodik_model->GetMasters($dept);
		if (!is_array($masters)) {
			return $fail('Data budget tidak dapat dibaca. Silakan coba lagi.');
		}
		$ids = array_map('intval', array_column($masters, 'id'));
		foreach ($draft as $r) {
			if (!is_array($r)) {
				return $fail('Data tidak valid. Muat ulang halaman lalu coba lagi.');
			}
			if (isset($r['id']) && $r['id'] !== '' && $r['id'] !== null && !in_array((int) $r['id'], $ids, true)) {
				return $fail('Data budget tidak sesuai dengan departemen ini. Muat ulang halaman lalu coba lagi.');
			}
		}
		$error = Budget_logic::validate($draft, $this->inSysMap($masters), $masters);
		if ($error !== null) {
			return $fail($error);
		}
		$pending = $this->Budget_periodik_model->GetPendingBg($dept);
		if ($pending === false) {
			return $fail('Data budget tidak dapat dibaca. Silakan coba lagi.');
		}
		if ($pending) {
			return $fail('Masih ada perubahan ' . $pending['no'] . ' yang menunggu Approval Perubahan Budget untuk departemen ini.');
		}
		$lines = Budget_logic::diff($masters, $draft);
		if (!$lines) {
			return $fail('Tidak ada perubahan untuk disimpan.');
		}
		list($ok, $no, $msg) = $this->Budget_periodik_model->CreateBg($dept, $lines, method_exists($this->auth, 'user_name') ? (string) $this->auth->user_name() : '');
		if (!$ok) {
			return $fail($msg !== '' ? $msg : 'Please try again later !');
		}
		simpan_aktifitas($this->addPermission, 'NewData', 'SUKSES, ajukan perubahan budget ' . $dept . ' ' . $no, count($lines), '', 1);
		$this->jsonOut(array('save' => 1, 'no_bg' => $no, 'count' => count($lines)));
	}

	function proses_budget_periodik()
	{
		$this->auth->restrict($this->managePermission);
		$data = $this->Budget_periodik_model->GetBudgetRutin();
		$this->db->trans_begin();
		if (!empty($data)) {
			$this->db->query("update ms_budget set bulan_1=0,bulan_2=0, bulan_3=0, bulan_4=0, bulan_5=0, bulan_6=0, bulan_7=0, bulan_8=0, bulan_9=0, bulan_10=0, bulan_11=0, bulan_12=0,total=0 WHERE tahun='" . date("Y") . "' and kategori='PEMBAYARAN PERIODIK'");
			foreach ($data as $record) {
				if (isset($record->aktif) && !(int) $record->aktif) {
					continue; // item dinonaktifkan lewat Approval Perubahan Budget tidak ikut anggaran
				}

				$databudget['tipe'] = $record->tipe;
				$databudget['coa'] = $record->coa;
				$databudget['tanggal'] = $record->tanggal;
				$databudget['nilai'] = $record->nilai;
				$databudget['departement'] = $record->departement;
				$databudget['kode_id'] = $record->kode_id;
				$databudget['tahun'] = date("Y");
				if ($record->kode_id != '') {
					$dtalok = $this->db->query("select * from ms_budget_rutin_alokasi where kode='" . $record->kode_id . "'")->result();
					if (!empty($dtalok)) {
						foreach ($dtalok as $rdetail) {
							$databudget['coa'] = $rdetail->coa;
							$databudget['nilai'] = ($rdetail->nilai * $record->nilai / 100);
							$this->Budget_periodik_model->updatebudget($databudget);
						}
					} else {
						$this->Budget_periodik_model->updatebudget($databudget);
					}
				} else {
					$this->Budget_periodik_model->updatebudget($databudget);
				}
			}
		}
		if ($this->db->trans_status()) {
			$keterangan     = "SUKSES, proses data ";
			$status         = 1;
			$nm_hak_akses   = $this->addPermission;
			$kode_universal = 'UpdateData';
			$jumlah         = 1;
			$sql            = $this->db->last_query();
			$result         = TRUE;
		} else {
			$keterangan     = "GAGAL, proses data ";
			$status         = 0;
			$nm_hak_akses   = $this->addPermission;
			$kode_universal = 'UpdateData';
			$jumlah         = 1;
			$sql            = $this->db->last_query();
			$result = FALSE;
		}
		$this->db->trans_complete();
		simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
		$this->db->trans_complete();
		$param = array(
			'save' => $result
		);
		echo json_encode($param);
	}
}
