<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Stok_gudang_pusat_model extends BF_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	public function get_json_stock()
	{
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_stock(
			$requestData['date_filter'],
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
		$id_gudang = 1;
		$date_filter = $requestData['date_filter'];
		if (empty($date_filter)) {
			$GET_STOK = getStokMaterial($id_gudang);
		} else {
			$GET_STOK = getStokMaterialHistory($id_gudang, $date_filter);
		}
		$GET_UNIT = get_list_satuan();
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
			$nestedData[]	= "<div align='left'>" . $row['code'] . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper($row['nama']) . "</div>";

			$unit_packing = (!empty($GET_UNIT[$row['id_unit_packing']]['code'])) ? $GET_UNIT[$row['id_unit_packing']]['code'] : '';
			$nestedData[]	= "<div align='left'>" . strtoupper($unit_packing) . "</div>";

			$id_material = $row['code_lv4'];
			$stock_pack = (!empty($GET_STOK[$id_material]['stok_packing'])) ? $GET_STOK[$id_material]['stok_packing'] : 0;
			$stock      = (!empty($GET_STOK[$id_material]['stok'])) ? $GET_STOK[$id_material]['stok'] : 0;
			$booking    = (!empty($GET_STOK[$id_material]['booking'])) ? $GET_STOK[$id_material]['booking'] : 0;
			$available  = $stock - $booking;

			$nestedData[]	= "<div align='right'>" . number_format($stock_pack, 2) . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($row['konversi'], 2) . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($stock, 2) . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($booking, 2) . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($available, 2) . "</div>";
			$nestedData[]	= "<div align='center'></div>";
			$nestedData[]	= "<div align='center'>
			<button type='button' class='btn btn-sm btn-primary hist' data-gudang='" . $id_gudang . "' data-material='" . $id_material . "' title='History'><i class='fa fa-history'></i></button>
			<button type='button' class='btn btn-sm btn-success lot_detail' data-gudang='" . $id_gudang . "' data-material='" . $id_material . "' title='Lot Detail'><i class='fa fa-history'></i></button>
			</div>";

			// $nestedData[]	= "<div align='right'>".number_format(get_stock_material($row['code_material'], '2'), 2)."</div>";
			// $nestedData[]	= "<div align='right'>".number_format(get_stock_material_packing($row['code_material'], '2'), 2)."</div>";
			// $nestedData[]	= "<div align='center'><button type='button' class='btn btn-sm btn-success hist' data-gudang='2' data-material='".$row['code_material']."'><i class='fa fa-history'></i></button></div>";
			// $nestedData[]	= "<div align='right'>".number_format(get_stock_material($row['code_material'], '3'), 2)."</div>";
			// $nestedData[]	= "<div align='right'>".number_format(get_stock_material_packing($row['code_material'], '3'), 2)."</div>";
			// $nestedData[]	= "<div align='center'><button type='button' class='btn btn-sm btn-warning hist' data-gudang='3' data-material='".$row['code_material']."'><i class='fa fa-history'></i></button></div>";
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

	public function get_query_json_stock($date_filter, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.code_lv4,
              a.code,
              a.nama,
              a.id_unit_packing,
              a.id_unit,
              a.konversi
            FROM
              new_inventory_4 a,
              (SELECT @row:=0) r
            WHERE a.category='material' AND (
              a.code LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              )
          ";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'code',
			2 => 'nama'
		);

		$sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}
}
