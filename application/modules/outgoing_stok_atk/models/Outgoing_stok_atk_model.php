<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Outgoing_stok_atk_model extends BF_Model
{

  protected $ENABLE_ADD;
  protected $ENABLE_MANAGE;
  protected $ENABLE_VIEW;
  protected $ENABLE_DELETE;

  protected $dbhris;

  public function __construct()
  {
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('Outgoing_Stok_ATK.Add');
    $this->ENABLE_MANAGE  = has_permission('Outgoing_Stok_ATK.Manage');
    $this->ENABLE_VIEW    = has_permission('Outgoing_Stok_ATK.View');
    $this->ENABLE_DELETE  = has_permission('Outgoing_Stok_ATK.Delete');

    $this->dbhris = $this->load->database('hris', true);
  }

  //request material add
  public function server_side_request_produksi()
  {
    $requestData  = $_REQUEST;
    $fetch      = $this->query_data_request_produksi(
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData    = $fetch['totalData'];
    $totalFiltered  = $fetch['totalFiltered'];
    $query      = $fetch['query'];

    $data  = array();
    $urut1  = 1;
    $urut2  = 0;
    $GET_STOCK = getStokBarang(1); //17 kode warehouse
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

      $stock = (!empty($GET_STOCK[$row['id']]['stok'])) ? $GET_STOCK[$row['id']]['stok'] : 0;

      $nestedData   = array();
      $nestedData[]  = "<div align='center'>" . $nomor . "
                        <input type='hidden' class='id' name='detailx[" . $nomor . "][id]' value='" . $row['id'] . "'>
                        <input type='hidden' class='nm_material' name='detailx[" . $nomor . "][nm_material]' value='" . strtoupper($row['stock_name']) . "'>
                        <input type='hidden' class='code_material' name='detailx[" . $nomor . "][code]' value='" . strtoupper($row['id_stock']) . "'>
                        <input type='hidden' class='packing' name='detailx[" . $nomor . "][unit_packing]' value='" . strtoupper($row['unit_packing']) . "'>
                        <input type='hidden' class='satuan' name='detailx[" . $nomor . "][unit_satuan]' value='" . strtoupper($row['unit_satuan']) . "'>
                        <input type='hidden' class='konversi' name='detailx[" . $nomor . "][konversi]' value='" . $row['konversi'] . "'>
                        <input type='hidden' class='nm_category' name='detailx[" . $nomor . "][nm_category]' value='" . strtoupper($row['nm_category']) . "'>
                        <input type='hidden' class='stock_qty' name='detailx[" . $nomor . "][stock_qty]' value='" . $stock . "'>
                      </div>";

      $nestedData[]  = "<div align='left'>" . strtoupper($row['id_stock']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['stock_name']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_category']) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($stock) . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['unit_satuan']) . "</div>";
      $nestedData[]  = "<div align='left'><input type='text' style='width:100%' name='detailx[" . $nomor . "][sudah_request]' data-no='" . $nomor . "'class='form-control input-sm text-center autoNumeric4 sudah_request'><script type='text/javascript'>$('.autoNumeric4').autoNumeric('init', {mDec: '4', aPad: false});;</script></div>";
      $nestedData[]  = "<div align='left'><input type='text' style='width:100%' name='detailx[" . $nomor . "][ket_request]' data-no='" . $nomor . "' class='form-control input-sm text-left ket_request'></div>";
      // if($row['konversi'] > 0){
      $nestedData[]  = "<div align='center'><button type='button' class='btn btn-primary btn-sm pindahkan' title='Pindahkan'><i class='fa fa-location-arrow'></i></button></div>";
      // }
      // else{
      //   $nestedData[]	= "<div align='center' class='text-red text-bold'>Konversi Nol</div>";
      // }
      $data[] = $nestedData;
      $urut1++;
      $urut2++;
    }

    $json_data = array(
      "draw"              => intval($requestData['draw']),
      "recordsTotal"      => intval($totalData),
      "recordsFiltered"   => intval($totalFiltered),
      "data"              => $data
    );

    echo json_encode($json_data);
  }

  public function query_data_request_produksi($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $sql = "SELECT 
            (@row:=@row+1) AS nomor,
              b.*,
              c.code AS unit_packing,
              d.code AS unit_satuan,
              z.nm_category
            FROM
              accessories b
              LEFT JOIN accessories_category z ON b.id_category = z.id
              LEFT JOIN ms_satuan c ON b.id_unit_gudang=c.id
              LEFT JOIN ms_satuan d ON b.id_unit=d.id,
              (SELECT @row:=0) r
            WHERE b.deleted_date IS NULL
            AND(
              b.id_stock LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR b.stock_name LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR z.nm_category LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
          ";

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'id_stock',
      2 => 'b.stock_name',
      3 => 'z.nm_category'
    );

    $sql .= " ORDER BY b.stock_name ASC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  //request material
  public function data_side_request_material()
  {
    $requestData = $_REQUEST;
    $search = isset($requestData['search']) && is_array($requestData['search']) ? ($requestData['search']['value'] ?? '') : '';
    $order = isset($requestData['order'][0]) && is_array($requestData['order'][0]) ? $requestData['order'][0] : array();
    $draw = isset($requestData['draw']) && is_scalar($requestData['draw']) ? (int) $requestData['draw'] : 0;
    try {
      $fetch = $this->get_query_json_request_material($search, $order['column'] ?? 1, $order['dir'] ?? 'desc',
        $requestData['start'] ?? 0, $requestData['length'] ?? 10);
      $rows = $fetch['query']->result_array();
      $details = $this->get_outgoing_item_details(array_column($rows, 'kode_trans'));
      $users = get_list_user();
      $data = array();
      foreach ($rows as $index => $row) {
        $this->dbhris->select('a.name as nm_dept, b.name as nm_comp');
        $this->dbhris->from('departments a');
        $this->dbhris->join('companies b', 'b.id = a.company_id', 'left');
        $this->dbhris->where('a.id', $row['id_dept']);
        $department = $this->dbhris->get()->row_array();
        $departmentName = !empty($department) ? strtoupper($department['nm_dept']) . ' - ' . strtoupper($department['nm_comp']) : '';
        $username = !empty($users[$row['created_by']]['nama']) ? $users[$row['created_by']]['nama'] : '-';
        $code = htmlspecialchars((string) $row['kode_trans'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $view = "<button type='button' data-kode_trans='" . $code . "' data-tanda='detail' class='btn btn-sm btn-info detail' title='Detail' data-role='qtip'><i class='fa fa-eye'></i></button>";
        $data[] = array(
          "<div align='center'>" . ($fetch['start'] + $index + 1) . "</div>",
          "<div align='center'>" . htmlspecialchars(strtoupper((string) $row['kode_trans']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</div>",
          "<div align='center'>" . date('d-M-Y', strtotime($row['tanggal'])) . "</div>",
          "<div align='left'>" . htmlspecialchars($departmentName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</div>",
          $this->render_outgoing_items($details[$row['kode_trans']] ?? array()),
          "<div align='center'>" . number_format($row['qty_unit']) . "</div>",
          "<div align='left'>" . htmlspecialchars((string) $row['pic'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</div>",
          "<div align='left'>" . htmlspecialchars($username, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</div>",
          "<div align='center'>" . date('d-M-Y H:i:s', strtotime($row['created_date'])) . "</div>",
          "<div align='center'>" . $view . "</div>"
        );
      }
      echo json_encode(array('draw' => $draw, 'recordsTotal' => $fetch['totalData'],
        'recordsFiltered' => $fetch['totalFiltered'], 'data' => $data));
    } catch (Throwable $e) {
      log_message('error', 'Outgoing ATK index could not be loaded');
      echo json_encode(array('draw' => $draw, 'recordsTotal' => 0, 'recordsFiltered' => 0,
        'data' => array(), 'error' => 'Daftar outgoing gagal dimuat. Silakan coba lagi.'));
    }
  }

  private function outgoing_index_query($sql, $bindings = array())
  {
    $result = $this->db->query($sql, $bindings);
    $error = $this->db->error();
    if ($result === false || !empty($error['code'])) { throw new RuntimeException('Outgoing index query failed'); }
    return $result;
  }

  public function get_query_json_request_material($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {
    $search = is_scalar($like_value) ? (string) $like_value : '';
    $columns = array(0 => 'a.id', 1 => 'a.kode_trans', 2 => 'a.tanggal', 3 => 'a.id_dept',
      5 => 'a.jumlah_mat', 6 => 'a.pic', 7 => 'a.created_by', 8 => 'a.created_date');
    $column = is_scalar($column_order) && ctype_digit((string) $column_order) ? (int) $column_order : 1;
    $sort = $columns[$column] ?? $columns[1];
    $direction = is_string($column_dir) && strtolower($column_dir) === 'asc' ? 'ASC' : 'DESC';
    $start = is_scalar($limit_start) && ctype_digit((string) $limit_start) ? max(0, (int) $limit_start) : 0;
    $length = is_scalar($limit_length) && ctype_digit((string) $limit_length) && (int) $limit_length > 0 ? min(150, (int) $limit_length) : 10;
    $from = " FROM warehouse_adjustment a
      LEFT JOIN warehouse c ON a.id_gudang_ke = c.id
      LEFT JOIN warehouse d ON a.id_gudang_dari = d.id
      WHERE a.deleted_date IS NULL AND a.category = ?";
    $bindings = array('outgoing stok');
    $total = $this->outgoing_index_query('SELECT COUNT(DISTINCT a.kode_trans) AS total' . $from, $bindings)->row_array();
    $filtered = $total;
    if ($search !== '') {
      $pattern = '%' . str_replace(array('!', '%', '_'), array('!!', '!%', '!_'), $search) . '%';
      $from .= " AND (a.kode_trans LIKE ? ESCAPE '!' OR d.nm_gudang LIKE ? ESCAPE '!' OR c.nm_gudang LIKE ? ESCAPE '!'
        OR EXISTS (SELECT 1 FROM warehouse_adjustment_detail item
          INNER JOIN accessories material ON material.id = item.id_material
          WHERE item.kode_trans = a.kode_trans AND (material.stock_name LIKE ? ESCAPE '!' OR material.id_stock LIKE ? ESCAPE '!')))";
      $bindings = array_merge($bindings, array_fill(0, 5, $pattern));
      $filtered = $this->outgoing_index_query('SELECT COUNT(DISTINCT a.kode_trans) AS total' . $from, $bindings)->row_array();
    }
    $sql = 'SELECT a.kode_trans, a.pic, a.id_dept, a.tanggal, a.jumlah_mat AS qty_unit,
      a.created_by, a.created_date, a.checked AS sts_confirm' . $from
      . ' GROUP BY a.kode_trans ORDER BY ' . $sort . ' ' . $direction . ', a.id DESC LIMIT ' . $start . ', ' . $length;
    return array('totalData' => (int) $total['total'], 'totalFiltered' => (int) $filtered['total'],
      'start' => $start, 'query' => $this->outgoing_index_query($sql, $bindings));
  }

  public function get_outgoing_item_details($transactionCodes)
  {
    if (!$transactionCodes) { return array(); }
    $transactionCodes = array_values(array_unique($transactionCodes));
    $placeholders = implode(',', array_fill(0, count($transactionCodes), '?'));
    $sql = 'SELECT detail.kode_trans, detail.id_material, detail.qty_order, material.stock_name, unit.code AS unit_code
      FROM warehouse_adjustment_detail detail
      LEFT JOIN accessories material ON material.id = detail.id_material
      LEFT JOIN ms_satuan unit ON unit.id = material.id_unit AND unit.deleted_date IS NULL
      WHERE detail.kode_trans IN (' . $placeholders . ') ORDER BY detail.id ASC';
    $rows = $this->outgoing_index_query($sql, $transactionCodes)->result_array();
    $details = array();
    foreach ($rows as $row) { $details[$row['kode_trans']][] = $row; }
    return $details;
  }

  public function get_export_rows($search = '', $column = 1, $direction = 'desc')
  {
    $debugSettings = array();
    foreach (array($this->db, $this->dbhris) as $database) {
      if (property_exists($database, 'db_debug')) {
        $debugSettings[] = array($database, $database->db_debug);
        $database->db_debug = false;
      }
    }
    try {
      yield from $this->export_rows($search, $column, $direction);
    } finally {
      foreach ($debugSettings as $setting) { $setting[0]->db_debug = $setting[1]; }
    }
  }

  private function export_rows($search, $column, $direction)
  {
    $users = get_list_user();
    $departments = array();
    $start = 0;
    do {
      $fetch = $this->get_query_json_request_material($search, $column, $direction, $start, 150);
      $headers = $fetch['query']->result_array();
      $details = $this->get_outgoing_item_details(array_column($headers, 'kode_trans'));
      foreach ($headers as $header) {
        $departmentId = $header['id_dept'];
        if (!array_key_exists($departmentId, $departments)) {
          $query = $this->dbhris->select('a.name as nm_dept, b.name as nm_comp')
            ->from('departments a')->join('companies b', 'b.id = a.company_id', 'left')
            ->where('a.id', $departmentId)->get();
          $error = method_exists($this->dbhris, 'error') ? $this->dbhris->error() : array();
          if ($query === false || !empty($error['code'])) { throw new RuntimeException('Outgoing export department query failed'); }
          $department = $query->row_array();
          $departments[$departmentId] = $department ? strtoupper($department['nm_dept']) . ' - ' . strtoupper($department['nm_comp']) : '';
        }
        $items = $details[$header['kode_trans']] ?? array();
        if (!$items) { $items = array(null); }
        foreach ($items as $item) {
          yield array(
            'kode_trans' => strtoupper($header['kode_trans']),
            'tanggal' => date('d-M-Y', strtotime($header['tanggal'])),
            'department' => $departments[$departmentId],
            'name' => $item === null ? 'Tidak ada detail barang' :
              (($item['stock_name'] ?? '') !== '' ? $item['stock_name'] : 'Barang tidak tersedia (ID: ' . $item['id_material'] . ')'),
            'qty' => $item === null ? null : (float) $item['qty_order'],
            'unit' => !empty($item['unit_code']) ? $item['unit_code'] : '-',
            'pic' => (string) $header['pic'],
            'by' => !empty($users[$header['created_by']]['nama']) ? $users[$header['created_by']]['nama'] : '-',
            'dated' => date('d-M-Y H:i:s', strtotime($header['created_date']))
          );
        }
      }
      $start += count($headers);
    } while ($headers && $start < $fetch['totalFiltered']);
  }

  private function render_outgoing_items($details)
  {
    if (!$details) { return '<div class="outgoing-items text-muted">Tidak ada detail barang</div>'; }
    $lines = array();
    foreach ($details as $detail) {
      $name = isset($detail['stock_name']) && $detail['stock_name'] !== '' ? $detail['stock_name'] : 'Barang tidak tersedia (ID: ' . $detail['id_material'] . ')';
      $unit = isset($detail['unit_code']) && $detail['unit_code'] !== '' ? $detail['unit_code'] : '-';
      $lines[] = '<div class="outgoing-item">' . htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        . ' &mdash; <span class="outgoing-item-quantity">' . number_format((float) $detail['qty_order'], 2, ',', '.') . ' '
        . htmlspecialchars($unit, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span></div>';
    }
    return '<div class="outgoing-items">' . implode('', $lines) . '</div>';
  }
}
