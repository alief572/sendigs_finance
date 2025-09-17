<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Ichsan
 * @copyright Copyright (c) 2019, Ichsan
 *
 * This is model class for table "customer"
 */

class Customer_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'master_customer';
    protected $key        = 'id_customer';

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
    protected $soft_deletes = true;

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

    function generate_id($id_negara) {
      $query = $this->db->query("SELECT MAX(id_customer) as max_id FROM customer where id_negara='$id_negara'");
      $row = $query->row_array();
      $max_id = $row['max_id'];
      $max_id1 =(int) substr($max_id,3,3);
      $counter = $max_id1 +1;
      $idsup = $id_negara.str_pad($counter, 3, "0", STR_PAD_LEFT);
      return $idsup;
    }


}
