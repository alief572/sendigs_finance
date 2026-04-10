<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is model class for table "Purchase Request"
 */

class Expense_model extends BF_Model
{
	/**
	 * @var string  User Table Name
	 */
	protected $table_name = 'tr_expense';
	protected $key        = 'id';

	/**
	 * @var string Field name to use for the created time column in the DB table
	 * if $set_created is enabled.
	 */
	protected $created_field = 'created_on';

	/**
	 * @var string Field name to use for the modified time column in the DB
	 * table if $set_modified is enabled.
	 */
	protected $modified_field = 'modified_on';

	/**
	 * @var bool Set the created time automatically on a new record (if true)
	 */
	protected $set_created = true;

	/**
	 * @var bool Set the modified time automatically on editing a record (if true)
	 */
	protected $set_modified = true;

	/**
	 * @var string The type of date/time field used for $created_field and $modified_field.
	 * Valid values are 'int', 'datetime', 'date'.
	 */
	protected $date_format = 'datetime';

	/**
	 * @var bool If true, will log user id in $created_by_field, $modified_by_field,
	 * and $deleted_by_field.
	 */
	protected $log_user = true;

	/**
	 * Function construct used to load some library, do some actions, etc.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->hr = $this->load->database('hris', true);
	}

	// list data kasbon
	public function GetListDataKasbon($where = '')
	{
		$this->db->select('a.*, b.nm_lengkap as nmuser');
		$this->db->from('tr_kasbon a');
		$this->db->join('users b', 'a.nama = b.username', 'left');
		// $this->db->join('employee c', 'b.employee_id = c.id');
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	// get data kasbon
	public function GetDataKasbon($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_kasbon a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	// list data transport request
	public function GetListDataTransportRequest($id_user = '', $where = '')
	{
		$this->db->select('a.*, a.created_by as nmuser');
		$this->db->from('tr_transport_req a');
		if ($id_user != '') $this->db->where('a.created_by', $id_user);
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	// list data transport request
	public function GetListDataTransportRequestAll($id_user = '', $where = '')
	{
		$this->db->select('a.*, b.nm_lengkap as nmuser,c.tgl_doc as tgl_trans,c.keperluan');
		$this->db->from('tr_transport_req a');
		$this->db->join('tr_transport c', 'a.no_doc=c.no_req', 'left');
		$this->db->join('users b', 'b.id_user = a.created_by', 'left');
		if ($id_user !== '') $this->db->where('a.created_by', $id_user);
		if ($where !== '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	// get data transport req
	public function GetDataTransportReq($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_transport_req a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	// get data transport req detail
	public function GetDataTransportInReq($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_transport a');
		$this->db->where('a.no_req', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}


	// list data transport
	public function GetListDatatransport($id_user = '')
	{
		$this->db->select('a.*');
		$this->db->from('tr_transport a');
		if ($id_user != '') $this->db->where('a.nama', $id_user);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	// get data transport
	public function GetDataTransport($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_transport a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	// list data
	public function GetListData($where = '')
	{
		$this->db->select('a.*, b.username as nmuser, c.username as nmapproval');
		$this->db->from($this->table_name . ' a');
		$this->db->join('users b', 'a.nama=b.username', 'left');
		$this->db->join('users c', 'a.approval=c.username', 'left');
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	public function GetListDataAll($where = '')
	{
		$this->db->select('a.*, b.username as nmuser, c.username as nmapproval,d.tanggal,d.deskripsi');
		$this->db->from($this->table_name . ' a');
		$this->db->join('users b', 'a.nama=b.username', 'left');
		$this->db->join('users c', 'a.approval=c.username', 'left');
		$this->db->join('tr_expense_detail d', 'a.no_doc=d.no_doc', 'left');
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	// get data
	public function GetDataHeader($id)
	{
		$this->db->select('a.*');
		$this->db->from($this->table_name . ' a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function GetDataDetail($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_expense_detail a');
		$this->db->where('a.no_doc', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function GetDetailPurchaseRequest($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_expense_detail a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function GetBudget($coa, $tahun)
	{
		$this->db->select('a.*');
		$this->db->from('ms_budget a');
		$this->db->where('a.coa', $coa);
		$this->db->where('a.tahun', $tahun);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function GetBudgetDivisi($type, $divisi, $tahun)
	{
		$this->db->select('a.*');
		$this->db->from('ms_coa_budget a');
		$this->db->where('a.coa', $type);
		$this->db->where('a.divisi', $divisi);
		$this->db->where('a.tahun', $tahun);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function Update_budget($coa, $tgl, $nilai, $divisi, $nilai_pr = 0)
	{
		$bulan = date("n", strtotime($tgl));
		$tahun = date("Y", strtotime($tgl));

		$this->db->select('a.*');
		$this->db->from('ms_coa_budget a');
		$this->db->where('a.coa', $coa);
		$this->db->where('a.tahun', $tahun);
		$this->db->where('a.divisi', $divisi);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			$data = $query->row();
			$terpakai_bulan = $data->{"terpakai_bulan_" . $bulan};
			$terpakai = $data->terpakai;
			$sisa = $data->sisa;
			$idbudget = $data->id;
			$upd_terpakai_bulan = ($terpakai_bulan + $nilai - $nilai_pr);
			$upd_terpakai = ($terpakai + $nilai - $nilai_pr);
			$upd_sisa = ($sisa - $nilai + $nilai_pr);
			$this->db->query("update ms_coa_budget set terpakai_bulan_" . $bulan . "=" . $upd_terpakai_bulan . ", terpakai=" . $upd_terpakai . ", sisa=" . $upd_sisa . " where id=" . $idbudget . " and coa='" . $coa . "' and tahun='" . $tahun . "'");
			return true;
		} else {
			return false;
		}
	}

	public function getArray($table, $WHERE = array(), $keyArr = '', $valArr = '')
	{
		if ($WHERE) {
			$query = $this->db->get_where($table, $WHERE);
		} else {
			$query = $this->db->get($table);
		}
		$dataArr	= $query->result_array();

		if (!empty($keyArr)) {
			$Arr_Data	= array();
			foreach ($dataArr as $key => $val) {
				$nilai_id					= $val[$keyArr];
				if (!empty($valArr)) {
					$nilai_val				= $val[$valArr];
					$Arr_Data[$nilai_id]	= $nilai_val;
				} else {
					$Arr_Data[$nilai_id]	= $val;
				}
			}

			return $Arr_Data;
		} else {
			return $dataArr;
		}
	}

	public function get_data_transport_input()
	{
		$draw = $this->input->post('draw');
		$length = $this->input->post('length');
		$start = $this->input->post('start');
		$search = $this->input->post('search');

		$this->db->select('a.id, a.no_doc, a.tgl_doc, a.nama, a.keperluan, a.nopol, a.status, (a.bensin + a.tol + a.parkir + a.lainnya) as ttl_transport, a.created_by as nmuser');
		$this->db->from('tr_transport a');
		$this->db->where('a.created_by', $this->auth->user_name());
		if (!empty($search['value'])) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search['value'], 'both');
			$this->db->or_like('a.tgl_doc', $search['value'], 'both');
			$this->db->or_like('a.nama', $search['value'], 'both');
			$this->db->group_end();
		}
		$this->db->group_by('a.id');
		$this->db->order_by('a.id', 'desc');

		$db_clone = clone $this->db;
		$count_all = $db_clone->count_all_results();

		$this->db->limit($length, $start);
		$get_data = $this->db->get()->result();

		$hasil = [];

		$no = (0 + $start);

		foreach ($get_data as $item) {
			$no++;

			$status = '<span class="badge bg-yellow">Baru</span>';
			if ($item->status == '1') {
				$status = '<span class="badge bg-green">Disetujui</span>';
			}
			if ($item->status == '2') {
				$status = '<span class="badge bg-green">Disetujui Management</span>';
			}
			if ($item->status == '3') {
				$status = '<span class="badge bg-blue">Selesai</span>';
			}
			if ($item->status == '9') {
				$status = '<span class="badge bg-red">Ditolak</span>';
			}

			$action = '';

			if (has_permission('Transportasi.View')) {
				$action .= '<a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="data_view(' . $item->id . ')"><i class="fa fa-eye"></i></a>';
			}

			if (has_permission('Transportasi.Manage') && $item->status == 0) {
				$action .= ' <a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" onclick="data_edit(' . $item->id . ')"><i class="fa fa-edit"></i></a>';
			}

			if (has_permission('Transportasi.Delete') && $item->status == 0) {
				$action .= ' <a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Hapus" onclick="data_delete(' . $item->id . ')"><i class="fa fa-trash"></i></a>';
			}

			$hasil[] = [
				'no' => $no,
				'id_pengajuan' => $item->no_doc,
				'tanggal' => date('d F Y', strtotime($item->tgl_doc)),
				'nama' => $item->nama,
				'keperluan' => $item->keperluan,
				'no_polisi' => $item->nopol,
				'total' => number_format($item->ttl_transport),
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

	public function get_data_transport_req_fin_list()
	{
		$post = $this->input->post();

		$viewPermission 	= 'Pengajuan_Transportasi_Approval.View';
		$addPermission  	= 'Pengajuan_Transportasi_Approval.Add';
		$managePermission = 'Pengajuan_Transportasi_Approval.Manage';
		$deletePermission = 'Pengajuan_Transportasi_Approval.Delete';

		$draw = $post['draw'];
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search'];

		$this->db->select('a.*, a.created_by as nmuser');
		$this->db->from('tr_transport_req a');
		// $this->db->where('a.created_by', $this->auth->user_name());
		$this->db->where('a.status', 0);
		if (!empty($search['value'])) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search['value'], 'both');
			$this->db->or_like('a.no_doc', $search['value'], 'both');
			$this->db->or_like('a.tgl_doc', $search['value'], 'both');
			$this->db->or_like('a.created_by', $search['value'], 'both');
			$this->db->group_end();
		}
		$this->db->order_by('a.no_doc', 'desc');

		$db_clone = clone $this->db;
		$count_all = $db_clone->count_all_results();

		$this->db->limit($length, $start);

		$get_data = $this->db->get()->result();

		$hasil = [];
		$no = (0 + $start);

		foreach ($get_data as $item) {
			$no++;

			$status = '<span class="badge bg-yellow">Baru</span>';
			if ($item->status == '1') {
				$status = '<span class="badge bg-green">Disetujui</span>';
			}
			if ($item->status == '2') {
				$status = '<span class="badge bg-green">Disetujui Management</span>';
			}
			if ($item->status == '3') {
				$status = '<span class="badge bg-primary">Selesai</span>';
			}
			if ($item->status == '9') {
				$status = '<span class="badge bg-red">Ditolak</span>';
			}

			$action = '';
			if (has_permission($viewPermission)) {
				$action .= ' <a class="btn btn-default btn-sm print" href="' . base_url('expense/transport_req_print/' . $item->id) . '" target="transport_req_print" title="Print"><i class="fa fa-print"></i> </a> <a class="btn btn-warning btn-sm view" href="' . base_url('expense/transport_req_view/' . $item->id . '/_fin') . '" title="View"><i class="fa fa-eye"></i></a>';
			}

			if (has_permission($managePermission) && $item->status == 0) {
				$action .= ' <a class="btn btn-success btn-sm approve" href="' . base_url('expense/transport_req_edit/' . $item->id . '/_fin') . '" title="Approve"><i class="fa fa-check-square-o"></i></a>';
			}

			$hasil[] = [
				'no' => $no,
				'no_transport' => $item->no_doc,
				'tanggal' => date('d F Y', strtotime($item->tgl_doc)),
				'nama' => $item->nmuser,
				'status' => $status,
				'action' => $action,
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

	public function get_data_transport_req()
	{
		$post = $this->input->post();

		$viewPermission 	= 'Pengajuan_Transportasi_Approval.View';
		$addPermission  	= 'Pengajuan_Transportasi_Approval.Add';
		$managePermission = 'Pengajuan_Transportasi_Approval.Manage';
		$deletePermission = 'Pengajuan_Transportasi_Approval.Delete';

		$draw = $post['draw'];
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search'];

		$this->db->select('a.*, a.created_by as nmuser');
		$this->db->from('tr_transport_req a');
		$this->db->where('a.created_by', $this->auth->user_name());
		if (!empty($search['value'])) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search['value'], 'both');
			$this->db->or_like('a.no_doc', $search['value'], 'both');
			$this->db->or_like('a.tgl_doc', $search['value'], 'both');
			$this->db->or_like('a.created_by', $search['value'], 'both');
			$this->db->group_end();
		}

		$db_clone = clone $this->db;
		$count_all = $db_clone->count_all_results();

		$this->db->limit($length, $start);

		$get_data = $this->db->get()->result();

		$hasil = [];
		$no = (0 + $start);

		foreach ($get_data as $item) {
			$no++;

			$status = '<span class="badge bg-yellow">Baru</span>';
			if ($item->status == '1') {
				$status = '<span class="badge bg-green">Disetujui</span>';
			}
			if ($item->status == '2') {
				$status = '<span class="badge bg-green">Disetujui Management</span>';
			}
			if ($item->status == '3') {
				$status = '<span class="badge bg-primary">Selesai</span>';
			}
			if ($item->status == '9') {
				$status = '<span class="badge bg-red">Ditolak</span>';
			}

			$action = '';
			if (has_permission($viewPermission)) {
				$action .= ' <a class="btn btn-default btn-sm print" href="' . base_url('expense/transport_req_print/' . $item->id) . '" target="transport_req_print" title="Print"><i class="fa fa-print"></i> </a> <a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="data_view(' . $item->id . ')"><i class="fa fa-eye"></i></a>';
			}

			if (has_permission($managePermission) && ($item->status == 0 || $item->status == 9)) {
				$action .= ' <a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" onclick="data_edit(' . $item->id . ')"><i class="fa fa-edit"></i></a>';
			}

			if (has_permission($deletePermission) && ($item->status == 0 || $item->status == 9)) {
				$action .= ' <a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Hapus" onclick="data_delete(' . $item->id . ')"><i class="fa fa-trash"></i></a>';
			}

			$hasil[] = [
				'no' => $no,
				'no_transport' => $item->no_doc,
				'tanggal' => date('d F Y', strtotime($item->tgl_doc)),
				'nama' => $item->nmuser,
				'total' => number_format($item->jumlah_expense),
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

	public function get_data_transport_req_all()
	{
		$post = $this->input->post();

		$draw = $post['draw'];
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search'];
		$order = $post['order'];

		$this->db->select('a.id, a.no_doc, a.tgl_doc, a.date1, a.date2, a.jumlah_expense, a.status, a.nama, a.approved_on, a.status');
		$this->db->from('tr_transport_req a');
		if (!empty($search['value'])) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search['value'], 'both');
			$this->db->or_like('a.tgl_doc', $search['value'], 'both');
			$this->db->or_like('a.nama', $search['value'], 'both');
			$this->db->or_like('a.approved_on', $search['value'], 'both');
			$this->db->or_like('a.jumlah_expense', $search['value'], 'both');
			$this->db->group_end();
		}

		$db_clone = clone $this->db;
		$count_all = $db_clone->count_all_results();

		$column_order = [
			1 => 'no_doc',
			2 => 'tgl_doc',
			3 => 'nama',
			4 => 'approved_on',
			5 => 'jumlah_expense',
			6 => 'status'
		]; // List of columns to sort by
		$column_index = $order[0]['column']; // Column index from the order parameter
		$column_dir = $order[0]['dir']; // Ascending or Descending direction

		// Apply order by dynamically
		if (isset($column_order[$column_index])) {
			$this->db->order_by($column_order[$column_index], $column_dir);
		} else {
			$this->db->order_by('a.tgl_doc', 'desc');  // Default sorting
		}

		$this->db->limit($length, $start);


		$get_data = $this->db->get()->result();

		$hasil = [];
		$no = (0 + $start);

		foreach ($get_data as $item) {
			$no++;

			$tgl_doc = ($item->tgl_doc !== '0000-00-00') ? date('d F Y', strtotime($item->tgl_doc)) : '';
			$approval_date = ($item->approved_on !== null) ? date('d F Y H:i:s', strtotime($item->approved_on)) : '';

			$status = '<span class="badge bg-yellow">Baru</span>';
			if ($item->status == '1') {
				$status = '<span class="badge bg-blue">Disetujui</span>';
			}
			if ($item->status == '2') {
				$status = '<span class="badge bg-green">Selesai</span>';
			}
			if ($item->status == '3') {
				$status = '<span class="badge bg-green">Selesai</span>';
			}
			if ($item->status == '9') {
				$status = '<span class="badge bg-red">Ditolak</span>';
			}

			$action = '
				<a class="btn btn-default btn-sm print" href="' . base_url('expense/transport_req_print/' . $item->id) . '" target="transport_req_print" title="Print"><i class="fa fa-print"></i> </a>
				<a class="btn btn-warning btn-sm view" href="' . base_url('expense/transport_req_view/' . $item->id . '/_all') . '" title="View"><i class="fa fa-eye"></i></a>
			';

			$hasil[] = [
				'no' => $no,
				'no_doc' => $item->no_doc,
				'tanggal' => $tgl_doc,
				'nama' => $item->nama,
				'approval_date' => $approval_date,
				'total_transport' => number_format($item->jumlah_expense),
				'status' => $status,
				'action' => $action
			];
		}

		$response = [
			'draw' => intval($draw),
			'recordsTotal' => intval($count_all),
			'recordsFiltered' => intval($count_all),
			'data' => $hasil
		];

		echo json_encode($response);
	}

	public function GetListCoa($jenis_pengeluaran)
	{
		$this->db->select('a.coa');
		$this->db->from('coa_expense a');
		$this->db->where('a.jenis_pengeluaran', $jenis_pengeluaran);
		$query = $this->db->get()->row();

		if (!empty($query->coa)) {
			$this->db->select('a.no_perkiraan, a.nama');
			$this->db->from(DBACC . '.coa_master a');
			$this->db->where_in('a.no_perkiraan', explode(';', $query->coa));
			$result = $this->db->get()->result_array();
			return $result;
		} else {
			return [];
		}
	}

	public function getCoaDetail($no_coa)
	{
		$this->db->select('a.nama');
		$this->db->from(DBACC . '.coa_master a');
		$this->db->where('a.no_perkiraan', $no_coa);
		$query = $this->db->get()->row();
		return $query;
	}

	public function getCoaTransport()
	{
		$get_user = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();

		$this->db->select('a.coa');
		$this->db->from('coa_expense a');
		$this->db->where('a.jenis_pengeluaran', 'Transport');
		$get_kelompok_coa = $this->db->get()->row();

		if (!empty($get_kelompok_coa->coa)) {
			$this->db->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
			$this->db->from(DBACC . '.coa_master a');
			$this->db->where_in('a.no_perkiraan', explode(';', $get_kelompok_coa->coa));
			if ($this->auth->user_id() !== '7') {


				$target_departments = ['DEP005', 'DEP006', 'DEP011', 'DEP015', 'DEP016', 'DEP017', 'DEP018', 'DEP019', 'DEP022', 'DEP024', 'DEP007'];

				if (in_array($get_user->department_id, $target_departments)) {
					$this->db->like('a.no_perkiraan', '6202-', 'both');
				} else {
					$this->db->like('a.no_perkiraan', '5103-', 'both');
				}
			}
			$get_list_coa = $this->db->get()->result();

			return $get_list_coa;
		} else {
			return [];
		}
	}
}
