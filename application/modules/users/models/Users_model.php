<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/* 
 * @author CokesHome
 * @copyright Copyright (c) 2015, CokesHome
 * 
 * This is model class for table "users"
 */

class Users_model extends BF_Model
{
    /**
     * @var string  User Table Name
     */
    protected $table_name = 'users';
    protected $key        = 'id_user';

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
    protected $set_created = TRUE;

    /**
     * @var bool Set the modified time automatically on editing a record (if true)
     */
    protected $set_modified = false;

    /**
     * @var bool Enable/Disable soft deletes.
     * If false, the delete() method will perform a delete of that row.
     * If true, the value in $deleted_field will be set to 1.
     */
    protected $soft_deletes = TRUE;

    /**
     * @var string The type of date/time field used for $created_field and $modified_field.
     * Valid values are 'int', 'datetime', 'date'.
     */
    protected $date_format = 'datetime';
    //--------------------------------------------------------------------

    /**
     * @var bool If true, will log user id in $created_by_field, $modified_by_field,
     * and $deleted_by_field.
     */
    protected $log_user = false;

    /**
     * Function construct used to load some library, do some actions, etc.
     */

    protected $hris;

    public function __construct()
    {
        parent::__construct();

        $this->hris = $this->load->database('hris', true);
    }

    public function get_list_department()
    {
        $this->hris->select('a.id, a.name as nm_dept, b.name as nm_comp');
        $this->hris->from('departments a');
        $this->hris->join('companies b', 'b.id = a.company_id');
        $get_list_department = $this->hris->get();

        return $get_list_department->result_array();
    }

    public function get_titles($department_id = null)
    {
        $this->hris->select('a.id, a.name as nm_title');
        $this->hris->from('titles a');
        $this->hris->where('a.department_id', $department_id);
        $get_titles = $this->hris->get()->result_array();

        return $get_titles;
    }

    public function get_list_employees()
    {
        $this->hris->select('a.id, a.name as nm_karyawan, b.name as nm_dept, c.name as nm_pos');
        $this->hris->from('employees a');
        $this->hris->join('departments b', 'b.id = a.department_id', 'left');
        $this->hris->join('positions c', 'c.id = a.position_id', 'left');
        $this->hris->where('a.flag_active', 'Y');
        $this->hris->order_by('a.name', 'asc');
        $get_list_employees = $this->hris->get();

        return $get_list_employees->result_array();
    }
}
