<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Ipp_custom_model extends BF_Model
{

	public function __construct()
	{
		parent::__construct();

		$this->ENABLE_ADD     = has_permission('IPP_Custom.Add');
		$this->ENABLE_MANAGE  = has_permission('IPP_Custom.Manage');
		$this->ENABLE_VIEW    = has_permission('IPP_Custom.View');
		$this->ENABLE_DELETE  = has_permission('IPP_Custom.Delete');
	}

	public function get_data($table, $where_field = '', $where_value = '')
	{
		if ($where_field != '' && $where_value != '') {
			$query = $this->db->get_where($table, array($where_field => $where_value));
		} else {
			$query = $this->db->get($table);
		}

		return $query->result();
	}

	public function get_data_where_array($table, $where)
	{
		if (!empty($where)) {
			$query = $this->db->get_where($table, $where);
		} else {
			$query = $this->db->get($table);
		}

		return $query->result();
	}

	public function get_data_group($table, $where_field = '', $where_value = '', $where_group = '')
	{
		if ($where_field != '' && $where_value != '') {
			$query = $this->db->group_by($where_group)->get_where($table, array($where_field => $where_value));
		} else {
			$query = $this->db->get($table);
		}

		return $query->result();
	}

	public function get_json_ipp()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_ipp(
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalData			= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query					= $fetch['query'];

		$data	= array();
		$urut1  = 1;
		$urut2  = 0;
		foreach ($query->result_array() as $row) {
			$total_data     = $totalData;
			$start_dari     = $requestData['start'];
			$asc_desc       = $requestData['order'][0]['dir'];
			if ($asc_desc == 'asc') {
				$nomor = $urut1 + $start_dari;
			}
			if ($asc_desc == 'desc') {
				$nomor = ($total_data - $start_dari) - $urut2;
			}

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['no_ipp'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['nm_customer'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['project'])) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper(strtolower($row['rev'])) . "</div>";

			$last_create = (!empty($row['updated_by'])) ? $row['updated_by'] : $row['created_by'];
			$nestedData[]	= "<div align='left'>" . strtoupper(get_name('users', 'nm_lengkap', 'id_user', $last_create)) . "</div>";

			$last_date = (!empty($row['updated_date'])) ? $row['updated_date'] : $row['created_date'];
			$nestedData[]	= "<div align='center'>" . date('d-M-Y H:i', strtotime($last_date)) . "</div>";

			$status = "<span class='badge bg-blue'>Waiting Submit</span>";
			if ($row['ajukan_sts'] == 'Y') {
				$status = "<span class='badge bg-yellow'>Waiting Approval</span>";
			}
			if ($row['ajukan_sts'] == 'A') {
				$status = "<span class='badge bg-green'>Approved</span>";
			}

			$nestedData[]	= "<div align='center'>" . $status . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['ajukan_sts_reason'] . "</div>";

			$view	= "";
			$edit	= "";
			$delete	= "";
			$approve	= "";

			$view		= "<a href='" . site_url($this->uri->segment(1)) . '/add/' . $row['id'] . "/view' class='btn btn-sm btn-warning' title='View' data-role='qtip'><i class='fa fa-eye'></i></a>";
			if ($row['ajukan_sts'] == 'N') {
				if ($this->ENABLE_MANAGE) {
					$edit		= "&nbsp;<a href='" . site_url($this->uri->segment(1)) . '/add/' . $row['id'] . "' class='btn btn-sm btn-primary' title='Edit' data-role='qtip'><i class='fa fa-edit'></i></a>";
					$approve	= "&nbsp;<button type='button' class='btn btn-sm btn-success ajukan' title='Mengajukan' data-id='" . $row['id'] . "'><i class='fa fa-check'></i></button>";
				}
				if ($this->ENABLE_DELETE) {
					$delete		= "&nbsp;<button type='button' class='btn btn-sm btn-danger delete' title='Delete' data-id='" . $row['id'] . "'><i class='fa fa-trash'></i></button>";
				}
			}
			$nestedData[]	= "	<div align='left'>
								" . $view . "
								" . $edit . "
								" . $approve . "
								" . $delete . "
								</div>";
			$data[] = $nestedData;
			$urut1++;
			$urut2++;
		}

		$json_data = array(
			"draw"            	=> intval($requestData['draw']),
			"recordsTotal"    	=> intval($totalData),
			"recordsFiltered" 	=> intval($totalFiltered),
			"data"            	=> $data
		);

		echo json_encode($json_data);
	}

	public function get_query_json_ipp($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$sql = "SELECT
					(@row:=@row+1) AS nomor,
					a.*,
					b.nm_customer AS nm_customer
				FROM
					custom_ipp a
					LEFT JOIN customer b ON a.id_customer=b.id_customer,
					(SELECT @row:=0) r
				WHERE 1=1 AND a.deleted_date IS NULL AND 
					(
						b.nm_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR a.no_ipp LIKE '%" . $this->db->escape_like_str($like_value) . "%'
						OR a.project LIKE '%" . $this->db->escape_like_str($like_value) . "%'
					)
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_ipp',
			2 => 'b.nm_customer',
			3 => 'project',
			4 => 'rev'
		);

		$sql .= " ORDER BY a.id DESC,  " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}
}
