<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Harboens
 * @copyright Copyright (c) 2019, Harboens
 *
 * This is model class for table "Budget"
 */

class Budget_coa_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'ms_budget';
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

	function GetBudget($tahun='',$all='',$query=''){
		$this->db->select('a.*, b.no_perkiraan , b.nama as nama_perkiraan, c.nm_dept');
		if($tahun!='') {
			if($all!='') {
				$this->db->from('(select * from '.$this->table_name.' where tahun='.$tahun.' ) a');
			}else{
				$this->db->from($this->table_name.' a');
				$this->db->where('a.tahun', $tahun);
			}
		}else{
			$this->db->from($this->table_name.' a');
		}
//		$this->db->join('ms_coa_category c','a.coa=c.coa','right');
		$this->db->join(DBACC.'.coa_master b','a.coa=b.no_perkiraan','right');
		$this->db->join('department c','a.divisi=c.id','left');
		if($query!='') $this->db->where($query);
		$this->db->where("b.level='5'");
		$this->db->order_by('b.no_perkiraan', 'asc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function GetCoa($level='5',$query=''){
		$aMenu		= array();
		$this->db->select("a.no_perkiraan as coa, a.nama, a.no_perkiraan, a.nama as nama_perkiraan");
		$this->db->from(DBACC.'.coa_master a');
		$this->db->where('a.level',$level);
		if($query!='') $this->db->where($query);
		$this->db->order_by('a.no_perkiraan', 'asc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function GetCoaCategory($tipe,$coa='',$name=''){
		$aMenu		= array();
		$this->db->select("a.coa, a.nama, b.no_perkiraan, b.nama as nama_perkiraan");
		$this->db->from('ms_coa_category a');
		$this->db->join(DBACC.'.coa_master b','a.coa=b.no_perkiraan');
		$this->db->where('a.tipe',$tipe);
		if($coa!=''){
			$this->db->where('a.coa',$coa);
		}
		$this->db->order_by('a.coa', 'asc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	function SearchBudget($coa,$tahun){
		$this->db->select('a.*');
		$this->db->from($this->table_name.' a');
		$this->db->where('a.coa', $coa);
		$this->db->where('a.tahun', $tahun);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	function GetBudgetCategory($where){
		$this->db->select('a.*, b.no_perkiraan , b.nama as nama_perkiraan, c.nm_dept');
		$this->db->from($this->table_name.' a');
		$this->db->join(DBACC.'.coa_master b','a.coa=b.no_perkiraan');
		$this->db->join('department c','a.divisi=c.id','left');
		$this->db->where($where);
		$this->db->order_by('b.no_perkiraan', 'asc');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	
	function GetListBudgetDept($kategori){
		$this->db->select('a.created_on_dept, a.revisi, a.tahun, a.status, a.kategori, a.divisi, a.total,a.finance_tahun, c.nm_dept');
		$this->db->from($this->table_name.' a');
		$this->db->join('department c','a.divisi=c.id','left');
		$this->db->where('a.status>0');
		$this->db->where('a.kategori',$kategori);
		$this->db->group_by('a.tahun ,a.status, a.kategori, a.divisi, c.nm_dept, a.created_on_dept, a.revisi'); 
		$this->db->order_by('a.created_on_dept', 'desc');
		$query = $this->db->get();
//		echo  $this->db->last_query();die();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

}
