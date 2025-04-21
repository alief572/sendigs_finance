<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2021, Harboens
 *
 * This is model class for table "Pr Selection"
 */

class Pr_selection_model extends BF_Model
{
    /**
     * @var string  User Table Name
     */
    protected $table_name = 'tr_pr_aset';
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
    /**
     * @var bool Enable/Disable soft deletes.
     * If false, the delete() method will perform a delete of that row.
     * If true, the value in $deleted_field will be set to 1.
     */
    protected $soft_deletes = false;
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
    }

	
	
	function GetSupplier($rfq){
		$this->db->select('a.*');
		$this->db->from('tran_material_rfq_header a ');
		$this->db->where('a.no_rfq',$rfq);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	function GetSupplierPo($nopo){
		$this->db->select('a.*');
		$this->db->from('tran_material_po_header a ');
		$this->db->where('a.no_po',$nopo);
		$this->db->order_by('a.no_po', 'asc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	public function index_pengajuan(){	
		
		$data = array(
			'title'			=> 'Indeks Of Table Pengajuan',
			'action'		=> 'index',
		);		
		$this->load->view('pr_selection/pengajuan',$data);
	}
	
	public function get_data_json_pengajuan(){
		

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_pengajuan(
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
		foreach($query->result_array() as $row)
		{
			$total_data     = $totalData;
            $start_dari     = $requestData['start'];
            $asc_desc       = $requestData['order'][0]['dir'];
            if($asc_desc == 'asc')
            {
                $nomor = $urut1 + $start_dari;
            }
            if($asc_desc == 'desc')
            {
                $nomor = ($total_data - $start_dari) - $urut2;
            }
			
			$list_supplier		= $this->db->query("SELECT nm_supplier FROM tran_material_rfq_header WHERE no_rfq='".$row['no_rfq']."' AND deleted='N'")->result_array();
			$arr_sup = array();
			foreach($list_supplier AS $val => $valx){
				$arr_sup[$val] = $valx['nm_supplier'];
			}
			$dt_sup	= implode("<br>", $arr_sup);
			
			$list_material		= $this->db->query("SELECT nm_material, qty, price_ref, price_ref_sup FROM tran_material_rfq_detail WHERE no_rfq='".$row['no_rfq']."' AND deleted='N' GROUP BY id_material")->result_array();
			$arr_mat = array();
			foreach($list_material AS $val => $valx){
				$arr_mat[$val] = $valx['nm_material'];
			}
			$dt_mat	= implode("<br>", $arr_mat);
			
			$arr_qty = array();
			foreach($list_material AS $val => $valx){
				$arr_qty[$val] = number_format($valx['qty']);
			}
			$dt_qty	= implode("<br>", $arr_qty);
			
			$arr_price = array();
			foreach($list_material AS $val => $valx){
				$arr_price[$val] = number_format($valx['price_ref']);
			}
			$dt_price	= implode("<br>", $arr_price);

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>".$nomor."</div>";
			$nestedData[]	= "<div align='center'>".$row['no_rfq']."</div>";
			$nestedData[]	= "<div align='left'>".$dt_sup."</div>";
			$nestedData[]	= "<div align='left'>".$dt_mat."</div>";
			$nestedData[]	= "<div align='right'>".$dt_price."</div>";
			$nestedData[]	= "<div align='right'>".$dt_qty."</div>";
			$nestedData[]	= "<div align='center'>".$row['created_by']."</div>";
			$nestedData[]	= "<div align='right'>".date('d F Y', strtotime($row['created_date']))."</div>";
			
			$nestedData[]	= "<div align='left'><span class='badge' style='background-color: ".color_status_purchase($row['sts_ajuan'])['color']."'>".color_status_purchase($row['sts_ajuan'])['status']."</span></div>";
				$ajukan	= "";
				$print	= "";
				$hasil_ajukan	= "";
				if($row['sts_ajuan']=='AJU' AND $row['sts_process']=='Y'){

					if($Arr_Akses['approve']=='1'){
						$ajukan	= "&nbsp;<button type='button' class='btn btn-sm btn-info ajukan' title='Ajukan Perbandingan' data-no_rfq='".$row['no_rfq']."'><i class='fa fa-check'></i></button>";
					}
				}
				if(($row['sts_ajuan']=='APV' OR $row['sts_ajuan']=='CLS') AND $row['sts_process']=='Y'){
					$ajukan	= "&nbsp;<button type='button' class='btn btn-sm btn-success hasil_ajukan' title='Hasil Perbandingan' data-no_rfq='".$row['no_rfq']."'><i class='fa fa-eye'></i></button>";
					$print	= "&nbsp;<a href='".base_url('purchase/print_hasil_pemilihan/'.$row['no_rfq'])."' target='_blank' class='btn btn-sm btn-warning' title='Print Hasil Perbandingan'><i class='fa fa-print'></i></a>";
				
				}
			$nestedData[]	= "<div align='left'>
                                    <button type='button' class='btn btn-sm btn-primary detailMat' title='Total Material Purchase' data-no_rfq='".$row['no_rfq']."' data-status='".$row['sts_ajuan']."'><i class='fa fa-eye'></i></button>
                                   ".$ajukan."
								   ".$hasil_ajukan."
								   ".$print."
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

	public function query_data_json_pengajuan($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

		$sql = "
			SELECT
				a.*
			FROM
				tran_material_rfq_header a
		    WHERE  
				(a.sts_ajuan='AJU' OR a.sts_ajuan='CLS' OR a.sts_ajuan='APV') 
			AND (
				a.no_rfq LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR a.nm_supplier LIKE '%".$this->db->escape_like_str($like_value)."%'
	        )
			GROUP BY a.no_rfq
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_rfq',
			2 => 'nm_supplier'
		);

		$sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}
	
	
	public function get_data_json_purchase_order(){

		$sql = "
			SELECT
				a.*
			FROM
				tran_material_po_header a
			WHERE 1=1
		";
		
		$query = $this->db->query($sql);
		return $query->result();
	}

}