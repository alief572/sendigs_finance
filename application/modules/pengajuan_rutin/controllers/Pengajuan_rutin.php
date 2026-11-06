<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Pengajuan Rutin
 */

$status = array();
$waktu = array();
class Pengajuan_rutin extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Pengajuan_Pembayaran_Rutin.View';
	protected $addPermission  	= 'Pengajuan_Pembayaran_Rutin.Add';
	protected $managePermission = 'Pengajuan_Pembayaran_Rutin.Manage';
	protected $deletePermission = 'Pengajuan_Pembayaran_Rutin.Delete';
	protected $approvalViewPermission   = 'Approval_Pengajuan_Pembayaran_Rutin.View';
	protected $approvalManagePermission = 'Approval_Pengajuan_Pembayaran_Rutin.Manage';

	protected $hris;
	protected $waktu;

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('All/All_model', 'Pengajuan_rutin/Pengajuan_rutin_model'));
		$this->template->title('Master Pembayaran Rutin');
		$this->template->page_icon('fa fa-cubes');
		date_default_timezone_set('Asia/Bangkok');
		$this->waktu = array("bulan" => "bulan", "tahun" => "tahun");

		$this->hris = $this->load->database('hris', true);
	}

	private $auditQueriesOk = true;

	private function beginAudit($method, $data = array(), $keys = array())
	{
		$this->auditQueriesOk = true;
		$this->load->library('Module_audit_recorder');
		return $this->module_audit_recorder->begin('pengajuan_rutin', $method,
			array('data' => $this->auditSafeData($data)),
			array_unique(array_merge(array_keys($data), $keys, array('reason', 'outcome'))));
	}

	private function completeAudit($message, $status, $data = array())
	{
		return $this->module_audit_recorder->complete($message, $status,
			array('data' => $this->auditSafeData($data), 'sql' => null));
	}

	private function auditSafeData($data)
	{
		$safe = array();
		foreach ($data as $key => $value) {
			if (in_array($key, array('amount', 'total_amount'), true) && (!is_scalar($value) || !is_numeric($value))) { continue; }
			if (in_array($key, array('department_id', 'id'), true) && (!is_scalar($value) || !preg_match('/^[A-Za-z0-9_-]{1,64}$/', (string) $value))) { continue; }
			if ($key === 'no_doc' && (!is_scalar($value) || !preg_match('/^[A-Za-z0-9\/_.-]{1,100}$/', (string) $value))) { continue; }
			if (is_scalar($value) || $value === null) {
				$safe[$key] = is_string($value) ? mb_substr($value, 0, 512, 'UTF-8') : $value;
			}
		}
		return $safe;
	}

	private function auditObserve($result, $connection = null)
	{
		$connection = $connection ?: $this->db;
		$error = method_exists($connection, 'error') ? $connection->error() : array('code' => 0);
		if ($result === false || !empty($error['code'])) {
			$this->auditQueriesOk = false;
		}
		return $result;
	}

	public function get_user_department()
	{
		$startedAudit = $this->beginAudit(__FUNCTION__, array(), array('department_id', 'outcome'));
		$user_id = method_exists($this->auth, 'user_id') ? $this->auth->user_id() : 1;
		$query = $this->db->select('users.department_id as user_dept, emp.department_id as emp_dept')
			->from('users')
			->join(HRIS . '.employees emp', 'emp.id = users.employee_id', 'left')
			->where('users.id_user', $user_id)
			->get();
		$this->auditObserve($query);
		$user = $query ? $query->row() : null;

		$dept = null;
		if ($user) {
			$dept = !empty($user->user_dept) ? $user->user_dept : (!empty($user->emp_dept) ? $user->emp_dept : null);
		}

		if ($startedAudit) {
			$this->completeAudit('Membaca departemen user', $this->auditQueriesOk ? 1 : 0, array(
				'department_id' => $dept,
				'outcome'       => ($dept !== null && $this->auditQueriesOk) ? 'resolved' : 'unresolved'
			));
		}

		return $dept;
	}


	/* ------------------------------------------------------------------
	 * Pembayaran Periodik per item (mengikuti specs/input/Periodik.html)
	 * ------------------------------------------------------------------ */

	private function currentUserName()
	{
		return method_exists($this->auth, 'user_name') ? (string) $this->auth->user_name() : '';
	}

	/** Menyimpan/mengubah batch: izin Add atau Manage (sebelumnya Manage boleh mengedit). */
	private function requireWrite()
	{
		if (!has_permission($this->addPermission) && !has_permission($this->managePermission)) {
			$this->auth->restrict($this->addPermission);
		}
	}

	private function isAdmin()
	{
		return method_exists($this->auth, 'is_admin') ? (bool) $this->auth->is_admin() : false;
	}

	/** Departemen yang boleh dilihat user: admin semua, selain itu hanya departemennya sendiri. */
	private function allowedDept()
	{
		return $this->isAdmin() ? null : (string) $this->get_user_department();
	}

	private function deptNames()
	{
		$names = $this->All_model->GetDeptCombo();
		return is_array($names) ? $names : array();
	}

	/** Periode yang bisa dipilih di form: bulan ini dan dua bulan berikutnya. */
	private function periodOptions()
	{
		$out = array();
		for ($i = 0; $i < 3; $i++) {
			$t = strtotime(date('Y-m-01') . " +$i month");
			$out[date('Y-m', $t)] = $this->monthLabel(date('Y-m', $t));
		}
		return $out;
	}

	private function monthLabel($ym)
	{
		$bulan = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
		return $bulan[(int) substr($ym, 5, 2) - 1] . ' ' . substr($ym, 0, 4);
	}

	private function validPeriode($p)
	{
		return is_string($p) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $p) === 1;
	}

	/** Baris form per master budget untuk satu departemen dan periode. */
	private function buildFormRows($dept, $periode, $batch = null)
	{
		$masters = $this->Pengajuan_rutin_model->GetMasterWithItems($dept, $batch ? $batch['no'] : null);
		$this->auditObserve($masters);
		$masters = is_array($masters) ? $masters : array(); // pembacaan gagal: form kosong, galat tercatat di audit
		$mine = array();
		if ($batch) {
			foreach ($batch['items'] as $it) {
				$mine[$it['id_budget']] = $it;
			}
		}
		$rows = array();
		$hidden = array();
		usort($masters, function ($a, $b) { return strcmp((string) $a['nama'], (string) $b['nama']); });
		foreach ($masters as $m) {
			$st = Periodik_logic::state($m, $m['items'], $periode);
			$m['nm'] = $m['nama'];
			$row = array('m' => $m, 'st' => $st, 'tag' => Periodik_logic::masa_tag($st), 'mine' => isset($mine[$m['id']]) ? $mine[$m['id']] : null);
			$lastBank = $row['mine'] ? null : $this->Pengajuan_rutin_model->GetLastBank($m['id']);
			$row['bank'] = $row['mine'] ? array('bank_id' => $row['mine']['bank_id'], 'accnumber' => $row['mine']['accnumber'], 'accname' => $row['mine']['accname'])
				: array('bank_id' => $lastBank ? $lastBank->bank_id : '', 'accnumber' => $lastBank ? $lastBank->accnumber : '', 'accname' => $lastBank ? $lastBank->accname : '');
			if (in_array($st['state'], array('off', 'done', 'closed'), true) && !$row['mine']) {
				$hidden[] = $row;
			} else {
				$rows[] = $row;
			}
		}
		return array($rows, $hidden);
	}

	public function index()
	{
		$this->beginAudit(__FUNCTION__, array(), array('department_id', 'outcome'));
		$user_dept = $this->get_user_department();
		$dept = $this->allowedDept();
		$counts = $this->Pengajuan_rutin_model->CountBatchViews($dept);
		$this->auditObserve($counts);
		$this->template->set('counts', is_array($counts) ? $counts : array('wait' => 0, 'rec' => 0));
		$this->template->set('dd_items', json_encode($this->departmentChoices($dept), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
		$this->template->set('can_add', has_permission($this->addPermission));
		$this->template->set('can_manage', has_permission($this->managePermission));
		$this->template->set('can_delete', has_permission($this->deletePermission));
		$this->template->set('is_admin', $this->isAdmin());
		$this->template->set('user_dept', $user_dept);
		$this->template->title('Pengajuan Pembayaran Periodik');
		$this->template->render('list');
		$this->completeAudit('Membuka halaman pengajuan rutin', $this->auditQueriesOk ? 1 : 0, array(
			'department_id' => $user_dept,
			'outcome'       => $this->auditQueriesOk ? 'rendered' : 'query_error'
		));
	}

	/** Sumber data DataTables serverside halaman index: view wait|rec, mode batch|item. */
	public function data_list()
	{
		$this->beginAudit(__FUNCTION__, array(), array('department_id', 'outcome'));
		$in = $this->input->post();
		$view = ($in['view'] ?? '') === 'rec' ? 'rec' : 'wait';
		$mode = ($in['mode'] ?? '') === 'item' ? 'item' : 'batch';
		$filter = (string) ($in['filter'] ?? 'all');
		$search = trim((string) ($in['search']['value'] ?? ''));
		$start = (int) ($in['start'] ?? 0);
		$res = $this->Pengajuan_rutin_model->ListPage($this->allowedDept(), $view, $mode, $filter, $search, $start, (int) ($in['length'] ?? 10));
		$this->auditObserve($res);
		$out = array('draw' => (int) ($in['draw'] ?? 0), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array());
		if (is_array($res)) {
			$out['recordsTotal'] = $res[0];
			$out['recordsFiltered'] = $res[1];
			$out['data'] = $mode === 'item' ? $this->itemRows($res[2], $view) : $this->batchRows($res[2], $view, $start);
		} else {
			$out['error'] = 'Terjadi kesalahan. Silakan coba lagi.';
		}
		$this->jsonOut($out);
		$this->completeAudit('Memuat daftar pengajuan rutin', $this->auditQueriesOk ? 1 : 0, array(
			'outcome' => $this->auditQueriesOk ? 'rendered' : 'query_error'
		));
	}

	private function esc($v)
	{
		return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
	}

	private function batchRows(array $batches, $view, $start)
	{
		$names = $this->deptNames();
		$canEdit = has_permission($this->addPermission) || has_permission($this->managePermission);
		$canDelete = has_permission($this->deletePermission);
		$rows = array();
		foreach ($batches as $ix => $b) {
			$letters = array_column($b['items'], 'status');
			list($k, $t) = Periodik_logic::batch_status($letters);
			$status = '<span class="pill ' . $this->esc($k) . '">' . $this->esc($t) . '</span>';
			$actions = '<button type="button" class="btn c sm" data-view="' . (int) $b['id'] . '">Lihat</button>';
			if ($view === 'wait') {
				if ($canEdit) { $actions .= ' <a class="btn o sm" href="' . base_url('pengajuan_rutin/edit/' . (int) $b['id']) . '" style="text-decoration:none">Edit</a>'; }
				if ($canDelete) { $actions .= ' <button type="button" class="btn r sm" data-del="' . $this->esc($b['no']) . '" data-count="' . count($b['items']) . '">Hapus</button>'; }
			} elseif ($k === 'P' || $k === 'R') {
				$c = array_count_values($letters);
				$status .= '<span class="sub">' . (int) ($c['A'] ?? 0) . ' approved, ' . (int) ($c['R'] ?? 0) . ' rejected</span>';
			}
			$list = '<ol class="items">';
			foreach ($b['items'] as $i) { $list .= '<li>' . $this->esc($i['desc']) . '</li>'; }
			$rows[] = array(
				'DT_RowAttr' => array('data-batch' => $b['no']),
				$start + $ix + 1,
				$this->esc(isset($names[$b['dept']]) ? $names[$b['dept']] : $b['dept']),
				$this->esc($b['no']),
				$list . '</ol>',
				number_format((float) array_sum(array_column($b['items'], 'nominal')), 0, ',', '.'),
				'<span style="white-space:nowrap">' . $this->esc(Periodik_logic::fdate($b['date'])) . '</span>',
				$status,
				'<span style="white-space:nowrap">' . $actions . '</span>',
			);
		}
		return $rows;
	}

	private function itemRows(array $pairs, $view)
	{
		$names = $this->deptNames();
		$rp = $this->Pengajuan_rutin_model->GetRpStates(array_column(array_column($pairs, 'item'), 'no_item'));
		$rows = array();
		foreach ($pairs as $p) {
			$b = $p['batch'];
			$i = $p['item'];
			$no = $this->esc($i['no_item']) . ($i['ref'] !== '' ? '<span class="ref" style="display:block">Revisi dari ' . $this->esc($i['ref']) . '</span>' : '')
				. ($view === 'wait' && $i['resub'] !== '' ? '<span class="ref" style="display:block">Diajukan ulang di ' . $this->esc($i['resub']) . '</span>' : '');
			$row = array(
				'DT_RowAttr' => array('data-batch' => $b['no']),
				$no,
				'<span style="white-space:nowrap">' . $this->esc($b['no']) . '</span>',
				'<span style="white-space:nowrap">' . $this->esc(isset($names[$b['dept']]) ? $names[$b['dept']] : $b['dept']) . '</span>',
				$this->esc($i['desc']),
				number_format((float) $i['nominal'], 0, ',', '.'),
			);
			if ($view === 'wait') { $row[] = $this->esc($i['ket']); }
			$row[] = Periodik_logic::steps_html($i, isset($rp[$i['no_item']]) ? $rp[$i['no_item']] : null);
			$rows[] = $row;
		}
		return $rows;
	}

	/** Pilihan departemen pada tombol + New: hanya departemen yang punya budget yang bisa dipilih. */
	private function departmentChoices($onlyDept)
	{
		$names = $this->deptNames();
		$periode = date('Y-m');
		$label = $this->monthLabel($periode);
		$q = $this->db->distinct()->select('departement')->get('ms_budget_rutin');
		$this->auditObserve($q);
		$withBudget = array();
		foreach ($q ? $q->result() : array() as $r) {
			$withBudget[(string) $r->departement] = true;
		}
		$out = array();
		foreach ($names as $id => $name) {
			if ($id === '' || ($onlyDept !== null && (string) $id !== (string) $onlyDept)) {
				continue;
			}
			if (empty($withBudget[(string) $id])) {
				$out[] = array('id' => (string) $id, 'name' => $name, 'sub' => 'Belum ada budget aktif. Buat dulu di Budget Pembayaran Periodik', 'disabled' => true);
				continue;
			}
			list($rows) = $this->buildFormRows($id, $periode);
			$open = 0;
			foreach ($rows as $r) {
				$open += in_array($r['st']['state'], array('free', 'back'), true) ? 1 : 0;
			}
			$out[] = array('id' => (string) $id, 'name' => $name, 'disabled' => false,
				'sub' => $open > 0 ? $open . ' item bisa diajukan untuk ' . $label : 'Semua item sudah diajukan untuk ' . $label);
		}
		return $out;
	}

	public function create($key = '')
	{
		$this->auth->restrict($this->addPermission);
		$this->beginAudit(__FUNCTION__, array('department_id' => $key), array('department_id', 'outcome'));
		$user_dept = $this->get_user_department();

		if (!$this->isAdmin()) {
			if (empty($user_dept)) {
				$this->template->set_message("Departemen akun Anda belum disetting. Silakan hubungi Administrator.", 'error');
				$this->completeAudit('Membuka form input pengajuan rutin', 0, array(
					'department_id' => null,
					'outcome'       => 'dept_unconfigured',
					'reason'        => 'Departemen akun belum disetting'
				));
				redirect('pengajuan_rutin');
				return;
			}
			$key = $user_dept;
		}
		$periode = $this->input->get('periode');
		$periodOptions = $this->periodOptions();
		$periode = isset($periodOptions[$periode]) ? $periode : key($periodOptions);

		list($rows, $hidden) = $this->buildFormRows($key, $periode);
		$names = $this->deptNames();
		$this->template->set('type', 'add');
		$this->template->set('dept', $key);
		$this->template->set('dept_name', isset($names[$key]) ? $names[$key] : $key);
		$this->template->set('periode', $periode);
		$this->template->set('period_options', $periodOptions);
		$this->template->set('rows', $rows);
		$this->template->set('hidden', $hidden);
		$this->template->set('batch', null);
		$this->template->set('next_item', null);
		$this->template->title('Input Pengajuan Pembayaran Periodik');
		$this->template->render('input_form');
		$this->completeAudit('Membuka form input pengajuan rutin', $this->auditQueriesOk ? 1 : 0, array(
			'department_id' => $key,
			'outcome'       => $this->auditQueriesOk ? 'rendered' : 'query_error'
		));
	}

	public function edit($id)
	{
		$this->requireWrite();
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'department_id', 'outcome'));
		$data = $this->Pengajuan_rutin_model->GetDataPengajuanRutin($id);
		$this->auditObserve($data);
		if (!$data) {
			$this->template->set_message("Invalid Data", 'error');
			$this->completeAudit('Membuka form edit pengajuan rutin', 0, array('id' => $id, 'outcome' => 'not_found', 'reason' => 'Invalid Data'));
			redirect('pengajuan_rutin');
			return;
		}
		if (!$this->isAdmin() && (string) $data->departement !== (string) $this->get_user_department()) {
			$this->template->set_message("Anda tidak memiliki akses ke data departemen ini.", 'error');
			$this->completeAudit('Membuka form edit pengajuan rutin', 0, array(
				'id' => $id, 'no_doc' => $data->no_doc ?? null, 'department_id' => $data->departement ?? null,
				'outcome' => 'unauthorized', 'reason' => 'Akses ditolak ke departemen lain'
			));
			redirect('pengajuan_rutin');
			return;
		}
		$all = $this->Pengajuan_rutin_model->GetBatchesWithItems(null, $data->no_doc);
		$this->auditObserve($all);
		$batch = is_array($all) && isset($all[$data->no_doc]) ? $all[$data->no_doc] : null;
		$letters = $batch ? array_column($batch['items'], 'status') : array();
		if (!$batch || array_unique($letters) !== array('W')) {
			$this->template->set_message("Batch sudah diputuskan dan tidak bisa diubah.", 'error');
			$this->completeAudit('Membuka form edit pengajuan rutin', 0, array(
				'id' => $id, 'no_doc' => $data->no_doc ?? null, 'department_id' => $data->departement ?? null,
				'outcome' => $this->auditQueriesOk ? 'already_decided' : 'query_error', 'reason' => 'Batch sudah diputuskan'
			));
			redirect('pengajuan_rutin');
			return;
		}

		list($rows, $hidden) = $this->buildFormRows($data->departement, $batch['periode'], $batch);
		$names = $this->deptNames();
		$this->template->set('type', 'edit');
		$this->template->set('dept', $data->departement);
		$this->template->set('dept_name', isset($names[$data->departement]) ? $names[$data->departement] : $data->departement);
		$this->template->set('periode', $batch['periode']);
		$this->template->set('period_options', array($batch['periode'] => $this->monthLabel($batch['periode'])));
		$this->template->set('rows', $rows);
		$this->template->set('hidden', $hidden);
		$this->template->set('batch', $batch);
		$this->template->title('Edit Pengajuan Pembayaran Periodik');
		$this->template->render('input_form');
		$this->completeAudit('Membuka form edit pengajuan rutin', $this->auditQueriesOk ? 1 : 0, array(
			'id' => $id, 'no_doc' => $data->no_doc ?? null, 'department_id' => $data->departement ?? null,
			'outcome' => $this->auditQueriesOk ? 'rendered' : 'query_error'
		));
	}

	/** Modal "Lihat" satu batch. $from = 'app' menampilkan tombol Proses approval bila masih ada item menunggu. */
	public function view($id, $from = '')
	{
		$this->beginAudit(__FUNCTION__, array('id' => $id, 'app' => $from), array('id', 'no_doc', 'app', 'outcome'));
		$data = $this->Pengajuan_rutin_model->GetDataPengajuanRutin($id);
		$this->auditObserve($data);
		if (!$data) {
			$this->completeAudit('Melihat detail pengajuan rutin', 0, array('id' => $id, 'outcome' => 'not_found', 'reason' => 'Invalid Data'));
			echo '<p class="hint">Data tidak ditemukan.</p>';
			return;
		}
		if ($from !== 'app' && !$this->isAdmin() && (string) $data->departement !== (string) $this->get_user_department()) {
			$this->completeAudit('Melihat detail pengajuan rutin', 0, array('id' => $id, 'no_doc' => $data->no_doc, 'outcome' => 'unauthorized', 'reason' => 'Akses ditolak ke departemen lain'));
			echo '<p class="hint">Anda tidak memiliki akses ke data departemen ini.</p>';
			return;
		}
		$all = $this->Pengajuan_rutin_model->GetBatchesWithItems(null, $data->no_doc);
		$this->auditObserve($all);
		$batch = is_array($all) && isset($all[$data->no_doc]) ? $all[$data->no_doc] : null;
		$names = $this->deptNames();
		$chain = array();
		foreach ($batch ? $batch['items'] : array() as $it) {
			if ($it['ref'] !== '') {
				$chain[$it['no_item']] = $this->revisionChain($it['ref']);
			}
		}
		$this->load->view('batch_detail', array(
			'chain' => $chain,
			'batch' => $batch,
			'dept_name' => isset($names[$data->departement]) ? $names[$data->departement] : $data->departement,
			'rp' => $this->Pengajuan_rutin_model->GetRpStates($batch ? array_column($batch['items'], 'no_item') : array()),
			'from' => $from,
			'can_decide' => $from === 'app' && has_permission($this->approvalManagePermission),
		));
		$this->completeAudit('Melihat detail pengajuan rutin', $this->auditQueriesOk ? 1 : 0, array(
			'id' => $id, 'no_doc' => $data->no_doc, 'app' => $from, 'outcome' => $this->auditQueriesOk ? 'rendered' : 'query_error'
		));
	}

	private function jsonOut($payload)
	{
		$this->output->set_content_type('application/json');
		echo json_encode($payload);
	}

	private function uploadDoc($field)
	{
		if (empty($_FILES[$field]['name'])) {
			return array(true, null);
		}
		if (!is_dir('./assets/bayar_rutin/')) {
			mkdir('./assets/bayar_rutin/', 0755, true);
		}
		$this->load->library('upload');
		$this->upload->initialize(array('upload_path' => './assets/bayar_rutin/', 'allowed_types' => 'pdf|jpg|jpeg|png', 'remove_spaces' => true, 'encrypt_name' => true));
		if (!$this->upload->do_upload($field)) {
			return array(false, strip_tags($this->upload->display_errors('', '')));
		}
		$up = $this->upload->data();
		return array(true, $up['file_name']);
	}

	/**
	 * Simpan batch baru atau perubahan batch (hanya bila semua item masih menunggu).
	 * POST: departement, periode, id (batch, kosong untuk baru), biaya[id_budget], ket[id_budget], file doc_<id_budget>.
	 */
	public function save_data()
	{
		$this->requireWrite();
		$departement = (string) $this->input->post('departement');
		$periode     = (string) $this->input->post('periode');
		$id          = (string) $this->input->post('id');
		$this->beginAudit(__FUNCTION__, array('department_id' => $departement), array('no_doc', 'department_id', 'amount', 'item_count', 'outcome'));

		$fail = function ($msg, $outcome) use ($departement) {
			$this->completeAudit('Menyimpan data pengajuan rutin', 0, array('department_id' => $departement, 'outcome' => $outcome, 'reason' => $msg));
			$this->jsonOut(array('save' => 0, 'msg' => $msg));
		};

		if (!$this->isAdmin() && (string) $this->get_user_department() !== $departement) {
			return $fail('Anda tidak memiliki akses ke data departemen ini.', 'unauthorized');
		}
		if (!$this->validPeriode($periode)) {
			return $fail('Periode pembayaran tidak valid.', 'invalid_period');
		}

		$batch = null;
		if ($id !== '') {
			$header = $this->Pengajuan_rutin_model->GetDataPengajuanRutin($id);
			$all = $header ? $this->Pengajuan_rutin_model->GetBatchesWithItems(null, $header->no_doc) : false;
			$batch = is_array($all) && $header && isset($all[$header->no_doc]) ? $all[$header->no_doc] : null;
			if (!$batch || (string) $batch['dept'] !== $departement) {
				return $fail('Batch tidak ditemukan.', 'not_found');
			}
			if (array_unique(array_column($batch['items'], 'status')) !== array('W')) {
				return $fail('Batch sudah diputuskan dan tidak bisa diubah.', 'already_decided');
			}
			$periode = $batch['periode']; // periode batch tidak bisa diubah
		}

		list($rows) = $this->buildFormRows($departement, $periode, $batch);
		if (!$this->auditQueriesOk) {
			return $fail('Data budget tidak dapat dibaca. Silakan coba lagi.', 'query_error');
		}
		$biaya = (array) $this->input->post('biaya');
		$ket   = (array) $this->input->post('ket');
		$bankIn = array();
		foreach (array('bank_id', 'accnumber', 'accname') as $f) {
			$bankIn[$f] = (array) $this->input->post($f);
		}

		$plan = array();
		$errors = array();
		$overBudget = false;
		foreach ($rows as $r) {
			$mid = (string) $r['m']['id'];
			$val = isset($biaya[$mid]) && is_numeric($biaya[$mid]) ? (float) $biaya[$mid] : 0;
			if ($val <= 0) {
				continue;
			}
			$editable = $r['mine'] || in_array($r['st']['state'], array('free', 'back'), true);
			if (!$editable) {
				$errors[] = 'Item "' . $r['m']['nama'] . '" tidak bisa diajukan untuk periode ini.';
				continue;
			}
			if ($val > (float) $r['m']['nilai']) {
				$errors[] = 'Perkiraan biaya "' . $r['m']['nama'] . '" tidak boleh melebihi budget (' . number_format((float) $r['m']['nilai'], 0, ',', '.') . ').';
				$overBudget = true;
				continue;
			}
			$bank = array('bank_id' => trim((string) ($bankIn['bank_id'][$mid] ?? '')), 'accnumber' => trim((string) ($bankIn['accnumber'][$mid] ?? '')), 'accname' => trim((string) ($bankIn['accname'][$mid] ?? '')));
			if (!$r['mine'] && implode('', $bank) === '') {
				$bank = $r['bank']; // dikosongkan di form: item baru memakai bank terakhir
			}
			$plan[] = array('row' => $r, 'nilai' => $val, 'ket' => trim((string) ($ket[$mid] ?? '')), 'bank' => $bank);
		}
		if ($errors) {
			return $fail(implode("\n", $errors), $overBudget ? 'over_budget' : 'not_editable');
		}
		if (!$plan) {
			return $fail($batch ? 'Minimal satu item harus diisi. Untuk membatalkan seluruh batch, pakai tombol Hapus di daftar.' : 'Isi perkiraan biaya minimal satu item sebelum menyimpan.', 'empty');
		}

		if ($this->db->trans_begin() === false) {
			return $fail('Please try again later !', 'trans_begin_failure');
		}
		$userId = method_exists($this->auth, 'user_id') ? $this->auth->user_id() : 1;
		$now = date('Y-m-d H:i:s');
		$kept = array();
		$created = array();
		$ok = true;
		$msg = '';
		try {
		do {
			if ($batch) {
				$noDoc = $batch['no'];
			} else {
				$noDoc = periodik_next_number($this->db, 'BP');
				if ($noDoc === '') { $ok = false; $msg = 'Nomor batch gagal dibuat.'; break; }
				$ok = (bool) $this->db->insert('tr_pengajuan_rutin', array('tipe' => 'rutin', 'no_doc' => $noDoc, 'tanggal_doc' => date('Y-m-d'),
					'periode' => $periode, 'departement' => $departement, 'status' => 0, 'created_by' => $userId, 'created_on' => $now));
				if (!$ok) { break; }
			}
			foreach ($plan as $p) {
				$m = $p['row']['m'];
				$mine = $p['row']['mine'];
				$st = $p['row']['st'];
				$ketText = implode(' - ', array_filter(array(Periodik_logic::ket_prefix($st), $p['ket']), 'strlen'));
				list($upOk, $file) = $this->uploadDoc('doc_' . $m['id']);
				if (!$upOk) { $ok = false; $msg = $file; break 2; }
				$data = array('id_budget' => $m['id'], 'coa' => $m['coa'], 'nama' => $m['nama'], 'tanggal' => $m['tanggal'], 'budget' => $m['nilai'],
					'nilai' => $p['nilai'], 'keterangan' => $ketText, 'metode_pembelian' => '1', 'modified_by' => $userId, 'modified_on' => $now) + $p['bank'];
				if ($file !== null) { $data['doc_file'] = $file; }
				if ($mine) {
					$ok = (bool) $this->db->update('tr_pengajuan_rutin_detail', $data, array('id' => $mine['id']));
					$kept[] = $mine['id'];
				} else {
					$data += array('no_doc' => $noDoc, 'no_item' => periodik_next_number($this->db, 'PI'), 'item_status' => 0,
						'created_by' => $userId, 'created_on' => $now);
					if ($data['no_item'] === '') { $ok = false; $msg = 'Nomor item gagal dibuat.'; break 2; }
					if ($st['state'] === 'back') {
						$data['revisi_dari'] = $st['back']['no_item'];
					}
					$ok = (bool) $this->db->insert('tr_pengajuan_rutin_detail', $data);
					$newId = $this->db->insert_id();
					$kept[] = $newId;
					$created[] = $data['no_item'];
					if ($ok && $st['state'] === 'back') {
						$ok = (bool) $this->db->update('tr_pengajuan_rutin_detail', array('diajukan_ulang_sebagai' => $data['no_item']), array('no_item' => $st['back']['no_item']));
					}
				}
				if (!$ok) { break 2; }
			}
			if ($batch) {
				// item yang biayanya dikosongkan dikeluarkan dari batch; tautan "diajukan ulang" item asal dilepas
				foreach ($batch['items'] as $it) {
					if (in_array($it['id'], $kept, true)) { continue; }
					if ($it['ref'] !== '') {
						$ok = (bool) $this->db->update('tr_pengajuan_rutin_detail', array('diajukan_ulang_sebagai' => null), array('no_item' => $it['ref'])) && $ok;
					}
					$ok = (bool) $this->db->delete('tr_pengajuan_rutin_detail', array('id' => $it['id'])) && $ok;
				}
			}
		} while (false);
		} catch (Throwable $e) {
			$this->db->trans_rollback();
			return $fail('Please try again later !', 'exception');
		}

		if (!$ok || $this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return $fail($msg !== '' ? $msg : 'Please try again later !', 'write_failed');
		}
		if ($this->db->trans_commit() === false) {
			return $fail('Please try again later !', 'commit_failed');
		}
		$total = array_sum(array_column($plan, 'nilai'));
		$this->completeAudit('Menyimpan data pengajuan rutin', 1, array('no_doc' => $noDoc, 'department_id' => $departement, 'amount' => $total, 'item_count' => count($plan), 'outcome' => 'saved'));
		$this->jsonOut(array('save' => 1, 'no_doc' => $noDoc, 'edit' => $batch ? 1 : 0, 'count' => count($plan),
			'first' => $created ? $created[0] : '', 'last' => $created ? end($created) : '', 'periode' => $this->monthLabel($periode)));
	}

	/** Hapus batch: hanya bila semua item masih menunggu approval. $id = nomor batch. */
	public function hapus_data($id)
	{
		$this->auth->restrict($this->deletePermission);
		$this->beginAudit(__FUNCTION__, array('no_doc' => $id), array('no_doc', 'outcome'));
		$fail = function ($msg, $outcome) use ($id) {
			$this->completeAudit('Hapus data pengajuan rutin', 0, array('no_doc' => $id, 'outcome' => $outcome));
			$this->jsonOut(array('delete' => 0, 'idx' => $id, 'msg' => $msg));
		};
		$all = $id !== '' ? $this->Pengajuan_rutin_model->GetBatchesWithItems(null, $id) : false;
		$batch = is_array($all) && isset($all[$id]) ? $all[$id] : null;
		if (!$batch) {
			return $fail('Batch tidak ditemukan.', 'not_found');
		}
		if (!$this->isAdmin() && (string) $batch['dept'] !== (string) $this->get_user_department()) {
			return $fail('Akses ditolak', 'unauthorized');
		}
		if (array_unique(array_column($batch['items'], 'status')) !== array('W')) {
			return $fail('Batch sudah diputuskan dan tidak bisa dihapus.', 'already_decided');
		}
		$this->db->trans_begin();
		$ok = true;
		try {
			foreach ($batch['items'] as $it) {
				if ($it['ref'] !== '') {
					$ok = (bool) $this->db->update('tr_pengajuan_rutin_detail', array('diajukan_ulang_sebagai' => null), array('no_item' => $it['ref'])) && $ok;
				}
			}
			$ok = (bool) $this->db->delete('tr_pengajuan_rutin_detail', array('no_doc' => $id)) && $ok;
			$ok = (bool) $this->db->delete('tr_pengajuan_rutin', array('no_doc' => $id)) && $ok;
		} catch (Throwable $e) {
			$this->db->trans_rollback();
			return $fail('Please try again later !', 'exception');
		}
		if (!$ok || $this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return $fail('Please try again later !', 'write_failed');
		}
		if ($this->db->trans_commit() === false) {
			return $fail('Please try again later !', 'commit_failed');
		}
		$this->completeAudit('Hapus data pengajuan rutin', 1, array('no_doc' => $id, 'outcome' => 'deleted'));
		$this->jsonOut(array('delete' => 1, 'idx' => $id));
	}

	/** Tutup item yang ditolak tanpa mengajukan ulang. POST: no_item, note (wajib). */
	public function close_item()
	{
		$this->requireWrite();
		$no   = (string) $this->input->post('no_item');
		$note = trim((string) $this->input->post('note'));
		$this->beginAudit(__FUNCTION__, array(), array('outcome'));
		$fail = function ($msg, $outcome) {
			$this->completeAudit('Menutup item pengajuan rutin', 0, array('outcome' => $outcome));
			$this->jsonOut(array('save' => 0, 'msg' => $msg));
		};
		if ($note === '') {
			return $fail('Alasan wajib diisi untuk menutup item.', 'empty_note');
		}
		$item = periodik_is_item_no($no) ? $this->db->get_where('tr_pengajuan_rutin_detail', array('no_item' => $no))->row() : null;
		$head = $item ? $this->db->get_where('tr_pengajuan_rutin', array('no_doc' => $item->no_doc))->row() : null;
		if (!$item || !$head) {
			return $fail('Item tidak ditemukan.', 'not_found');
		}
		if (!$this->isAdmin() && (string) $head->departement !== (string) $this->get_user_department()) {
			return $fail('Anda tidak memiliki akses ke data departemen ini.', 'unauthorized');
		}
		if ((int) $item->item_status !== 8 || !empty($item->diajukan_ulang_sebagai) || !empty($item->closed_at)) {
			return $fail('Hanya item yang ditolak dan belum diajukan ulang yang bisa ditutup.', 'not_closable');
		}
		try {
			$ok = (bool) $this->db->update('tr_pengajuan_rutin_detail',
				array('closed_note' => $note, 'closed_by' => $this->currentUserName(), 'closed_at' => date('Y-m-d H:i:s')), array('no_item' => $no));
		} catch (Throwable $e) {
			$ok = false;
		}
		if (!$ok) {
			return $fail('Please try again later !', 'write_failed');
		}
		$this->completeAudit('Menutup item pengajuan rutin', 1, array('outcome' => 'closed'));
		$this->jsonOut(array('save' => 1, 'no_item' => $no));
	}

	/* ------------------------------ Approval ------------------------------ */

	public function app_list()
	{
		$this->auth->restrict($this->approvalViewPermission);
		$this->beginAudit(__FUNCTION__, array(), array('outcome'));
		$batches = $this->Pengajuan_rutin_model->GetBatchesWithItems(null);
		$this->auditObserve($batches);
		$batches = is_array($batches) ? $batches : array();
		$this->template->set('batches', $batches);
		$this->template->set('names', $this->deptNames());
		$this->template->set('can_decide', has_permission($this->approvalManagePermission));
		$this->template->title('Approval Pengajuan Periodik');
		$this->template->render('app_list');
		$this->completeAudit('Membuka halaman approval pengajuan rutin', $this->auditQueriesOk ? 1 : 0, array(
			'outcome' => $this->auditQueriesOk ? 'rendered' : 'query_error'
		));
	}

	/** Form keputusan satu batch (per item). $id = id header. */
	public function proses($id = '')
	{
		$this->auth->restrict($this->approvalManagePermission);
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'outcome'));
		$data = $this->Pengajuan_rutin_model->GetDataPengajuanRutin($id);
		$this->auditObserve($data);
		$all = $data ? $this->Pengajuan_rutin_model->GetBatchesWithItems(null, $data->no_doc) : false;
		$this->auditObserve($all);
		$batch = is_array($all) && $data && isset($all[$data->no_doc]) ? $all[$data->no_doc] : null;
		if (!$batch || !in_array('W', array_column($batch['items'], 'status'), true)) {
			$this->template->set_message($batch ? 'Semua item batch ini sudah diputuskan.' : 'Invalid Data', 'error');
			$this->completeAudit('Membuka form approval pengajuan rutin', 0, array('id' => $id, 'outcome' => !$data ? 'not_found' : (!$this->auditQueriesOk ? 'query_error' : ($batch ? 'already_decided' : 'not_found'))));
			redirect('pengajuan_rutin/app_list');
			return;
		}
		$names = $this->deptNames();
		$chain = array();
		foreach ($batch['items'] as $it) {
			if ($it['ref'] !== '') {
				$chain[$it['no_item']] = $this->revisionChain($it['ref']);
			}
		}
		$this->template->set('batch', $batch);
		$this->template->set('dept_name', isset($names[$batch['dept']]) ? $names[$batch['dept']] : $batch['dept']);
		$this->template->set('chain', $chain);
		$this->template->title('Approval Pengajuan Periodik');
		$this->template->render('approval_form');
		$this->completeAudit('Membuka form approval pengajuan rutin', $this->auditQueriesOk ? 1 : 0, array(
			'id' => $id, 'no_doc' => $batch['no'], 'outcome' => $this->auditQueriesOk ? 'rendered' : 'query_error'
		));
	}

	/** Rantai revisi ke belakang: [{no, note}] dari penolakan terdekat. Maksimal 10 tingkat. */
	private function revisionChain($no)
	{
		$out = array();
		for ($g = 0; $no !== '' && $g < 10; $g++) {
			$row = $this->db->get_where('tr_pengajuan_rutin_detail', array('no_item' => $no))->row();
			if (!$row) {
				break;
			}
			$out[] = array('no' => $no, 'note' => (string) $row->item_reject_ket);
			$no = (string) $row->revisi_dari;
		}
		return $out;
	}

	/**
	 * Simpan keputusan per item. POST: id (batch), decision[no_item]=A|R, note[no_item], general_note.
	 * Keputusan bersifat final: item yang sudah diputuskan tidak diubah, batch tanpa item menunggu ditolak.
	 */
	public function save_decision()
	{
		$this->auth->restrict($this->approvalManagePermission);
		$id = (string) $this->input->post('id');
		$decision = (array) $this->input->post('decision');
		$note = (array) $this->input->post('note');
		$general = trim((string) $this->input->post('general_note'));
		$this->beginAudit(__FUNCTION__, array('id' => $id), array('id', 'no_doc', 'approved_count', 'rejected_count', 'outcome'));
		$fail = function ($msg, $outcome) use ($id) {
			$this->completeAudit('Menyimpan keputusan approval pengajuan rutin', 0, array('id' => $id, 'outcome' => $outcome));
			$this->jsonOut(array('save' => 0, 'msg' => $msg));
		};

		$header = $this->Pengajuan_rutin_model->GetDataPengajuanRutin($id);
		$all = $header ? $this->Pengajuan_rutin_model->GetBatchesWithItems(null, $header->no_doc) : false;
		$batch = is_array($all) && $header && isset($all[$header->no_doc]) ? $all[$header->no_doc] : null;
		if (!$batch) {
			return $fail('Batch tidak ditemukan.', 'not_found');
		}
		$waiting = array_filter($batch['items'], function ($i) { return $i['status'] === 'W'; });
		if (!$waiting) {
			return $fail('Keputusan batch ini sudah disimpan dan tidak bisa diubah.', 'already_decided');
		}

		$plan = array();
		$missing = array();
		foreach ($waiting as $it) {
			$key = $it['id'];
			$dec = isset($decision[$key]) && $decision[$key] === 'R' ? 'R' : 'A';
			$text = trim((string) ($note[$key] ?? ''));
			if ($dec === 'R' && $text === '' && $general === '') {
				$missing[] = $key;
			}
			$plan[] = array('it' => $it, 'dec' => $dec, 'note' => $text !== '' ? $text : $general);
		}
		if ($missing) {
			return $fail('Ada item yang di-reject tanpa alasan. Isi alasan per item atau alasan reject umum.', 'missing_reason');
		}

		$by = $this->currentUserName();
		$byId = method_exists($this->auth, 'user_id') ? (string) $this->auth->user_id() : ''; // kolom header varchar(10) berisi id user, seperti data yang sudah ada
		$now = date('Y-m-d H:i:s');
		if ($this->db->trans_begin() === false) {
			return $fail('Please try again later !', 'trans_begin_failure');
		}
		$ok = true;
		$approved = $rejected = 0;
		try {
		foreach ($plan as $p) {
			$upd = array('item_status' => $p['dec'] === 'A' ? 1 : 8, 'item_approved_by' => $by, 'item_approved_date' => $now,
				'item_reject_ket' => $p['dec'] === 'R' ? $p['note'] : null);
			if ($p['it']['no_item'] === $p['it']['no_batch']) { // batch lama: nomor item baru dibuat saat diputuskan
				$upd['no_item'] = periodik_next_number($this->db, 'PI');
				if ($upd['no_item'] === '') { $ok = false; break; }
			}
			$ok = (bool) $this->db->update('tr_pengajuan_rutin_detail', $upd, array('id' => $p['it']['id'], 'item_status' => 0)) && $ok;
			$ok = $ok && $this->db->affected_rows() === 1;
			$p['dec'] === 'A' ? $approved++ : $rejected++;
			if (!$ok) { break; }
		}
		if ($ok) {
			// header: status 1 bila ada item disetujui; seluruhnya ditolak -> sts_reject
			$anyApproved = $approved > 0 || in_array('A', array_column($batch['items'], 'status'), true);
			$ok = (bool) $this->db->update('tr_pengajuan_rutin', $anyApproved
				? array('status' => 1, 'sts_reject' => null, 'approved_by' => $byId, 'approved_date' => $now)
				: array('status' => 0, 'sts_reject' => 1, 'reject_ket' => $general !== '' ? $general : 'Seluruh item ditolak', 'reject_by' => $byId, 'reject_date' => $now),
				array('no_doc' => $batch['no']));
		}
		} catch (Throwable $e) {
			$this->db->trans_rollback();
			return $fail('Please try again later !', 'exception');
		}
		if (!$ok || $this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return $fail('Please try again later !', 'write_failed');
		}
		if ($this->db->trans_commit() === false) {
			return $fail('Please try again later !', 'commit_failed');
		}
		$letters = array_merge(array_column($plan, 'dec'), array_column(array_filter($batch['items'], function ($i) { return $i['status'] !== 'W'; }), 'status'));
		list($code, $label) = Periodik_logic::batch_status($letters);
		$this->completeAudit('Menyimpan keputusan approval pengajuan rutin', 1, array('id' => $id, 'no_doc' => $batch['no'], 'approved_count' => $approved, 'rejected_count' => $rejected, 'outcome' => 'decided'));
		$this->jsonOut(array('save' => 1, 'no_doc' => $batch['no'], 'status' => $label, 'approved' => $approved, 'rejected' => $rejected));
	}
}
