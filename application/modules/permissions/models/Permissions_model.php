<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Cabang"
 */

class Permissions_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'permissions';
    protected $key        = 'id_permission';

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

  	public function pilih_menu	(){
		$aMenu		= array();
		if(!empty($where)){
			
			$this->db->distinct();
			$query = $this->db->get_where('menus',$where);
			
		}else{
			
			$this->db->distinct();
			$query = $this->db->get('menus');
			
		}
		
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['id']]	= $vals['title'];
			}
		}
		return $aMenu;
		
	}
	
	public function pilih_menu_group(){
		$aMenu		= array();
		if(!empty($where)){
			
			$this->db->distinct();
			$query = $this->db->get_where('group_menus',$where);
			
		}else{
			
			$this->db->distinct();
			$query = $this->db->get('group_menus');
			
		}
		
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['id']]	= $vals['group_name'];
			}
		}
		return $aMenu;
		
	}
	
	public function pilih_permission(){
		$aMenu		= array();
		if(!empty($where)){
			
		$query1="SELECT *
                FROM
                permissions
				WHERE nm_permission LIKE '%View'";
        $query = $this->db->query($query1);
			
		}else{
			
		$query2="SELECT *
                FROM
                permissions
				WHERE nm_permission LIKE '%View'";
        $query = $this->db->query($query2);
		}
		
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['id_permission']]	= $vals['nm_permission'];
			}
		}
		return $aMenu;
		
	}
	
	public function pilih_parent(){
		$aMenu		= array();
		if(!empty($where)){
			
		$query1="SELECT *
                FROM
                menus
				";
        $query = $this->db->query($query1);
			
		}else{
			
		$query2="SELECT *
                FROM
                menus
				";
        $query = $this->db->query($query2);
		}
		
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['id']]	= $vals['title'];
			}
		}
		return $aMenu;
		
	}
	
	public function pilih_menu_permission(){
		$aMenu		= array();
		if(!empty($where)){
			
			$this->db->distinct();
			$query = $this->db->get_where('menus',$where);
			
		}else{
			
			$this->db->distinct();
			$query = $this->db->get('menus');
			
		}
		
		$results	= $query->result_array();
		if($results){
			foreach($results as $key=>$vals){
				$aMenu[$vals['title']]	= $vals['title'];
			}
		}
		return $aMenu;
		
	}
}
