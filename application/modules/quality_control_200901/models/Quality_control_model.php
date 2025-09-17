<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Quality_control_model extends BF_Model{

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

    public function get_json_qc(){
      $controller			= ucfirst(strtolower($this->uri->segment(1)));
      // $Arr_Akses			= getAcccesmenu($controller);
      $requestData		= $_REQUEST;
      $fetch					= $this->get_query_json_qc(
        $requestData['costcenter'],
        $requestData['daycode'],
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
      foreach($query->result_array() as $row){
        $total_data     = $totalData;
        $start_dari     = $requestData['start'];
        $asc_desc       = $requestData['order'][0]['dir'];
        if($asc_desc == 'asc'){
          $nomor = $urut1 + $start_dari;
        }
        if($asc_desc == 'desc'){
          $nomor = ($total_data - $start_dari) - $urut2;
        }

        $nestedData 	= array();
        $nestedData[]	= "<div align='center'><center><input type='checkbox' name='check[".$nomor."]' class='chk_personal' value='".$row['id']."' ></center></div>";
        $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nama_costcenter']))."</div>";
        $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nama']))."</div>";
        $nestedData[]	= "<div align='center'>".strtoupper(strtolower($row['code']))."</div>";
        $remark       = (!empty($row['remarks']))?$row['remarks']:'-';
        $nestedData[]	= "<div align='left'>".ucfirst(strtolower($remark))."</div>";
        //<button type='button' class='btn btn-sm btn-danger reject' title='Reject Data' data-tanda='reject' data-id='".$row['id']."'>Reject</button>
        $nestedData[]	= "<div align='left'>
                          <button type='button' class='btn btn-sm btn-success approve' title='Approve Data' data-tanda='oke' data-id='".$row['id']."'>Oke</button>

                          </div>";
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

    public function get_query_json_qc($costcenter, $daycode, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

      $costcenter_where = "";
      if($costcenter != '0'){
      $costcenter_where = " AND b.id_costcenter = '".$costcenter."'";
      }

      $daycode_where = "";
      if($daycode != ''){
      $daycode_where = "AND a.code LIKE '%".$this->db->escape_like_str($daycode)."%'";
      }
      // a.id_produksi LIKE '%".$this->db->escape_like_str($like_value)."%'
      // OR a.id_product LIKE '%".$this->db->escape_like_str($like_value)."%'
      // OR b.id_costcenter LIKE '%".$this->db->escape_like_str($like_value)."%'
      $sql = "
            SELECT
              (@row:=@row+1) AS nomor,
              a.id,
              a.id_produksi,
              a.tanggal_produksi,
              b.id_costcenter,
              a.id_product,
              a.`code`,
              a.remarks,
              a.ket,
              c.nama,
              d.nama_costcenter
            FROM
              report_produksi_daily_detail a
              LEFT JOIN report_produksi_daily_header b ON a.id_produksi = b.id_produksi
              LEFT JOIN ms_inventory_category2 c ON a.id_product = c.id_category2
              LEFT JOIN ms_costcenter d ON b.id_costcenter = d.id_costcenter,
              (SELECT @row:=0) r
            WHERE
              a.id_produksi_h = b.id_produksi_h
              AND a.ket = 'not yet'
              AND a.id_product <> '0'
              AND b.id_costcenter <> 'CC2000001'
              ".$costcenter_where."
              ".$daycode_where."
              ";
      // echo $sql; exit;

      $data['totalData'] = $this->db->query($sql)->num_rows();
      $data['totalFiltered'] = $this->db->query($sql)->num_rows();
      $columns_order_by = array(
        0 => 'nomor',
        1 => 'id_costcenter',
        2 => 'id_product',
        3 => 'code',
        4 => 'remarks'
      );

      $sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
      $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

      $data['query'] = $this->db->query($sql);
      return $data;
    }

    //FINAL
    public function get_json_qc_final(){
      $controller			= ucfirst(strtolower($this->uri->segment(1)));
      // $Arr_Akses			= getAcccesmenu($controller);
      $requestData		= $_REQUEST;
      $fetch					= $this->get_query_json_qc_final(
        $requestData['costcenter'],
        $requestData['daycode'],
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
      foreach($query->result_array() as $row){
        $total_data     = $totalData;
        $start_dari     = $requestData['start'];
        $asc_desc       = $requestData['order'][0]['dir'];
        if($asc_desc == 'asc'){
          $nomor = $urut1 + $start_dari;
        }
        if($asc_desc == 'desc'){
          $nomor = ($total_data - $start_dari) - $urut2;
        }

        $nestedData 	= array();
        $nestedData[]	= "<div align='center'>".$nomor."</div>";
        $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nama']))."</div>";
        $nestedData[]	= "<div align='center'>".strtoupper(strtolower($row['code']))."</div>";
        $remark = (!empty($row['remarks']))?$row['remarks']:'-';
        $nestedData[]	= "<div align='left'>".ucfirst(strtolower($remark))."</div>";
        //  <button type='button' class='btn btn-sm btn-danger reject' title='Reject Data' data-tanda='reject' data-id='".$row['id']."'>Reject</button>
        $nestedData[]	= "<div align='left'>
                          <button type='button' class='btn btn-sm btn-success approve' title='Approve Data' data-tanda='oke' data-id='".$row['id']."'>Oke</button>

                          </div>";
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

    public function get_query_json_qc_final($costcenter, $daycode, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

      $costcenter_where = "";
      if($costcenter != '0'){
      $costcenter_where = " AND a.id_product = '".$costcenter."'";
      }

      $daycode_where = "";
      if($daycode != ''){
      $daycode_where = "AND a.code LIKE '%".$this->db->escape_like_str($daycode)."%'";
      }
      // a.id_produksi LIKE '%".$this->db->escape_like_str($like_value)."%'
      // OR a.id_product LIKE '%".$this->db->escape_like_str($like_value)."%'
      // OR b.id_costcenter LIKE '%".$this->db->escape_like_str($like_value)."%'
      $sql = "
            SELECT
              (@row:=@row+1) AS nomor,
              a.id,
              a.id_produksi,
              a.tanggal_produksi,
              b.id_costcenter,
              a.id_product,
              a.`code`,
              a.remarks,
              a.ket,
              c.nama,
              d.nama_costcenter
            FROM
              report_produksi_daily_detail a
              LEFT JOIN report_produksi_daily_header b ON a.id_produksi = b.id_produksi
              LEFT JOIN ms_inventory_category2 c ON a.id_product = c.id_category2
              LEFT JOIN ms_costcenter d ON b.id_costcenter = d.id_costcenter,
              (SELECT @row:=0) r
            WHERE
              a.ket = 'not yet'
              AND a.id_product <> '0'
              AND b.id_costcenter = 'CC2000001'
              ".$costcenter_where."
              ".$daycode_where."
              ";
      // echo $sql; exit;

      $data['totalData'] = $this->db->query($sql)->num_rows();
      $data['totalFiltered'] = $this->db->query($sql)->num_rows();
      $columns_order_by = array(
        0 => 'nomor',
        1 => 'id_costcenter',
        2 => 'id_product',
        3 => 'code',
        4 => 'remarks'
      );

      $sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
      $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

      $data['query'] = $this->db->query($sql);
      return $data;
    }


}
