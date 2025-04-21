<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Adjustment_stock_model extends BF_Model{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_data($table,$where_field='',$where_value=''){
  		if($where_field !='' && $where_value!=''){
  			$query = $this->db->get_where($table, array($where_field=>$where_value));
  		}else{
  			$query = $this->db->get($table);
  		}

  		return $query->result();
  	}

  	public function get_data_group($table,$where_field='',$where_value='',$where_group=''){
  		if($where_field !='' && $where_value!=''){
  			$query = $this->db->group_by($where_group)->get_where($table, array($where_field=>$where_value));

  		}else{
  			$query = $this->db->get($table);
  		}

  		return $query->result();
  	}

    public function get_data_json_adjustment(){
		
      $requestData	= $_REQUEST;
      $fetch			= $this->query_data_json_adjustment(
        $requestData['type'],
        $requestData['code_lv4'],
        $requestData['search']['value'],
        $requestData['order'][0]['column'],
        $requestData['order'][0]['dir'],
        $requestData['start'],
        $requestData['length']
      );
      $totalData		= $fetch['totalData'];
      $totalFiltered	= $fetch['totalFiltered'];
      $query			= $fetch['query'];
  
      $data	= array();
      $urut1  = 1;
          $urut2  = 0;
          $GET_USER = get_list_user();
      foreach($query->result_array() as $row)
      {
        $total_data     = $totalData;
              $start_dari     = $requestData['start'];
              $asc_desc       = $requestData['order'][0]['dir'];
              if($asc_desc == 'desc')
              {
                  $nomor = $urut1 + $start_dari;
              }
              if($asc_desc == 'asc')
              {
                  $nomor = ($total_data - $start_dari) - $urut2;
              }
  
        $nestedData 	= array();
        $nestedData[]	= "<div align='center'>".$nomor."</div>";
        $nestedData[]	= "<div align='center'>".$row['kode_trans']."</div>";
        $nestedData[]	= "<div align='center'>".strtoupper($row['adjustment_type'])."</div>";
        // $gudang_dari 	= (!empty($row['id_gudang_dari']))?get_name('warehouse', 'nm_gudang', 'id', $row['id_gudang_dari']):$row['kd_gudang_dari'];
        // $gudang_ke 		= (!empty($row['id_gudang_ke']))?get_name('warehouse', 'nm_gudang', 'id', $row['id_gudang_ke']):$row['kd_gudang_ke']." ".strtoupper($row['adjustment_type']);
        // $nestedData[]	= "<div align='left'>".$gudang_dari."</div>";
        // $nestedData[]	= "<div align='left'>".$gudang_ke."</div>";
        $nestedData[]	= "<div align='left'>".strtoupper($row['nm_material'])."</div>";
        $nestedData[]	= "<div align='right'>".number_format($row['jumlah_mat'],2)."</div>";
        $nestedData[]	= "<div align='left'>".strtoupper($row['pic'])."</div>";
        $nestedData[]	= "<div align='left'>".strtoupper($row['no_ba'])."</div>";
        $nestedData[]	= "<div align='left'>".strtoupper($row['note'])."</div>";
        $username = (!empty($GET_USER[$row['created_by']]['nama']))?$GET_USER[$row['created_by']]['nama']:'';
        $nestedData[]	= "<div align='left'>".$username."</div>";
        $nestedData[]	= "<div align='center'>".date('d-M-Y H:i:s', strtotime($row['created_date']))."</div>";
          // $detail	= "<button type='button' class='btn btn-sm btn-primary detailAjust' title='View Incoming' data-kode_trans='".$row['kode_trans']."' ><i class='fa fa-eye'></i></button>";
          // $print	= "&nbsp;<a href='".base_url('warehouse/print_incoming/'.$row['kode_trans'])."' target='_blank' class='btn btn-sm btn-warning' title='Print Incoming'><i class='fa fa-print'></i></a>";
          
          // $detail = "";
          // $print = "";
        // $nestedData[]	= "<div align='center'>
                                      // ".$detail."
                    // ".$print."
                    // </div>";
        $data[] = $nestedData;
              $urut1++;
              $urut2++;
      }
  
      $json_data = array(
        "draw"            	=> intval( $requestData['draw'] ),
        "recordsTotal"    	=> intval( $totalData ),
        "recordsFiltered" 	=> intval( $totalFiltered ),
        "data"            	=> $data
      );
  
      echo json_encode($json_data);
    }
  
    public function query_data_json_adjustment($type, $material, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){
      
      $where = "";
      if($type <> '0'){
        $where = " AND a.adjustment_type='".$type."' ";
      }
      
      $whereMaterial = "";
      if($material <> '0'){
        $whereMaterial = " AND b.id_material='".$material."' ";
      }
      
      $sql = "
        SELECT
          (@row:=@row+1) AS nomor,
          a.*,
          b.no_ba,
          b.nm_material
        FROM
          warehouse_adjustment a 
          LEFT JOIN warehouse_adjustment_detail b ON a.kode_trans=b.kode_trans,
          (SELECT @row:=0) r
          WHERE 1=1 
            AND a.category = 'adjustment stock' ".$where." ".$whereMaterial." AND a.deleted_date IS NULL
        AND(
          a.kode_trans LIKE '%".$this->db->escape_like_str($like_value)."%'
          OR a.kd_gudang_dari LIKE '%".$this->db->escape_like_str($like_value)."%'
          OR a.kd_gudang_ke LIKE '%".$this->db->escape_like_str($like_value)."%'
          OR a.pic LIKE '%".$this->db->escape_like_str($like_value)."%'
          OR b.no_ba LIKE '%".$this->db->escape_like_str($like_value)."%'
          OR b.nm_material LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
      ";
      // echo $sql; exit;
  
      $data['totalData'] = $this->db->query($sql)->num_rows();
      $data['totalFiltered'] = $this->db->query($sql)->num_rows();
      $columns_order_by = array(
        0 => 'nomor',
        1 => 'kode_trans',
        2 => 'adjustment_type',
        3 => 'id_gudang_dari',
        4 => 'id_gudang_ke',
        5 => 'nm_material',
        6 => 'jumlah_mat',
        7 => 'pic',
        8 => 'no_ba',
        9 => 'note',
        10 => 'created_by',
        11 => 'created_date'
      );
  
      $sql .= " ORDER BY a.id DESC, ".$columns_order_by[$column_order]." ".$column_dir." ";
      $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";
  
      $data['query'] = $this->db->query($sql);
      return $data;
    }



}
