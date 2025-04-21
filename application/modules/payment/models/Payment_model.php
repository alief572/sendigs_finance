<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is model class for table "Budget Rutin"
 */

class Payment_model extends BF_Model
{
    /**
     * @var string  User Table Name
     */
    protected $table_name = 'tr_payment_header';
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

	// list data
	public function ListPembayaran(){
		$this->db->select('a.*');
		$this->db->from($this->table_name.' a');
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function GetDataPayment($id=''){
		$this->db->select('a.*');
		$this->db->from($this->table_name.' a');
		$this->db->where('a.id',$id);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function GetDataPaymentDetail($nodoc=''){
		$this->db->select('a.*');
		$this->db->from('tr_payment_detail'.' a');
		$this->db->where('a.no_doc',$nodoc);
		$query = $this->db->get();
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	// get data
	public function GetDataBilling($tanggal='',$idbilling=''){
		$sql="select a.id id_bill, a.no_ipp no_po, a.category, a.progress, a.value nilai_invoice, a.jatuh_tempo tanggal_top, (a.value-a.value_paid) sisa_bayar, b.id_supplier id_vendor, b.nm_supplier nama from billing_top a join tran_material_po_header b on a.no_ipp=b.no_po where a.jatuh_tempo<='".$tanggal."' and a.status=1 ";
		if($idbilling!='') $sql.=" and a.id not in (".implode(",",$idbilling).")";
		$query = $this->db->query($sql);
		if($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

}
